<?php

namespace App\Http\Controllers;

use App\Exports\DealerExport;
use App\Models\Branch;
use App\Models\Dealer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DealerController extends Controller
{
    public function index()
    {
        return view('dealer.list');
    }

    public function getData(Request $req)
    {
        $records = new Dealer;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%'.$keyword.'%')
                    ->orWhere('sku', 'like', '%'.$keyword.'%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
                1 => 'name',
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
                'can_edit' => hasPermission('dealer.edit'),
                'can_delete' => hasPermission('dealer.delete'),
            ];
        }

        return response()->json($data);
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
        ]);

        try {
            DB::beginTransaction();

            if ($dealer->id == null) {
                $new_dealer = Dealer::create([
                    'name' => $req->name,
                    'sku' => (new Dealer)->generateSku(),
                ]);

                (new Branch)->assign(Dealer::class, $new_dealer->id);
            } else {
                $dealer->name = $req->name;
                $dealer->save();
            }

            DB::commit();

            return redirect(route('dealer.index'))->with('success', 'Dealer '.(isset($new_dealer) && $new_dealer != null ? 'created' : 'updated'));
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong, Please contact administrator');
        }
    }

    public function export()
    {
        return Excel::download(new DealerExport, 'dealer.xlsx');
    }
}
