<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    const FORM_RULES = [
        'subject' => 'required|max:250',
        'customer' => 'required',
        'status' => 'required',
        'attachment' => 'nullable',
        'attachment.*' => 'file'
    ];
    
    public function index() {
        return view('ticket.list');
    }

    public function getData(Request $req) {
        $records = new Ticket;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%')
                    ->orWhere('subject', 'like', '%' . $keyword . '%')
                    ->orWhere('body', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
                1 => 'subject',
                2 => 'created_at',
            ];
            foreach ($req->order as $order) {
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('id', 'desc');
        }

        $records_count = $records->count();
        $records_ids = $records->pluck('id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'id' => $record->id,
                'sku' => $record->sku,
                'subject' => $record->subject,
                'created_at' => Carbon::parse($record->created_at)->format('d M Y H:i'),
                'status' => $record->is_active,
            ];
        }
                
        return response()->json($data);
    }

    public function create() {
        return view('ticket.form');
    }

    public function store(Request $req) {
        // Validate request
        $validator = Validator::make($req->all(), self::FORM_RULES);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $ticket = Ticket::create([
                'sku' => (new Ticket)->generateSku(),
                'subject' => $req->subject,
                'customer_id' => $req->customer,
                'is_active' => $req->boolean('status'),
                'body' => $req->body,
                'last_touch_by' => Auth::user()->id,
            ]);

            if ($req->hasFile('attachment')) {
                foreach ($req->file('attachment') as $key => $file) {
                    $path = Storage::putFile(Attachment::TICKET_PATH, $file);
                    Attachment::create([
                        'object_type' => Ticket::class,
                        'object_id' => $ticket->id,
                        'src' => basename($path),
                    ]);
                }
            }

            DB::commit();

            return redirect(route('ticket.index'))->with('success', 'Ticket created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit(Ticket $ticket) {
        $ticket->load('attachments');

        return view('ticket.form', [
            'ticket' => $ticket
        ]);
    }

    public function update(Request $req, Ticket $ticket) {
        // Validate request
        $validator = Validator::make($req->all(), self::FORM_RULES);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $ticket->update([
                'subject' => $req->subject,
                'customer_id' => $req->customer,
                'is_active' => $req->boolean('status'),
                'body' => $req->body,
                'last_touch_by' => Auth::user()->id,
            ]);

            if ($req->hasFile('attachment')) {
                Attachment::where([
                    ['object_type', Ticket::class],
                    ['object_id', $ticket->id]
                ])->delete();

                foreach ($req->file('attachment') as $key => $file) {
                    $path = Storage::putFile(Attachment::TICKET_PATH, $file);
                    Attachment::create([
                        'object_type' => Ticket::class,
                        'object_id' => $ticket->id,
                        'src' => basename($path),
                    ]);
                }
            }

            DB::commit();

            return redirect(route('ticket.index'))->with('success', 'Ticket updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function delete(Ticket $ticket) {
        $ticket->delete();

        return back()->with('success', 'Ticket deleted');
    }
}
