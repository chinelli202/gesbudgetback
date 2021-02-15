<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Role;

class TeamController extends Controller
{
    private $sucess_status = 200;

    public function getlignes(Request $request) {
        $teamId = $request->teamId;
        $team = Team::findOrFail($teamId);
        $lignes = $team->lignes()->get();
        return response()->json(["status" => $this->sucess_status, "success" => true, "data" => $lignes]);       
    }

    public function ownlignes(Request $request) {
        $ligneIDs = explode(',', $request->ligneIds);

        $allnumbers = array_reduce($ligneIDs
        , function($old, $new) { return $old && is_numeric($new); }
        , true );
        
        if(!$allnumbers) {
            return response()->json([
                "status" => "failed"
                , "success" => false
                , "message" => "Erreur dans la requête envoyée. Les identifiants des lignes doivent être des nombres"]);  
        }

        $ligneIDs = array_map(function ($str) {
            return (int) $str;
        }, $ligneIDs);

        $requireall = is_null($request->requireall)? true: ($request->requireall == "false" ? false : true);
        
        $team = Team::findOrFail($request->teamId);
        try {
            $owns = $team->ownsLines($ligneIDs, $requireall);
        }
        catch (\Illuminate\Database\QueryException $e) {
            return response()->json(["status" => "failed", "success" => false, "message" => $e->errorInfo[2]]);
        }
        return response()->json(["status" => $this->sucess_status, "success" => true, "data" => $owns]);
    }

    public function addlignes(Request $request) {
        $ligneIDs = explode(',', $request->ligneIds);

        $allnumbers = array_reduce($ligneIDs
        , function($old, $new) { return $old && is_numeric($new); }
        , true );
        
        if(!$allnumbers) {
            return response()->json([
                "status" => "failed"
                , "success" => false
                , "message" => "Erreur dans la requête envoyée. Les identifiants des lignes doivent être des nombres"]);  
        }

        $ligneIDs = array_map(function ($str) {
            return (int) $str;
        }, $ligneIDs);

        $teamId = $request->teamId;
        $team = Team::findOrFail($teamId);
        try {
            $team->attachLignes($ligneIDs);
        }
        catch (\Illuminate\Database\QueryException $e) {
            if($e->errorInfo[0] == "23000") {
                return response()->json(["status" => "failed", "success" => false, "message" => "Cette association ligne-equipe a déjà été faîte."]);
            }
            return response()->json(["status" => "failed", "success" => false, "message" => $e->errorInfo[2]]);
        }
        return response()->json(["status" => $this->sucess_status, "success" => true, "data" => $team]);       
    }
}
