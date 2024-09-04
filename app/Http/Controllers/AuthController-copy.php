<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function index() {
        return view('Auth.login');
    }

    public function login(Request $req) {
        // Validate request
        $validator = Validator::make($req->all(), [
            'email' => 'required|email|exists:users',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $user = User::withTrashed()->where('email', $req->email)->first();

        // Check user is deleted
        if ($user->isDeleted()) {
            return back()->withErrors([
                'email' => 'The account has been deleted'
            ])->withInput();
        }
        // Check password is correct
        if (!Hash::check($req->password, $user->password)) {
            return back()->withErrors([
                'password' => 'The password is invalid'
            ])->withInput();
        }

        Auth::login($user);

        return redirect(route('ticket.index'))->with('info', 'Welcome Back!');
    }

    public function logout() {
        Session::flush();

        return redirect('/login');
    }

    public function viewProfile() {
        return view('Auth.profile');
    }
}
