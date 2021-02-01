<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Contracts\Activity;

class UserController extends Controller
{
    use CausesActivity;
    
    private $sucess_status = 200;

    // ---------------- [ User Sign Up ] -----------------
    public function createUser(Request $request) {
        $current_user   =       Auth::user();
        $validator      =       Validator::make($request->all(),
            [
                'sexe'              =>        'nullable|size:1',
                'date_naissance'    =>        'nullable|date',
                'date_embauche'     =>        'nullable|date',
                'addresse'          =>        'nullable',
                'num_compte'        =>        'nullable',
                'dom_bancaire'      =>        'nullable',
                'first_name'        =>        'required',
                'last_name'         =>        'required',
                'email'             =>        'required|email|unique:users',
                'matricule'         =>        'required|alpha_num|unique:users|size:5',
                'password'          =>        'required|min:5',
                'confirm_password'  =>        'required|same:password',
                'division'          =>        'required',
                'representation'    =>        'required',
                'fonction'          =>        'required'
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
            'sexe'              =>          $request->sexe,
            'date_naissance'    =>          $request->date_naissance,
            'date_embauche'     =>          $request->date_embauche,
            'addresse'          =>          $request->addresse,
            'num_compte'        =>          $request->num_compte,
            'dom_bancaire'      =>          $request->dom_bancaire,
            "first_name"        =>          $request->first_name,
            "last_name"         =>          $request->last_name,
            "name"              =>          $name,
            "email"             =>          $request->email,
            "matricule"         =>          $request->matricule,
            "password"          =>          bcrypt($request->password),
            'representation'    =>          $request->representation ? $request->representation : 'YDE',
            'division'          =>          $request->division, 
            'fonction'          =>          $request->fonction,
            'saisisseur'        =>          $current_user ? $current_user->matricule : 'NA', 
            'valideur'          =>          $current_user ? $current_user->matricule : 'NA',
            'source'            =>          $current_user ? $current_user->matricule : Config::get('laratrust.constants.user_creation_source.API'),
            'statut_utilisateur'=>          Config::get('laratrust.constants.user_status.INITIATED')
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

    // ---------------- [ Get list of all users ] -----------------
    public function getUsers(Request $request) {
        $users          =       User::all();
        if(!is_null($users)) {
            return response()->json(["status" => $this->sucess_status, "success" => true, "data" => $users]);
        }
        else {
            return response()->json(["status" => "failed", "success" => false, "message" => "Whoops! no user found"]);
        }
    }

    // -------------- [ User Login ] ---------------
    public function userLogin(Request $request) {
        if(Auth::attempt(['matricule' => $request->matricule, 'password' => $request->password, 'statut_utilisateur' => 'Init'])){
            activity()
                ->causedBy(Auth::user())
                ->tap(function(Activity $activity) use (&$request) {
                    $activity->comment = $request->header('User-Agent');
                })
                ->log(Config::get('gesbudget.variables.actions.LOGIN')[1]);

            $user       =       Auth::user();
            $token      =       $user->createToken('token')->accessToken;
            $user['token'] = $token;
            $user['roles'] = $user->getRoles();

            return response()->json(["status" => $this->sucess_status, "success" => true, "login" => true, "token" => $token, "data" => $user]);
        }
        else {
            return response()->json(["status" => "failed", "success" => false, "message" => "Whoops! invalid matricule or password"]);
        }
    }

    // -------------- [ get Login ] ---------------
    public function getLogin() {
        return response()->json(["status" => "failed", "success" => false, "message" => "You are not authenticated. You should login to perfom this action"]);
    }

    // -------------- [ User Logout ] ---------------
    public function userLogout(Request $request) {
        $request->user()->token()->revoke();
        activity()
            ->causedBy(Auth::user())
            ->tap(function(Activity $activity) use (&$request) {
                $activity->comment = $request->header('User-Agent');
            })
            ->log(Config::get('gesbudget.variables.actions.LOGOUT')[1]);

        return response()->json([
            "status" => $this->sucess_status,
            'message' => 'Deconnexion réussie'
        ]);
    }

    // ---------------- [ User Detail ] -------------------
    public function userDetail(Request $request) {
        $user           =       Auth::user();
        $user['roles'] = $request->user()->getRoles();
        $user['permissions'] = $request->user()->allPermissions();

        if(!is_null($user)) {
            return response()->json(["status" => $this->sucess_status, "success" => true, "data" => $user]);
        }
        else {
            return response()->json(["status" => "failed", "success" => false, "message" => "Whoops! no user found"]);
        }
    }
}
