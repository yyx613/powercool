<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BranchSelectionController extends Controller
{
    public function index()
    {
        $branches = [
            Branch::LOCATION_KL => (new Branch)->keyToLabel(Branch::LOCATION_KL),
            Branch::LOCATION_PENANG => (new Branch)->keyToLabel(Branch::LOCATION_PENANG),
        ];

        $redirectUrl = Session::get('branch_redirect_url');

        return view('branch.select', compact('branches', 'redirectUrl'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch' => 'required|in:'.Branch::LOCATION_KL.','.Branch::LOCATION_PENANG,
        ]);

        Session::put('as_branch', $request->branch);

        $redirectUrl = Session::pull('branch_redirect_url', route('dashboard.index'));

        return redirect($redirectUrl);
    }
}
