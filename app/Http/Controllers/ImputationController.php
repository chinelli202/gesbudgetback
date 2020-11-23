<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Contracts\Activity;
use App\Models\Engagement;
use App\Models\Imputation;
use App\Models\User;
use App\Models\Variable;
use App\Services\ImputationService;
use App\Services\EngagementService;

class ImputationController extends Controller
{
    private $success_status = 200;

    public function __construct() {

    }

    public function createImputation(Request $request) {
        $validator = Validator::make($request->all(), ImputationService::ImputationCreateValidator);
        
        if($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors()]);
        }

        $imputation = Imputation::create([
            "engagement_id" => $request->engagement_id,
            "reference" => $request->reference,
            "montant_ttc" => $request->montant_ttc,
            "montant_ht" => $request->montant_ht,
            "devise" => $request->devise,
            "observations" => $request->observations,
            
            'etat' => Config::get('gesbudget.variables.etat_engagement.INIT')[1],
            'statut' => Config::get('gesbudget.variables.statut_engagement.SAISI')[1],

            'saisisseur' => Auth::user()->matricule,
            'valideur_first' => null,
            'valideur_second' => null,
            'valideur_final' => null,
            'source' => Config::get('gesbudget.variables.source.API')[0]
        ]);
        
        $engagement = $imputation->engagement;

        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "data" => EngagementService::enrichEngagement($engagement->id)
        ]);
    }

    public function update(Request $request){
        $imputationId = $request->id;
        $validator = Validator::make($request->all(), ImputationService::ImputationCreateValidator);

        if($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors()]);
        }

        $imputation = Imputation::findOrFail($imputationId);
        $imputation->update([
            "observations" => $request->observations,
            "reference" => $request->reference,
            "montant_ttc" => $request->montant_ttc,
            "montant_ht" => $request->montant_ht,
            "devise" => $request->devise
        ]);
        
        $engagement = $imputation->engagement;

        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Imputation ". $imputation->code ." mis à jour avec succès"
            , "data" => EngagementService::enrichEngagement($engagement->id)
        ]);
    }
}
