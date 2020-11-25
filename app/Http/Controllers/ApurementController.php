<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Contracts\Activity;
use App\Models\Engagement;
use App\Models\Apurement;
use App\Models\User;
use App\Models\Variable;
use App\Services\ApurementService;
use App\Services\EngagementService;

class ApurementController extends Controller
{
    private $success_status = 200;

    public function __construct() {

    }

    /** Refactor all this class. It should be merged with EngagementController and Apurement Controller to obtain only one class
     * that will handle all these operations with the correct App\Models\... and App\Services\...
    */
    public function create(Request $request) {
        
        $validator = Validator::make($request->all(), ApurementService::ApurementCreateValidator);
        
        if($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors()]);
        }

        $apurement = Apurement::create([
            "engagement_id" => $request->engagement_id,
            "reference_paiement" => $request->reference_paiement,
            "montant_ttc" => $request->montant_ttc,
            "montant_ht" => $request->montant_ht,
            "devise" => $request->devise,
            "observations" => $request->observations,
            "libelle" => $request->libelle,
            
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
            , "data" => EngagementService::enrichEngagement($apurement->engagement->id)
        ]);
    }

    public function update(Request $request){
        $apurementId = $request->id;
        $validator = Validator::make($request->all(), ApurementService::ApurementCreateValidator);

        if($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors()]);
        }

        $apurement = Apurement::findOrFail($apurementId);
        $apurement->update([
            "observations" => $request->observations,
            "reference" => $request->reference,
            "montant_ttc" => $request->montant_ttc,
            "montant_ht" => $request->montant_ht,
            "devise" => $request->devise
        ]);
        
        $engagement = $apurement->engagement;

        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Apurement ". $apurement->id ." mis à jour avec succès"
            , "data" => EngagementService::enrichEngagement($engagement->id)
        ]);
    }

    public function close(Request $request){
        $apurementId = $request->id;
        $apurement = Apurement::findOrFail($apurementId);
        if ($apurement->etat === Config::get('gesbudget.variables.etat_engagement.CLOT')[1]) {
            return response()->json(["error" => true, "message" => "Cette apurement ". $apurement->id ." a déjà été clôturé"]);
        }
        session()->put('CommentApurement'.Auth::user()->id.$apurementId, $request->comment);
        
        $apurement->update([
            "etat" => Config::get('gesbudget.variables.etat_engagement.CLOT')[1],
        ]);

        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Apurement ". $apurement->id ." cloturé avec succès"
            , "data" => EngagementService::enrichEngagement($apurement->engagement->id)
        ]);
    }

    public function restore(Request $request){
        $apurementId = $request->id;
        $apurement = Apurement::findOrFail($apurementId);
        if ($apurement->etat !== Config::get('gesbudget.variables.etat_engagement.CLOT')[1]) {
            return response()->json([
                "error" => true
                , "message" => "Cet engagement '". $apurement->id
                    ."' n'est pas clôturé ". $apurement->etat
            ]);
        }
        session()->put(['CommentApurement'.Auth::user()->id.$apurementId, $request->comment]);
        
        $apurement->update([
            "etat" => Config::get('gesbudget.variables.etat_engagement.INIT')[1],
        ]);

        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Apurement ". $apurement->id ." restauré avec succès"
            , "data" => EngagementService::enrichEngagement($apurement->engagement->id)]);
    }

    public function addcomment(Request $request){
        $apurementId = $request->id;
        $apurement = Apurement::findOrFail($apurementId);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($apurement)
            ->tap(function(Activity $activity) use (&$request) {
                $activity->comment = $request->comment;
            })
            ->log(Config::get('gesbudget.variables.actions.ADD_COMMENT')[1]);

        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Commentaire ajouté à l'Apurement ". $apurement->id ." avec succès"
            , "data" => EngagementService::enrichEngagement($apurement->engagement->id)
        ]);
    }

    public function sendback(Request $request){
        $apurementId = $request->id;
        $apurement = Apurement::findOrFail($apurementId);

        session()->put('CommentApurement'.Auth::user()->id.$apurementId, $request->comment);
        $apurement->update([
            "next_statut" => Config::get('gesbudget.variables.statut_engagement.SAISI')[1],
            "statut" => Config::get('gesbudget.variables.statut_engagement.SAISI')[1],
            "valideur_first" => null,
            "valideur_second" => null,
            "valideur_final" => null
        ]);

        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Apurement ". $apurement->id ." renvoyé avec succès." //$engagement->next_statut 
            , "data" => EngagementService::enrichEngagement($apurement->engagement->id)
        ]);
    }

    public function resendupdate(Request $request){
        $apurementId = $request->id;
        $validator = Validator::make($request->all(), ApurementService::ApurementCreateValidator);

        if($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors()]);
        }
        session()->put('CommentApurement'.Auth::user()->id.$apurementId, $request->comment);
        $apurement = Apurement::findOrFail($apurementId);
        $apurement->update([
            "observations" => $request->observations,
            "reference" => $request->reference,
            "montant_ttc" => $request->montant_ttc,
            "montant_ht" => $request->montant_ht,
            "devise" => $request->devise,
            "next_statut" => null
        ]);

        return response()->json([
            "status" => $this->success_status
            , "success" => true, "message" => "Apurement ". $apurement->id ." mis à jour avec succès'"
            , "data" => EngagementService::enrichEngagement($apurement->engagement->id)
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
        
        $apurementId = $request->id;
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

        $apurement = Apurement::findOrFail($apurementId);

        if ($apurement->etat === Config::get('gesbudget.variables.etat_engagement.CLOT')[1]) {
            /** The engagement is closed */
            return response()->json([
                "error" => true
                , "message" => "Cette entité ". $apurement->id ." est déjà clôturé. Vous ne pouvez pas la valider."
            ]);
        }

        if ($apurement->etat !== Config::get('gesbudget.variables.etat_engagement.INIT')[1]) {
            /** The engagement doesn't have the INIT state */
            return response()->json([
                "error" => true
                , "message" => "Cette entité ". $apurement->id .", n'est pas à l'état 'Initié' donc ne peut être validé en tant que préengagement."
            ]);
        }

        if ( $statutIndice > 0 && $apurement[$operateursKeys[$statutIndice - 1]] === Auth::user()->matricule) {
            /** The current performed the n-1 action on the engagement */
            return response()->json([
                "error" => true
                , "message" => "Vous ne pouvez pas valider l'engagement ". $apurement->id 
                    ." que vous avez ". $statutsEngagement[$statutsEngagementKeys[$statutIndice - 1]][0]
            ]);
        }

        if ($apurement->next_statut !== null) {
            /** The engagement has been sent back for correction*/
            return response()->json([
                "error" => true
                , "message" => "Vous ne pouvez pas valider l'apurement ". $apurement->id .". Il a été renvoyé à l'opérateur de saisi pour modification.
                    Celui-ci doit mettre à jour l'engagement afin que vous puissiez valider."
            ]);
        }

        if ($statutIndice < $prevRequiredStatutIndice) {
            /** The engagement has been sent back for correction*/
            return response()->json([
                "error" => true
                , "message" => "Vous ne pouvez pas valider l'apurement ". $apurement->id ." en cet état.
                    Celui-ci doit d'abord être ". $statutsEngagement[$prevRequiredStatut][0]
            ]);
        }

        session()->put('CommentApurement'.Auth::user()->id.$apurementId, $request->comment);

        $engagement = $apurement->engagement;
        /** We change the 'etat' attribute to the next state if the validation to perform is a 'VALIDF' type of validation */
        if($statut === Config::get('gesbudget.variables.statut_engagement.VALIDF')[1]) {
            
            $apurement->update([
                "statut" => $statutsEngagementKeys[$statutIndice],
                $operateursKeys[$statutIndice] => Auth::user()->matricule
            ]);

            $engagement->update([
                "cumul_apurements" => $engagement->cumul_apurements + $apurement->montant_ttc,
                "nb_apurements" => $engagement->nb_apurements + 1,
                "etat" => Config::get('gesbudget.variables.etat_engagement.APUR')[1]
            ]);
        } else {
            $apurement->update([
                "statut" => $statutsEngagementKeys[$statutIndice],
                $operateursKeys[$statutIndice] => Auth::user()->matricule
            ]);
        }

        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Apurement ". $apurement->id . " ". $statutsEngagement[$statut][0]. " avec succès"
            , "data" => EngagementService::enrichEngagement($engagement->id)
        ]);
    }

    public function cancelvalider(Request $request){
        $statutsEngagement = Config::get('gesbudget.variables.statut_engagement');
        $statutsEngagementKeys = array_keys($statutsEngagement);
        $operateursKeys = array_keys(Config::get('gesbudget.variables.operateur'));

        $apurementId = $request->id;
        $statut = $request->statut;
        $statutIndice = array_search($statut, $statutsEngagementKeys);

        $apurement = Apurement::findOrFail($apurementId);
        if ($apurement->etat === Config::get('gesbudget.variables.etat_engagement.CLOT')[1]) {
            /** The engagement is closed */
            return response()->json([
                "error" => true
                , "message" => "Cette entité ". $apurement->id ." est déjà clôturé. Vous ne pouvez annuler de validation."
            ]);
        }

        if ($apurement->etat !== Config::get('gesbudget.variables.etat_engagement.INIT')[1]) {
            /** The engagement doesn't have the INIT state */
            return response()->json([
                "error" => true
                , "message" => "Annulation de validation Impossible. Cet apurement "
                    . $apurement->id .", n'est pas à l'état 'Initié'. -" . $apurement->etat
            ]);
        }

        if ( $apurement[$operateursKeys[$statutIndice]] !== Auth::user()->matricule) {
            /** The current validation hasn't been done by the current user */
            return response()->json([
                "error" => true
                , "message" => "Annulation de validation Impossible pour l'apurement ". $apurement->id 
                    .", car que vous n'êtes pas celui qui l'a ". $statutsEngagement[$statutsEngagementKeys[$statutIndice]][0]
                    . "operateursKeys statutIndice"
            ]);
        }

        if ( $apurement->statut !== $statutsEngagementKeys[$statutIndice]) {
            /** The validation to cancel hasn't been performed */
            return response()->json([
                "error" => true
                , "message" => "Annulation de validation impossible. Cet apurement ". $apurement->id 
                    ." n'a pas été ". $statutsEngagement[$statutsEngagementKeys[$statutIndice ]][0]
            ]);
        }

        if ($apurement->next_statut !== null) {
            /** The engagement has been sent back for correction*/
            return response()->json([
                "error" => true
                , "message" => "Vous ne pouvez pas valider l'apurement ". $apurement->id .". Il a été renvoyé à l'opérateur de saisi pour modification.
                    Celui-ci doit mettre à jour l'engagement afin que vous puissiez valider."
            ]);
        }

        session()->put('CommentApurement'.Auth::user()->id.$ApurementId, $request->comment);
        
        $engagement = $apurement->engagement;
        /** We change the 'etat' attribute to the next state if the validation to perform is a 'VALIDF' type of validation */
        if($statut === Config::get('gesbudget.variables.statut_engagement.VALIDF')[1]) {
            
            $apurement->update([
                "statut" => $statutsEngagementKeys[$statutIndice-1],
                $operateursKeys[$statutIndice] => null
            ]);

            $engagement->update([
                "cumul_apurements" => $engagement->cumul_apurements - $apurement->montant_ttc,
                "nb_apurements" => $engagement->nb_apurements - 1,
                "etat" => Config::get('gesbudget.variables.etat_engagement.PEG')[1]
            ]);
        } else {
            $apurement->update([
                "statut" => $statutsEngagementKeys[$statutIndice-1],
                $operateursKeys[$statutIndice] => null
            ]);
        }

        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Annulation de validation réussie pour l'apurement ". $apurement->id
            , "data" => EngagementService::enrichEngagement($engagement->id)
        ]);
    }
}
