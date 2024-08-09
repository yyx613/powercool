<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\EmailVerification;
use App\Mail\ForgetPassword;
use App\Models\Attachment;
use App\Models\Product;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\WellonWalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class AuthController extends Controller
{
    public function autoLogin(Request $req) {
        $token = $req->bearerToken();

        if ($token == null) {
            return Response::json([
                'msg' => 'token not found',
            ], HttpFoundationResponse::HTTP_UNAUTHORIZED);
        }

        $pat = PersonalAccessToken::findToken($token);

        if ($pat != null) {
            $user = $pat->tokenable;
    
            $user->role = getUserRole($user);
    
            return Response::json([
                'user' => $user,
            ], HttpFoundationResponse::HTTP_OK);
        }

        return Response::json([], HttpFoundationResponse::HTTP_BAD_REQUEST);
    }

    public function login(Request $request) {
        // Validate form
        $rules = [
            'email' => 'required|email|exists:users',
            'password' => 'required',
        ];
        $rule_msg = [
            'email.exists' => 'The email does not exists' 
        ];
        $validator = Validator::make($request->all(), $rules, $rule_msg);
        if ($validator->fails()) {
            return Response::json($validator->errors(), HttpFoundationResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = User::where('email', $request->input('email'))->first();

            if (!in_array(getUserRoleId($user), [Role::DRIVER, Role::TECHNICIAN, Role::SALE])) {
                return Response::json([
                    'email' => ['The account is not authorized']
                ], HttpFoundationResponse::HTTP_BAD_REQUEST);
            }

            if ($user == null || !Hash::check($request->input('password'), $user->password)) {
                return Response::json([
                    'password' => ['Invalid credentials']
                ], HttpFoundationResponse::HTTP_BAD_REQUEST);
            }

            // if (!$user->hasVerifiedEmail()) {
            //     return Response::json([
            //         'email' => ['The email is not verified'],
            //         'email_not_verified' => true,
            //     ], HttpFoundationResponse::HTTP_BAD_REQUEST);
            // }

            $user->role = getUserRole($user);
            // Create token
            $token = $user->createToken($user->email);

            return Response::json([
                'user' => $user,
                'token' => $token->plainTextToken,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            report($th);
            return Response::json([
                'msg' => 'something went wrong'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $req) {
        // Validate form
        $rules = [
            'profile_picture' => 'nullable|file|mimes:jpeg,jpg,png',
            'name' => 'required|max:250',
            'phone_number' => 'required',
            'password' => 'nullable|confirmed|max:250',
            'password_confirmation' => 'required_with:password',
        ];
        $validator = Validator::make($req->all(), $rules);
        if ($validator->fails()) {
            return Response::json($validator->errors(), HttpFoundationResponse::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $user = $req->user();

            $data_to_store = [
                'name' => $req->name,
                'phone_number' => $req->phone_number,
            ];
            if ($req->input('password')) {
                $data_to_store['password'] = Hash::make($req->password);
            }
            $user->update($data_to_store);

            if ($req->hasFile('profile_picture')) {
                $path = Storage::putFile(Attachment::USER_PATH, $req->file('profile_picture'));
                Attachment::create([
                    'object_type' => User::class,
                    'object_id' => $user->id,
                    'src' => basename($path),
                ]);
            }
            
            DB::commit();
    
            return Response::json([
                'msg' => 'profile updated',
                'user' => $user,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'msg' => 'something went wrong'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
