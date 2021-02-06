<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Engagement;
use App\Models\Ligne;
use App\Services\LigneService;

class LigneController extends Controller
{
    private $success_status = 200;

    public function getSolde(Request $request) {
        return response()->json([
            "status" => $this->success_status,
            "success" => true,
            "data" => LigneService::getSolde($request->id),
        ]);
    }
}
