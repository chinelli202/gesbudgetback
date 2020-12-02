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

    /** Refactor all this class. It should be merged with EngagementController and Apurement Controller to obtain only one class
     * that will handle all these operations with the correct App\Models\... and App\Services\...
    */
    public function create(Request $request) {
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

        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Création d'une imputation réussie pour l'engagement ". $imputation->engagement->id
            , "data" => EngagementService::enrichEngagement($imputation->engagement->id)
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
            , "message" => "Imputation ". $imputation->id ." mis à jour avec succès"
            , "data" => EngagementService::enrichEngagement($engagement->id)
        ]);
    }

    public function close(Request $request){
        $imputationId = $request->id;
        $imputation = Imputation::findOrFail($imputationId);
        if ($imputation->etat === Config::get('gesbudget.variables.etat_engagement.CLOT')[1]) {
            return response()->json(["error" => true, "message" => "Cette imputation ". $imputation->id ." a déjà été clôturé"]);
        }
        session()->put('CommentImputation'.Auth::user()->id.$imputationId, $request->comment);
        
        $imputation->update([
            "etat" => Config::get('gesbudget.variables.etat_engagement.CLOT')[1],
        ]);

        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Imputation ". $imputation->id ." cloturé avec succès"
            , "data" => EngagementService::enrichEngagement($imputation->engagement->id)
        ]);
    }

    public function restore(Request $request){
        $imputationId = $request->id;
        $imputation = Imputation::findOrFail($imputationId);
        if ($imputation->etat !== Config::get('gesbudget.variables.etat_engagement.CLOT')[1]) {
            return response()->json([
                "error" => true
                , "message" => "Cet engagement '". $imputation->id
                    ."' n'est pas clôturé ". $imputation->etat
            ]);
        }
        session()->put(['CommentImputation'.Auth::user()->id.$imputationId, $request->comment]);
        
        $imputation->update([
            "etat" => Config::get('gesbudget.variables.etat_engagement.INIT')[1],
        ]);

        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Imputation ". $imputation->id ." restauré avec succès"
            , "data" => EngagementService::enrichEngagement($imputation->engagement->id)]);
    }

    public function addcomment(Request $request){
        $imputationId = $request->id;
        $imputation = Imputation::findOrFail($imputationId);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($imputation)
            ->tap(function(Activity $activity) use (&$request) {
                $activity->comment = $request->comment;
            })
            ->log(Config::get('gesbudget.variables.actions.ADD_COMMENT')[1]);

        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Commentaire ajouté à l'Imputation ". $imputation->id ." avec succès"
            , "data" => EngagementService::enrichEngagement($imputation->engagement->id)
        ]);
    }

    public function sendback(Request $request){
        $imputationId = $request->id;
        $imputation = Imputation::findOrFail($imputationId);

        session()->put('CommentImputation'.Auth::user()->id.$imputationId, $request->comment);
        $imputation->update([
            "next_statut" => Config::get('gesbudget.variables.statut_engagement.SAISI')[1],
            "statut" => Config::get('gesbudget.variables.statut_engagement.SAISI')[1],
            "valideur_first" => null,
            "valideur_second" => null,
            "valideur_final" => null
        ]);

        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Imputation ". $imputation->id ." renvoyé avec succès." //$engagement->next_statut 
            , "data" => EngagementService::enrichEngagement($imputation->engagement->id)
        ]);
    }

    public function resendupdate(Request $request){
        $imputationId = $request->id;
        $validator = Validator::make($request->all(), ImputationService::ImputationCreateValidator);

        if($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors()]);
        }
        session()->put('CommentImputation'.Auth::user()->id.$imputationId, $request->comment);
        $imputation = Imputation::findOrFail($imputationId);
        $imputation->update([
            "observations" => $request->observations,
            "reference" => $request->reference,
            "montant_ttc" => $request->montant_ttc,
            "montant_ht" => $request->montant_ht,
            "devise" => $request->devise,
            "next_statut" => null
        ]);

        return response()->json([
            "status" => $this->success_status
            , "success" => true, "message" => "Imputation ". $imputation->id ." mis à jour avec succès'"
            , "data" => EngagementService::enrichEngagement($imputation->engagement->id)
        ]);
    }

    public function valider(Request $request){
        $statutsEngagement = Config::get('gesbudget.variables.statut_engagement');
        $statutsEngagementKeys = array_keys($statutsEngagement);

        $etatsEngagement = Config::get('gesbudget.variables.etat_engagement');
        $etatsEngagementKeys = array_keys($etatsEngagement);
        
        $statut = $request->statut;
        $etat = $request->etat;
        
        $statutIndice = array_search($statut, $statutsEngagementKeys);
        $etatIndice = array_search($etat, $etatsEngagementKeys);
        
        $imputationId = $request->id;
        $operateursKeys = array_keys(Config::get('gesbudget.variables.operateur'));

        /** Previous blocking statut */
        if ( $statutIndice === array_search(Config::get('gesbudget.variables.statut_engagement.VALIDS')[1], $statutsEngagementKeys) + 1) {
            /** Since the VALIDS status is not required, we will return the statut previous VALIDS */
            $prevRequiredStatutIndice =  $statutIndice - 2;
            $prevRequiredStatut =  $statutsEngagementKeys[$prevRequiredStatutIndice];
        } else {
            $prevRequiredStatutIndice = ($statutIndice > 0) ? $statutIndice - 1 : null;
            $prevRequiredStatut = ($statutIndice > 0) ? $statutsEngagementKeys[$statutIndice - 1] : null;
        }

        $imputation = Imputation::findOrFail($imputationId);

        if ($imputation->etat === Config::get('gesbudget.variables.etat_engagement.CLOT')[1]) {
            /** The engagement is closed */
            return response()->json([
                "error" => true
                , "message" => "Cette entité ". $imputation->id ." est déjà clôturé. Vous ne pouvez pas la valider."
            ]);
        }

        if ($imputation->etat !== Config::get('gesbudget.variables.etat_engagement.INIT')[1]) {
            /** The engagement doesn't have the INIT state */
            return response()->json([
                "error" => true
                , "message" => "Cette entité ". $imputation->id .", n'est pas à l'état 'Initié' donc ne peut être validé en tant que préengagement."
            ]);
        }

        if ( $statutIndice > 0 && $imputation[$operateursKeys[$statutIndice - 1]] === Auth::user()->matricule) {
            /** The current performed the n-1 action on the engagement */
            return response()->json([
                "error" => true
                , "message" => "Vous ne pouvez pas valider l'engagement ". $imputation->id 
                    ." que vous avez ". $statutsEngagement[$statutsEngagementKeys[$statutIndice - 1]][0]
            ]);
        }

        if ($imputation->next_statut !== null) {
            /** The engagement has been sent back for correction*/
            return response()->json([
                "error" => true
                , "message" => "Vous ne pouvez pas valider l'imputation ". $imputation->id .". Il a été renvoyé à l'opérateur de saisi pour modification.
                    Celui-ci doit mettre à jour l'engagement afin que vous puissiez valider."
            ]);
        }

        if ($statutIndice < $prevRequiredStatutIndice) {
            /** The engagement has been sent back for correction*/
            return response()->json([
                "error" => true
                , "message" => "Vous ne pouvez pas valider l'apurement ". $imputation->id ." en cet état.
                    Celui-ci doit d'abord être ". $statutsEngagement[$prevRequiredStatut][0]
            ]);
        }

        session()->put('CommentImputation'.Auth::user()->id.$imputationId, $request->comment);

        $engagement = $imputation->engagement;
        /** We change the 'etat' attribute to the next state if the validation to perform is a 'VALIDF' type of validation */
        if($statut === Config::get('gesbudget.variables.statut_engagement.VALIDF')[1]) {
            
            $imputation->update([
                "statut" => $statutsEngagementKeys[$statutIndice],
                $operateursKeys[$statutIndice] => Auth::user()->matricule
            ]);

            $engagement->update([
                "cumul_imputations" => $engagement->cumul_imputations + $imputation->montant_ttc,
                "nb_imputations" => $engagement->nb_imputations + 1,
                "etat" => Config::get('gesbudget.variables.etat_engagement.IMP')[1],
                "latest_statut" => Config::get('gesbudget.variables.statut_engagement.NEW')[1],
                "latest_edited_at" => now()
            ]);
        } else {
            $imputation->update([
                "statut" => $statutsEngagementKeys[$statutIndice],
                "latest_statut" => $statutsEngagementKeys[$statutIndice],
                "latest_edited_at" => now(),
                $operateursKeys[$statutIndice] => Auth::user()->matricule
            ]);
        }

        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Imputation ". $imputation->id . " ". $statutsEngagement[$statut][0]. " avec succès"
            , "data" => EngagementService::enrichEngagement($engagement->id)
        ]);
    }

    public function cancelvalider(Request $request){
        $statutsEngagement = Config::get('gesbudget.variables.statut_engagement');
        $statutsEngagementKeys = array_keys($statutsEngagement);
        $operateursKeys = array_keys(Config::get('gesbudget.variables.operateur'));

        $imputationId = $request->id;
        $statut = $request->statut;
        $statutIndice = array_search($statut, $statutsEngagementKeys);

        $imputation = Imputation::findOrFail($imputationId);
        if ($imputation->etat === Config::get('gesbudget.variables.etat_engagement.CLOT')[1]) {
            /** The engagement is closed */
            return response()->json([
                "error" => true
                , "message" => "Cette entité ". $imputation->id ." est déjà clôturé. Vous ne pouvez annuler de validation."
            ]);
        }

        if ($imputation->etat !== Config::get('gesbudget.variables.etat_engagement.INIT')[1]) {
            /** The engagement doesn't have the INIT state */
            return response()->json([
                "error" => true
                , "message" => "Annulation de validation Impossible. Cet imputation "
                    . $imputation->id .", n'est pas à l'état 'Initié'. -" . $imputation->etat
            ]);
        }

        if ( $imputation[$operateursKeys[$statutIndice]] !== Auth::user()->matricule) {
            /** The current validation hasn't been done by the current user */
            return response()->json([
                "error" => true
                , "message" => "Annulation de validation Impossible pour l'imputation ". $imputation->id 
                    .", car que vous n'êtes pas celui qui l'a ". $statutsEngagement[$statutsEngagementKeys[$statutIndice]][0]
                    . "operateursKeys statutIndice"
            ]);
        }

        if ( $imputation->statut !== $statutsEngagementKeys[$statutIndice]) {
            /** The validation to cancel hasn't been performed */
            return response()->json([
                "error" => true
                , "message" => "Annulation de validation impossible. Cet imputation ". $imputation->id 
                    ." n'a pas été ". $statutsEngagement[$statutsEngagementKeys[$statutIndice ]][0]
            ]);
        }

        if ($imputation->next_statut !== null) {
            /** The engagement has been sent back for correction*/
            return response()->json([
                "error" => true
                , "message" => "Vous ne pouvez pas valider l'imputation ". $imputation->id .". Il a été renvoyé à l'opérateur de saisi pour modification.
                    Celui-ci doit mettre à jour l'engagement afin que vous puissiez valider."
            ]);
        }

        session()->put('CommentImputation'.Auth::user()->id.$imputationId, $request->comment);
        
        $engagement = $imputation->engagement;
        /** We change the 'etat' attribute to the next state if the validation to perform is a 'VALIDF' type of validation */
        if($statut === Config::get('gesbudget.variables.statut_engagement.VALIDF')[1]) {
            
            $imputation->update([
                "statut" => $statutsEngagementKeys[$statutIndice-1],
                $operateursKeys[$statutIndice] => null
            ]);

            $engagement->update([
                "cumul_imputations" => $engagement->cumul_imputations - $imputation->montant_ttc,
                "nb_imputations" => $engagement->nb_imputations - 1,
                "etat" => Config::get('gesbudget.variables.etat_engagement.PEG')[1]
            ]);
        } else {
            $imputation->update([
                "statut" => $statutsEngagementKeys[$statutIndice-1],
                $operateursKeys[$statutIndice] => null
            ]);
        }

        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Annulation de validation réussie pour l'imputation ". $imputation->id
            , "data" => EngagementService::enrichEngagement($engagement->id)
        ]);
    }
}
