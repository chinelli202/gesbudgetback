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

class ImputationController extends Controller
{
    
    protected $imputationCreateValidator;

    public function __construct() {
        $this->imputationCreateValidator = [
            'engagement_id'     =>          'required|exists:engagements,code',
            'reference'         =>          'required',
            'montant_ht'        =>          'required',
            'montant_ttc'       =>          'required',
            'devise'            =>          'required|exists:variables,code',
            'observations'      =>          'required'
        ];
    }

    public function createImputation(Request $request) {
        $validator = Validator::make($request->all(), $this->imputationCreateValidator);
        
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
        
        $engagement = $imputations->engagement;

        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "data" => $this->enrichEngagement($engagement->id)
        ]); 
    }
}
