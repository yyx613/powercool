<?php

namespace App\Http\Controllers;

use App\Exports\SaleEnquiryExport;
use App\Models\Approval;
use App\Models\Branch;

use App\Models\SaleEnquiry;
use App\Models\Scopes\BranchScope;
use App\Models\User;
use App\Notifications\SaleEnquiryAcceptedNotification;
use App\Notifications\SaleEnquiryAssignedNotification;
use App\Notifications\SaleEnquiryNoDealNotification;
use App\Notifications\SaleEnquiryRejectedNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class SaleEnquiryController extends Controller
{
    public function index()
    {
        $page = Session::get('sale-enquiry-page');
        $search = Session::get('sale-enquiry-search');

        return view('sale_enquiry.list', [
            'default_page' => $page ?? null,
            'default_search' => $search ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        Session::put('sale-enquiry-page', $req->page);
        Session::put('sale-enquiry-search', $req->search['value'] ?? null);

        $records = SaleEnquiry::query();

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%')
                    ->orWhere('name', 'like', '%' . $keyword . '%')
                    ->orWhere('phone_number', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%')
                    ->orWhere('category', 'like', '%' . $keyword . '%');
            });
        }

        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
                1 => 'enquiry_date',
                2 => 'name',
                3 => 'phone_number',
                4 => 'email',
                5 => 'enquiry_source',
            ];
            foreach ($req->order as $order) {
                $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records->orderBy('id', 'desc');
        }

        $records = $records->with(['assignedUser', 'promotion', 'createdByUser']);

        $records_count = $records->count();
        $records_ids = $records->pluck('id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
            'records_ids' => $records_ids,
        ];

        foreach ($records_paginator as $key => $record) {
            // Get priority label
            $priorityLabel = null;
            switch ($record->priority) {
                case 1:
                    $priorityLabel = 'Low';
                    break;
                case 2:
                    $priorityLabel = 'Medium';
                    break;
                case 3:
                    $priorityLabel = 'High';
                    break;
            }

            $data['data'][] = [
                'id' => $record->id,
                'sku' => $record->sku,
                'enquiry_date' => $record->enquiry_date->format('d M Y'),
                'name' => $record->name,
                'phone_number' => $record->phone_number,
                'email' => $record->email,
                'enquiry_source' => $record->enquiry_source,
                'product' => $record->product_service_interested,
                'assigned_user' => $record->assignedUser ? $record->assignedUser->name : null,
                'priority' => $priorityLabel,
                'quality' => $record->quality,
                'promotion' => $record->promotion ? $record->promotion->sku . ' - ' . ($record->promotion->type == 'perc' ? number_format($record->promotion->amount, 2) . '%' : 'RM' . number_format($record->promotion->amount, 2)) : null,
                'created_by_user' => $record->createdByUser ? $record->createdByUser->name : null,
                'status' => $record->status,
                // Surface the rejection on the list so the reason shows in a tooltip on the Status cell.
                'is_rejected' => $record->rejected_at !== null,
                'reject_reason' => $record->reject_reason,
                'can_view' => hasPermission('sale_enquiry.view'),
                'can_edit' => hasPermission('sale_enquiry.edit'),
                'can_delete' => hasPermission('sale_enquiry.delete'),
                // The assigned salesperson must accept or reject before viewing.
                'is_assignee' => (int) $record->assigned_user_id === (int) Auth::id(),
                'is_pending' => $record->accepted_at === null && $record->rejected_at === null,
                // Whether a "No Deal" request is awaiting management approval.
                'no_deal_pending' => $record->hasPendingNoDealApproval(),
            ];
        }

        return response()->json($data);
    }

    public function view(SaleEnquiry $enquiry)
    {
        // The assigned salesperson must accept or reject the enquiry before they
        // are allowed to view its details. Everyone else may view freely.
        if ($enquiry->isPendingActionBy(Auth::id())) {
            return redirect()->route('sale_enquiry.index')
                ->with('error', __('Please accept or reject this enquiry before viewing its details.'));
        }

        $enquiry->load(['assignedUser', 'acceptedByUser', 'rejectedByUser', 'createdByUser', 'countryModel', 'stateModel', 'promotion']);

        // Most recent management rejection of a No-Deal request, surfaced to the
        // salesperson (mirrors the quotation rejected-reason display).
        $noDealRejectedRemark = Approval::withoutGlobalScope(BranchScope::class)
            ->where('object_type', SaleEnquiry::class)
            ->where('object_id', $enquiry->id)
            ->where('status', Approval::STATUS_REJECTED)
            ->where('data', 'like', '%is_no_deal%')
            ->orderBy('id', 'desc')
            ->first();

        return view('sale_enquiry.view', [
            'enquiry' => $enquiry,
            'progress' => $enquiry->progress(),
            'noDealPending' => $enquiry->hasPendingNoDealApproval(),
            'noDealRejectedRemark' => $noDealRejectedRemark,
        ]);
    }

    public function getViewData(Request $req)
    {
        // Salespeople only see the enquiry details, not the related sales (with amounts).
        if (isSalesOnly()) {
            return response()->json([
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $enquiry = SaleEnquiry::find($req->enquiry_id);

        if (!$enquiry) {
            return response()->json([
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $records = $enquiry->sales()->with('customer');

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%')
                    ->orWhereHas('customer', function ($q) use ($keyword) {
                        $q->where('company_name', 'like', '%' . $keyword . '%');
                    });
            });
        }

        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
            ];
            foreach ($req->order as $order) {
                if (isset($map[$order['column']])) {
                    $records->orderBy($map[$order['column']], $order['dir']);
                }
            }
        } else {
            $records->orderBy('id', 'desc');
        }

        $records_count = $records->count();
        $records_paginator = $records->simplePaginate(10);

        $data = [
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
        ];

        foreach ($records_paginator as $sale) {
            $date = $sale->custom_date ?? $sale->created_at;

            $data['data'][] = [
                'sku' => $sale->sku,
                'date' => $date ? Carbon::parse($date)->format('d M Y') : null,
                'customer' => $sale->customer ? $sale->customer->company_name : null,
                'amount' => number_format($sale->getTotalAmount(), 2),
                'payment_status' => $sale->payment_status,
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        return view('sale_enquiry.form');
    }

    public function store(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'enquiry_date' => 'required|date',
            'enquiry_source' => 'required|in:1,2,3,4,5,6,7,8,9,10,11,12',
            'name' => 'required|max:250',
            'phone_number' => 'required|max:50',
            'email' => 'nullable|email|max:250',
            'preferred_contact_method' => 'required|in:1,2,3',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'category' => 'required|in:1,2,3,4,5',
            'description' => 'nullable',
            'product_service_interested' => 'required|max:500',
            'assigned_user_id' => 'required|exists:users,id',
            'priority' => 'required|in:1,2,3',
            'status' => 'required|in:1,2,3,4',
            'quality' => 'required|in:1,2,3',
            'promotion_id' => 'nullable|exists:promotions,id',
        ], [], [
            'name' => 'Customer Name',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Include trashed rows: the `sku` unique index still covers
            // soft-deleted enquiries, so their numbers must be skipped or the
            // insert collides on a SKU that generateSku thinks is free.
            // Include trashed rows: the `sku` unique index still covers
            // soft-deleted enquiries, so their numbers must be skipped or the
            // insert collides on a SKU that generateSku thinks is free.
            $existing_skus = SaleEnquiry::withoutGlobalScope(BranchScope::class)->withTrashed()->pluck('sku')->toArray();

            $enquiry = SaleEnquiry::create([
                'sku' => generateSku('ENQ', $existing_skus, false),
                'enquiry_date' => $req->enquiry_date,
                'enquiry_source' => $req->enquiry_source,
                'name' => $req->name,
                'phone_number' => $req->phone_number,
                'email' => $req->email,
                'preferred_contact_method' => $req->preferred_contact_method,
                'country_id' => $req->country_id,
                'state_id' => $req->state_id,
                'category' => $req->category,
                'description' => $req->description,
                'product_service_interested' => $req->product_service_interested,
                'assigned_user_id' => $req->assigned_user_id,
                'priority' => $req->priority,
                'status' => $req->status,
                'quality' => $req->quality,
                'promotion_id' => $req->promotion_id,
                'created_by' => Auth::id(),
            ]);

            (new Branch)->assign(SaleEnquiry::class, $enquiry->id);

            DB::commit();

            $this->notifyAssignedUser($enquiry);

            return redirect()->route('sale_enquiry.index')
                ->with('success', __('Sale enquiry created successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function edit(SaleEnquiry $enquiry)
    {
        return view('sale_enquiry.form', [
            'enquiry' => $enquiry
        ]);
    }

    public function update(Request $req, SaleEnquiry $enquiry)
    {
        $validator = Validator::make($req->all(), [
            'enquiry_date' => 'required|date',
            'enquiry_source' => 'required|in:1,2,3,4,5,6,7,8,9,10,11,12',
            'name' => 'required|max:250',
            'phone_number' => 'required|max:50',
            'email' => 'nullable|email|max:250',
            'preferred_contact_method' => 'required|in:1,2,3',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'category' => 'required|in:1,2,3,4,5',
            'description' => 'nullable',
            'product_service_interested' => 'required|max:500',
            'assigned_user_id' => 'required|exists:users,id',
            'priority' => 'required|in:1,2,3',
            'status' => 'required|in:1,2,3,4',
            'quality' => 'required|in:1,2,3',
            'promotion_id' => 'nullable|exists:promotions,id',
        ], [], [
            'name' => 'Customer Name',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $reassigned = (int) $enquiry->assigned_user_id !== (int) $req->assigned_user_id;

            $data = [
                'enquiry_date' => $req->enquiry_date,
                'enquiry_source' => $req->enquiry_source,
                'name' => $req->name,
                'phone_number' => $req->phone_number,
                'email' => $req->email,
                'preferred_contact_method' => $req->preferred_contact_method,
                'country_id' => $req->country_id,
                'state_id' => $req->state_id,
                'category' => $req->category,
                'description' => $req->description,
                'product_service_interested' => $req->product_service_interested,
                'assigned_user_id' => $req->assigned_user_id,
                'priority' => $req->priority,
                'status' => $req->status,
                'quality' => $req->quality,
                'promotion_id' => $req->promotion_id,
            ];

            // Reassigning to a different salesperson resets the acceptance state
            // so the new owner starts pending and must accept (or reject) the job
            // themselves. The prior rejection must be cleared too, otherwise the
            // stale rejected_at keeps the enquiry out of the pending state and the
            // new owner never sees the Accept/Reject buttons.
            if ($reassigned) {
                $data['accepted_at'] = null;
                $data['accepted_by'] = null;
                $data['rejected_at'] = null;
                $data['rejected_by'] = null;
                $data['reject_reason'] = null;
                // A previously rejected enquiry was closed as No Deal; handing it to
                // a new owner reopens it as New so they start from a clean state.
                $data['status'] = SaleEnquiry::STATUS_NEW;
            }

            $enquiry->update($data);

            DB::commit();

            if ($reassigned) {
                $this->notifyAssignedUser($enquiry);
            }

            return redirect()->route('sale_enquiry.index')
                ->with('success', __('Sale enquiry updated successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function accept(SaleEnquiry $enquiry)
    {
        // Only the salesperson the enquiry is assigned to may accept it.
        if ((int) $enquiry->assigned_user_id !== (int) Auth::id()) {
            abort(403);
        }

        if ($enquiry->accepted_at !== null) {
            return redirect()->route('sale_enquiry.view', ['enquiry' => $enquiry])
                ->with('info', __('You have already accepted this enquiry'));
        }

        if ($enquiry->rejected_at !== null) {
            return redirect()->route('sale_enquiry.view', ['enquiry' => $enquiry])
                ->with('info', __('You have already rejected this enquiry'));
        }

        try {
            DB::beginTransaction();

            $enquiry->accepted_at = now();
            $enquiry->accepted_by = Auth::id();
            if ((int) $enquiry->status === SaleEnquiry::STATUS_NEW) {
                $enquiry->status = SaleEnquiry::STATUS_IN_PROGRESS;
            }
            $enquiry->save();

            DB::commit();

            $this->notifyEnquiryAccepted($enquiry);

            return redirect()->route('sale_enquiry.view', ['enquiry' => $enquiry])
                ->with('success', __('Enquiry accepted successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $req, SaleEnquiry $enquiry)
    {
        // Only the salesperson the enquiry is assigned to may reject it.
        if ((int) $enquiry->assigned_user_id !== (int) Auth::id()) {
            abort(403);
        }

        if ($enquiry->rejected_at !== null) {
            return redirect()->route('sale_enquiry.view', ['enquiry' => $enquiry])
                ->with('info', __('You have already rejected this enquiry'));
        }

        if ($enquiry->accepted_at !== null) {
            return redirect()->route('sale_enquiry.view', ['enquiry' => $enquiry])
                ->with('info', __('You have already accepted this enquiry'));
        }

        // The salesperson must give a reason when rejecting.
        $validator = Validator::make($req->all(), [
            'reason' => 'required|max:1000',
        ], [], [
            'reason' => 'Reject Reason',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $enquiry->rejected_at = now();
            $enquiry->rejected_by = Auth::id();
            $enquiry->reject_reason = $req->reason;
            // Rejecting the enquiry up front means there is no deal to pursue, so
            // close it out as No Deal instead of leaving it sitting as New.
            $enquiry->status = SaleEnquiry::STATUS_CLOSED_DROPPED;
            $enquiry->save();

            DB::commit();

            $this->notifyEnquiryRejected($enquiry);

            return redirect()->route('sale_enquiry.index')
                ->with('success', __('Enquiry rejected successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * The assigned salesperson changes the Current Status from the view page.
     * They may move it to New / In Progress / Closed Deal directly; selecting
     * "No Deal" instead raises a request that management must approve.
     */
    public function updateStatus(Request $req, SaleEnquiry $enquiry)
    {
        // Only the salesperson the enquiry is assigned to may change its status.
        if ((int) $enquiry->assigned_user_id !== (int) Auth::id()) {
            abort(403);
        }

        // They must have accepted the job before progressing it.
        if ($enquiry->accepted_at === null) {
            return back()->with('error', __('Please accept this enquiry before updating its status.'));
        }

        $validator = Validator::make($req->all(), [
            'status' => 'required|in:1,2,3,4',
            'reason' => 'required_if:status,4|max:1000',
        ], [], [
            'reason' => 'No Deal Reason',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $status = (int) $req->status;

        // "No Deal" does not take effect immediately — it needs management approval.
        if ($status === SaleEnquiry::STATUS_CLOSED_DROPPED) {
            if ($enquiry->hasPendingNoDealApproval()) {
                return back()->with('info', __('A No Deal request is already pending approval.'));
            }

            try {
                DB::beginTransaction();

                $approval = Approval::create([
                    'object_type' => SaleEnquiry::class,
                    'object_id' => $enquiry->id,
                    'status' => Approval::STATUS_PENDING_APPROVAL,
                    'data' => json_encode([
                        'is_no_deal' => true,
                        'reason' => $req->reason,
                        'description' => __(':name marked enquiry :sku as No Deal.', [
                            'name' => Auth::user()->name,
                            'sku' => $enquiry->sku,
                        ]),
                    ]),
                ]);

                (new Branch)->assign(Approval::class, $approval->id);

                DB::commit();

                $this->notifyNoDealRequested($enquiry, $req->reason);

                return back()->with('success', __('No Deal request submitted for approval.'));

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', $e->getMessage());
            }
        }

        try {
            DB::beginTransaction();

            $enquiry->status = $status;
            // Moving back to an active state clears any prior No-Deal reason.
            $enquiry->no_deal_reason = null;
            $enquiry->save();

            DB::commit();

            return back()->with('success', __('Enquiry status updated successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Notify the enquiry creator (management) that the salesperson wants to mark
     * the enquiry as No Deal and is awaiting approval.
     */
    private function notifyNoDealRequested(SaleEnquiry $enquiry, ?string $reason): void
    {
        if (!$enquiry->created_by || (int) $enquiry->created_by === (int) Auth::id()) {
            return;
        }

        $creator = User::withoutGlobalScope(BranchScope::class)->find($enquiry->created_by);

        if (!$creator) {
            return;
        }

        $salesperson = User::withoutGlobalScope(BranchScope::class)->find($enquiry->assigned_user_id);

        Notification::send($creator, new SaleEnquiryNoDealNotification([
            'enquiry_id' => $enquiry->id,
            'sku' => $enquiry->sku,
            'url' => route('sale_enquiry.view', ['enquiry' => $enquiry]),
            'desc' => __(':name requested to mark enquiry (:sku) as No Deal. Reason: :reason', [
                'name' => $salesperson ? $salesperson->name : __('The salesperson'),
                'sku' => $enquiry->sku,
                'reason' => $reason,
            ]),
        ]));
    }

    /**
     * Notify the assigned salesperson that an enquiry has been assigned to them.
     */
    private function notifyAssignedUser(SaleEnquiry $enquiry): void
    {
        $user = User::withoutGlobalScope(BranchScope::class)->find($enquiry->assigned_user_id);

        if (!$user) {
            return;
        }

        Notification::send($user, new SaleEnquiryAssignedNotification([
            'enquiry_id' => $enquiry->id,
            'sku' => $enquiry->sku,
            'url' => route('sale_enquiry.view', ['enquiry' => $enquiry]),
            'desc' => __('You have been assigned a new sale enquiry (:sku) from :name.', [
                'sku' => $enquiry->sku,
                'name' => $enquiry->name,
            ]),
        ]));
    }

    /**
     * Notify the enquiry creator (management) that the salesperson accepted the job.
     */
    private function notifyEnquiryAccepted(SaleEnquiry $enquiry): void
    {
        if (!$enquiry->created_by || (int) $enquiry->created_by === (int) Auth::id()) {
            return;
        }

        $creator = User::withoutGlobalScope(BranchScope::class)->find($enquiry->created_by);

        if (!$creator) {
            return;
        }

        $salesperson = User::withoutGlobalScope(BranchScope::class)->find($enquiry->assigned_user_id);

        Notification::send($creator, new SaleEnquiryAcceptedNotification([
            'enquiry_id' => $enquiry->id,
            'sku' => $enquiry->sku,
            'url' => route('sale_enquiry.view', ['enquiry' => $enquiry]),
            'desc' => __(':name has accepted the sale enquiry (:sku).', [
                'name' => $salesperson ? $salesperson->name : __('The salesperson'),
                'sku' => $enquiry->sku,
            ]),
        ]));
    }

    /**
     * Notify the enquiry creator (management) that the salesperson rejected the job.
     */
    private function notifyEnquiryRejected(SaleEnquiry $enquiry): void
    {
        if (!$enquiry->created_by || (int) $enquiry->created_by === (int) Auth::id()) {
            return;
        }

        $creator = User::withoutGlobalScope(BranchScope::class)->find($enquiry->created_by);

        if (!$creator) {
            return;
        }

        $salesperson = User::withoutGlobalScope(BranchScope::class)->find($enquiry->assigned_user_id);

        Notification::send($creator, new SaleEnquiryRejectedNotification([
            'enquiry_id' => $enquiry->id,
            'sku' => $enquiry->sku,
            'url' => route('sale_enquiry.view', ['enquiry' => $enquiry]),
            'desc' => __(':name has rejected the sale enquiry (:sku). Reason: :reason', [
                'name' => $salesperson ? $salesperson->name : __('The salesperson'),
                'sku' => $enquiry->sku,
                'reason' => $enquiry->reject_reason,
            ]),
        ]));
    }

    public function delete(SaleEnquiry $enquiry)
    {
        try {
            $enquiry->delete();

            return redirect()->route('sale_enquiry.index')
                ->with('success', __('Sale enquiry deleted successfully'));

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function export()
    {
        return Excel::download(new SaleEnquiryExport, 'sale_enquiry.xlsx');
    }
}
