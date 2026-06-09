<?php

namespace App\Http\Controllers;

use App\Exports\DealerListingExport;
use App\Models\Branch;
use App\Models\Dealer;
use App\Services\StockCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class DealerController extends Controller
{
    public function index()
    {
        if (Session::get('dealer-company_group') != null) {
            $company_group = Session::get('dealer-company_group');
        }
        if (Session::get('dealer-search') != null) {
            $search = Session::get('dealer-search');
        }
        $page = Session::get('dealer-page');

        return view('dealer.list', [
            'default_company_group' => $company_group ?? null,
            'default_page' => $page ?? null,
            'default_search' => $search ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        Session::put('dealer-page', $req->page);

        $records = $this->applyFilters(new Dealer, $req);

        $records_count = $records->count();
        $records_ids = $records->pluck('id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $record) {
            $data['data'][] = [
                'id' => $record->id,
                'code' => $record->sku,
                'name' => $record->name,
                'company_name' => $record->company_name,
                'company_group' => $record->company_group == 1 ? 'Power Cool' : ($record->company_group == 2 ? 'Hi-Ten' : null),
                'can_edit' => hasPermission('dealer.edit'),
                'can_delete' => hasPermission('dealer.delete'),
            ];
        }

        return response()->json($data);
    }

    /**
     * Apply the dealer list filters (search, company group and ordering) to the
     * given query.
     *
     * Shared by the list (getData) and the Excel export so both honour the same
     * filter state. When a filter is absent from the request it falls back to
     * the value persisted in the session — which is what the export relies on,
     * since it carries no query parameters of its own.
     */
    protected function applyFilters($records, Request $req)
    {
        // Search with session persistence
        $keyword = null;
        if ($req->has('search')) {
            if ($req->search['value'] != null) {
                $keyword = $req->search['value'];
                Session::put('dealer-search', $keyword);
            } else {
                Session::remove('dealer-search');
            }
        } else if (Session::get('dealer-search') != null) {
            $keyword = Session::get('dealer-search');
        }

        if ($keyword != null) {
            $records = $records->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('company_name', 'like', '%' . $keyword . '%')
                    ->orWhere('sku', 'like', '%' . $keyword . '%');
            });
        }

        if ($req->has('company_group')) {
            if ($req->company_group == null) {
                Session::remove('dealer-company_group');
            } else {
                $records = $records->where('company_group', $req->company_group);
                Session::put('dealer-company_group', $req->company_group);
            }
        } else if (Session::get('dealer-company_group') != null) {
            $records = $records->where('company_group', Session::get('dealer-company_group'));
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
                1 => 'name',
                2 => 'company_name',
            ];
            foreach ($req->order as $order) {
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('id', 'desc');
        }

        return $records;
    }

    public function create()
    {
        return view('dealer.form');
    }

    public function edit(Dealer $dealer)
    {
        return view('dealer.form', [
            'dealer' => $dealer,
        ]);
    }

    public function view(Dealer $dealer)
    {
        return view('dealer.form', [
            'dealer' => $dealer,
            'mode' => 'view',
        ]);
    }

    public function delete(Dealer $dealer)
    {
        $dealer->delete();

        return back()->with('success', 'Dealer deleted');
    }

    public function upsert(Request $req, Dealer $dealer)
    {
        $req->validate([
            'name' => 'required|max:250',
            'company_name' => 'nullable|max:250',
            'company_group' => 'required',
        ]);

        try {
            DB::beginTransaction();

            if ($dealer->id == null) {
                $new_dealer = Dealer::create([
                    'name' => $req->name,
                    'company_name' => $req->company_name,
                    'company_group' => $req->company_group,
                    'sku' => (new Dealer)->generateSku(),
                ]);

                (new Branch)->assign(Dealer::class, $new_dealer->id);
            } else {
                $dealer->name = $req->name;
                $dealer->company_name = $req->company_name;
                $dealer->company_group = $req->company_group;
                $dealer->save();
            }

            DB::commit();

            return redirect(route('dealer.index'))->with('success', 'Dealer ' . (isset($new_dealer) && $new_dealer != null ? 'created' : 'updated'));
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong, Please contact administrator');
        }
    }

    public function export(Request $req)
    {
        // Exporting all dealers across branches builds a large in-memory
        // spreadsheet, which can overrun the default memory limit and return a
        // system error. Give the export enough headroom.
        ini_set('memory_limit', '512M');

        $branchLabels = [
            Branch::LOCATION_EVERY => 'Every Branch',
            Branch::LOCATION_KL => 'KL Branch',
            Branch::LOCATION_PENANG => 'Penang Branch',
        ];
        $branchLabel = $branchLabels[getCurrentUserBranch()] ?? 'Every Branch';

        // Mirror the list view's filters so the export only contains the records
        // the user is currently looking at.
        $query = $this->applyFilters(new Dealer, $req);

        // Stamp the report's company title to match the active company-group
        // filter. The export button carries no query params, so the filter lives
        // in the session (set when the list was last filtered).
        $companyGroup = $req->has('company_group') ? $req->company_group : Session::get('dealer-company_group');
        $companyName = StockCardService::companyTitleFor($companyGroup);

        return (new DealerListingExport($query, $companyName))->download("Dealer {$branchLabel}.xlsx");
    }
}
