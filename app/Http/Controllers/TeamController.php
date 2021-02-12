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
        $lignes = $team->lignes();
        return response()->json(["status" => $this->sucess_status, "success" => true, "data" => $lignes]);       
    }
}
