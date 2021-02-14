<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Role;

class TeamController extends Controller
{
    private $sucess_status = 200;

    public function getlignes(Request $request) {
        $teamId = $request->id;
        $team = Team::findOrFail($teamId);
        $lignes = $team->lignes()->get();
        return response()->json(["status" => $this->sucess_status, "success" => true, "data" => $lignes]);       
    }

    public function ownlignes(Request $request) {
        $ligneIDs = explode(',', $request->ids);
        $requireall = is_null($request->requireall)? true: ($request->requireall == "false" ? false : true);
        $teamId = $request->id;
        $team = Team::findOrFail($teamId);
        $owns = $team->ownsLine($ligneIDs, $requireall);
        return response()->json(["status" => $this->sucess_status, "success" => true, "data" => $owns]);
    }

    public function addlignes(Request $request) {
        $ligneIDs = explode(',', $request->ids);
        $teamId = $request->id;
        $team = Team::findOrFail($teamId);
        $lignes = $team->lignes()->get();
        return response()->json(["status" => $this->sucess_status, "success" => true, "data" => $lignes]);       
    }
}
