<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{
    private $sucess_status = 200;

    // ---------------- [ User Sign Up ] -----------------
    public function createUser(Request $request) {
        $current_user   =       Auth::user();
        $validator      =       Validator::make($request->all(),
            [
                'first_name'        =>        'required',
                'last_name'         =>        'required',
                'email'             =>        'required|email|unique:users',
                'matricule'         =>        'required|unique:users',
                'password'          =>        'required|alpha_num|min:5',
                'confirm_password'  =>        'required|same:password',
                'division'          =>        'required', 
                'fonction'          =>        'required',
            ]
        );

        if($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors()]);
        }

        $name                   =           $request->first_name . " " . $request->last_name;

        if(isset($fullName[1])) {
            $last_name      =      $fullName[1];
        }

        $dataArray          =       array(
            "first_name"        =>          $request->first_name,
            "last_name"         =>          $request->last_name,
            "name"              =>          $name,
            "email"             =>          $request->email,
            "matricule"         =>          $request->matricule,
            "password"          =>          bcrypt($request->password),
            'division'          =>          $request->division, 
            'fonction'          =>          $request->fonction,
            'saisisseur'        =>          $current_user ? $current_user->matricule : 'admin0', 
            'valideur'          =>          'NA', 
            'statut_utilisateur'=>          'init'
        );

        $user               =               User::create($dataArray);
        $user->attachRole('user');
        $user['roles'] = $user->getRoles();

        if(!is_null($user)) {
            return response()->json(["status" => $this->sucess_status, "success" => true, "data" => $user]);
        }

        else {
            return response()->json(["status" => "failed", "success" => false, "message" => "Whoops! user not created. please try again."]);
        }
    }


    // -------------- [ User Login ] ---------------
    public function userLogin(Request $request) {

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user       =       Auth::user();
            $token      =       $user->createToken('token')->accessToken;
            $user['token'] = $token;
            $user['roles'] = $user->getRoles();

            return response()->json(["status" => $this->sucess_status, "success" => true, "login" => true, "token" => $token, "data" => $user]);
        }
        else {
            return response()->json(["status" => "failed", "success" => false, "message" => "Whoops! invalid email or password"]);
        }
    }

    // -------------- [ get Login ] ---------------
    public function getLogin() {
        return response()->json(["status" => "failed", "success" => false, "message" => "You are not authenticated. You should login to perfom this action"]);
    }

    // -------------- [ User Logout ] ---------------
    public function userLogout(Request $request) {
        $request->user()->token()->revoke();
        return response()->json([
            "status" => $this->sucess_status,
            'message' => 'Successfully logged out'
        ]);
    }

    // ---------------- [ User Detail ] -------------------
    public function userDetail(Request $request) {
        $user           =       Auth::user();
        $user['roles'] = $request->user()->getRoles();

        if(!is_null($user)) {
            return response()->json(["status" => $this->sucess_status, "success" => true, "data" => $user]);
        }
        else {
            return response()->json(["status" => "failed", "success" => false, "message" => "Whoops! no user found"]);
        }
    }
}
