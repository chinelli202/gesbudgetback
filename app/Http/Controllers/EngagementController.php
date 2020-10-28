<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Engagement;
use Illuminate\Support\Facades\Config;

class EngagementController extends Controller
{
    private $sucess_status = 200;

    public function getEngagements(Request $request){
        $etat = $request->etat;

        $engagements = Engagement::where('etat', Config::get('app_seeder.variables.etat_engagement.'. $etat))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(["status" => $this->sucess_status, "success" => true, "data" => $engagements]);
    }
}
