<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Team;
use App\Models\Role;
use App\Models\Entreprise;
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
    /**
     * Return current user's detail for a specific team. If no team is specified, then return details for the first team to which
     * the user belongs. 
     */
    public function userDetail(Request $request) {
        $user           =       Auth::user();
        if(is_null($user)) {
            return response()->json(["status" => "failed", "success" => false, "message" => "Whoops! no user found"]);
        }

        $teamId = $request->teamId;
        $roleIDs = [];
        if(is_null($teamId)) {
            $team = $request->user()->rolesTeams()->first();
            if(is_null($team)) {
                return response()->json(["status" => "failed", "success" => false, "message" => "Cet utilisateur n'a pas d'équipe associée."]);
            }    
            $teamId = $team['id'];
        } else {
            $team = Team::findOrFail($teamId);
        }
        // Building team list and roleIDS list of all the roles per teams
        $user['teams'] = array_reduce(
            $request->user()->rolesTeams()->get()->toArray(),
            function($old, $new) use (&$roleIDs) {
                $newId = $new['id'];
                $roleIDs[$newId][] = $new['pivot']['role_id'];
                unset($new['pivot']);
                $old[$newId] = $new;
                return $old;
            },
            []
        );
        $user['team'] = $team;
        unset($user['team']['pivot']);
        $user['roles'] = $user->getRoles($team);
        $user['permissions'] = array_reduce(
            $roleIDs[$teamId],
            function($array, $roleID) {
                $permissions = Role::findOrFail($roleID)->permissions()->get()->toArray();
                foreach($permissions as $permission) {
                    $array[] = $permission;
                }
                return $array;
            },
            []
        );

        // building the 'teams' attribute to include the corresponding roles and permissions
        /* $user['teams'] = array_reduce(
            $request->user()->rolesTeams()->get()->toArray(),
            function($old, $new) use (&$user) {
                $newTeamId = $new['id'];
                $newTeam = Team::findOrFail($newTeamId);
                if(!($old && array_key_exists($newTeamId, $old))) {
                    $keys = array_keys($new);
                    foreach ($keys as $key) {
                        if($key == 'pivot') continue;
                        $old[$newTeamId][$key] = $new[$key];
                    }
                }
                $roleID = $new['pivot']['role_id'];
                $old[$newTeamId]['roles_id'][] = $roleID;
                $old[$newTeamId]['roles'] = $roles = $user->getRoles($newTeam);
                foreach($roles as $role) {
                    $permissions = Role::findOrFail($roleID)->permissions()->get()->toArray();
                    foreach($permissions as $permission) {
                        $old[$newTeamId]['permissions'][] = $permission;
                    }
                }
                return $old;
            },
            []
        ); */
        return response()->json(["status" => $this->sucess_status, "success" => true, "data" => $user]);
    }
}
