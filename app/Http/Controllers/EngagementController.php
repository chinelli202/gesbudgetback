<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Engagement;
use App\Models\Imputation;
use App\Models\User;
use App\Models\Variable;
use App\Models\Ligne;
use App\Models\Rubrique;
use App\Models\Chapitre;

use App\Services\EngagementService;
use App\Services\ImputationService;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Contracts\Activity;


class EngagementController extends Controller
{
    private $success_status = 200;
    protected $engagementCreateValidator;
    protected $engagementUpdateValidator;
    
    public function __construct()
    {
        $tva = Variable::where([['cle', '=', 'CONSTANTE'], ['code', '=', 'TVA']])
            ->orderBy('code')
            ->first()['valeur'];

        // TODO : Améliorer la validation pour inclure 
        // 1. montant_ttc = montant_ht*tva
        // 2. valideur_... required en fonction du statut de l'engagement
        // 3. nb_... & cumul_... required ou >0 en fonction du statut de l'engagement
        // 4. source exists in l'ensemble des valeurs possible de source
        // 5. Mieux gérer les retours des validations

        $this->engagementCreateValidator = [
            'libelle'           =>          'required',
            'montant_ht'        =>          'required',
            'montant_ttc'       =>          'required',
            'devise'            =>          'required|exists:variables,code',
            'nature'            =>          'required|exists:variables,code',
            'type'              =>          'required|exists:variables,code',
            'ligne_id'          =>          'required|exists:lignes,id'
        ];

        $this->engagementUpdateValidator = [
            'code'              =>          'required|alpha_dash',
            'libelle'           =>          'required',
            'montant_ht'        =>          'required|integer',
            'montant_ttc'       =>          'required|integer',
            'devise'            =>          'required|exists:variables,code',
            'nature'            =>          'required|exists:variables,code',
            'type'              =>          'required|exists:variables,code',
            'etat'              =>          'required|exists:variables,code',
            'statut'            =>          'required|exists:variables,code',
            'nb_imputations'    =>          'nullable|integer',
            'cumul_imputations' =>          'nullable|integer',
            'nb_apurements'     =>          'nullable|integer',
            'cumul_apurements'  =>          'nullable|integer',
            'saisisseur'        =>          'required|exists:users,matricule',
            'valideur_first'    =>          'nullable|exists:users,matricule',
            'valideur_second'   =>          'nullable|exists:users,matricule',
            'valideur_final'    =>          'nullable|exists:users,matricule',
            'source'            =>          'required',
            'ligne_id'          =>          'required|exists:lignes,id'

        ];
    }

    public function getEngagements(Request $request){
        $etat = $request->etat;

        $engagements = Engagement::where('etat', $etat)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($eng) {
                return EngagementService::enrichEngagement($eng->id);
            });
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $engagements]);
    }

    public function getEngagement(Request $request){
        $engagementId = $request->id;
        $engagement = EngagementService::enrichEngagement($engagementId);
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $engagement]);
    }
    
    public function create(Request $request){
        $validator = Validator::make($request->all(), $this->engagementCreateValidator);
        
        if($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors()]);
        }
        $engagement = Engagement::create([
            /** TODO : generate the code in this format : 020-LDC-113 
             * where '020' is the last 3 digit of the year
             * '113' is the id of the newly created engagement
             */
            "code" => $request->type .substr(now()->format('ymd-His-u'),0,17),
            "libelle" => $request->libelle,
            "montant_ttc" => $request->montant_ttc,
            "montant_ht" => $request->montant_ht,
            "devise" => $request->devise,
            "type" => $request->type,
            "nature" => $request->nature,
            
            'etat' => Config::get('gesbudget.variables.etat_engagement.INIT')[1],
            'statut' => Config::get('gesbudget.variables.statut_engagement.SAISI')[1],
            
            'nb_imputations' => 0,
            'cumul_imputations' => 0,
            'nb_apurements' => 0,
            'cumul_apurements' => 0,
            'saisisseur' => Auth::user()->matricule,
            'valideur_first' => null,
            'valideur_second' => null,
            'valideur_final' => null,
            'source' => Config::get('gesbudget.variables.source.API')[0],
            'ligne_id' => $request->ligne_id
        ]);
            
        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Engagement créé avec succès"
            , "data" => EngagementService::enrichEngagement($engagement->id)
        ]); 
    }

    public function update(Request $request){
        $engagementId = $request->id;
        $validator = Validator::make($request->all(), $this->engagementUpdateValidator);

        if($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors()]);
        }

        $engagement = Engagement::findOrFail($engagementId);
        $engagement->update([
            "libelle" => $request->libelle,
            "montant_ttc" => $request->montant_ttc,
            "montant_ht" => $request->montant_ht,
            "devise" => $request->devise,
            "type" => $request->type,
            "nature" => $request->nature,
            "ligne_id" => $request->ligne_id
        ]);
        $engagement = EngagementService::enrichEngagement($engagement->id);
        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Engagement ". $engagement->code ." mis à jour avec succès"
            , "data" => $engagement
        ]);
    }

    public function close(Request $request){
        $engagementId = $request->id;
        $engagement = Engagement::findOrFail($engagementId);
        if ($engagement->etat === Config::get('gesbudget.variables.etat_engagement.CLOT')[1]) {
            return response()->json(["error" => true, "message" => "Cet engagement ". $engagement->code ." a déjà été clôturé"]);
        }
        session()->put('CommentEngagement'.Auth::user()->id.$engagementId, $request->comment);
        
        $engagement->update([
            "etat" => Config::get('gesbudget.variables.etat_engagement.CLOT')[1],
        ]);
        $engagement = EngagementService::enrichEngagement($engagement->id);
        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Engagement ". $engagement->code ." cloturé avec succès"
            , "data" => $engagement
        ]);
    }

    public function restore(Request $request){
        $engagementId = $request->id;
        $engagement = Engagement::findOrFail($engagementId);
        if ($engagement->etat !== Config::get('gesbudget.variables.etat_engagement.CLOT')[1]) {
            return response()->json([
                "error" => true
                , "message" => "Cet engagement '". $engagement->code
                    ."' n'est pas clôturé ". $engagement->etat
            ]);
        }
        session()->put(['CommentEngagement'.Auth::user()->id.$engagementId, $request->comment]);
        
        $engagement->update([
            "etat" => Config::get('gesbudget.variables.etat_engagement.INIT')[1],
        ]);
        $engagement = EngagementService::enrichEngagement($engagement->id);
        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Engagement ". $engagement->code ." restauré avec succès"
            , "data" => $engagement]);
    }
    
    public function addcomment(Request $request){
        $engagementId = $request->id;
        $engagement = Engagement::findOrFail($engagementId);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($engagement)
            ->tap(function(Activity $activity) use (&$request) {
                $activity->comment = $request->comment;
            })
            ->log(Config::get('gesbudget.variables.actions.ADD_COMMENT')[1]);
        $engagement = EngagementService::enrichEngagement($engagement->id);
        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Commentaire ajouté à l'Engagement ". $engagement->code ." avec succès"
            , "data" => $engagement
        ]);
    }

    public function sendback(Request $request){
        $engagementId = $request->id;
        $engagement = Engagement::findOrFail($engagementId);

        session()->put('CommentEngagement'.Auth::user()->id.$engagementId, $request->comment);
        $engagement->update([
            "next_statut" => Config::get('gesbudget.variables.statut_engagement.SAISI')[1],
            "statut" => Config::get('gesbudget.variables.statut_engagement.SAISI')[1],
            "valideur_first" => null,
            "valideur_second" => null,
            "valideur_final" => null
        ]);
        $engagement = EngagementService::enrichEngagement($engagement->id);
        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Engagement ". $engagement->code ." renvoyé avec succès." //$engagement->next_statut 
            , "data" => $engagement
        ]);
    }

    public function resendupdate(Request $request){
        $engagementId = $request->id;
        $validator = Validator::make($request->all(), $this->engagementUpdateValidator);

        if($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors()]);
        }
        session()->put('CommentEngagement'.Auth::user()->id.$engagementId, $request->comment);
        $engagement = Engagement::findOrFail($engagementId);
        $engagement->update([
            "libelle" => $request->libelle,
            "montant_ttc" => $request->montant_ttc,
            "montant_ht" => $request->montant_ht,
            "devise" => $request->devise,
            "nature" => $request->nature,
            "type" => $request->type,
            "next_statut" => null
        ]);
        $engagement = EngagementService::enrichEngagement($engagement->id);
        return response()->json([
            "status" => $this->success_status
            , "success" => true, "message" => "Engagement ". $engagement->code ." mis à jour avec succès'"
            , "data" => $engagement
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
        
        $engagementId = $request->id;
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

        $engagement = Engagement::findOrFail($engagementId);

        if ($engagement->etat === Config::get('gesbudget.variables.etat_engagement.CLOT')[1]) {
            /** The engagement is closed */
            return response()->json([
                "error" => true
                , "message" => "Cet engagement ". $engagement->code ." est déjà clôturé. Vous ne pouvez pas le valider."
            ]);
        }

        if ($engagement->etat !== Config::get('gesbudget.variables.etat_engagement.INIT')[1]) {
            /** The engagement doesn't have the INIT state */
            return response()->json([
                "error" => true
                , "message" => "Cette entité ". $engagement->code .", n'est pas à l'état 'Initié' donc ne peut être validé en tant que préengagement."
            ]);
        }

        if ( $statutIndice > 0 && $engagement[$operateursKeys[$statutIndice - 1]] === Auth::user()->matricule) {
            /** The current performed the n-1 action on the engagement */
            return response()->json([
                "error" => true
                , "message" => "Vous ne pouvez pas valider l'engagement ". $engagement->code 
                    ." que vous avez ". $statutsEngagement[$statutsEngagementKeys[$statutIndice - 1]][0]
            ]);
        }

        if ($engagement->next_statut !== null) {
            /** The engagement has been sent back for correction*/
            return response()->json([
                "error" => true
                , "message" => "Vous ne pouvez pas valider l'engagement ". $engagement->code .". Il a été renvoyé à l'opérateur de saisi pour modification.
                    Celui-ci doit mettre à jour l'engagement afin que vous puissiez valider."
            ]);
        }

        if ($statutIndice < $prevRequiredStatutIndice) {
            /** The engagement has been sent back for correction*/
            return response()->json([
                "error" => true
                , "message" => "Vous ne pouvez pas valider l'engagement ". $engagement->code ." en cet état.
                    Celui-ci doit d'abord être ". $statutsEngagement[$prevRequiredStatut][0]
            ]);
        }

        session()->put('CommentEngagement'.Auth::user()->id.$engagementId, $request->comment);

        /** We change the 'etat' attribute to the next state if the validation to perform is a 'VALIDF' type of validation */
        if($statut === Config::get('gesbudget.variables.statut_engagement.VALIDF')[1]) {
            
            $engagement->update([
                "statut" => $statutsEngagementKeys[$statutIndice],
                $operateursKeys[$statutIndice] => Auth::user()->matricule,
                "etat" => $etatsEngagementKeys[
                    $engagement->etat === Config::get('gesbudget.variables.etat_engagement.REA')[1] ? $etatIndice: $etatIndice + 1]
            ]);
        } else {
            $engagement->update([
                "statut" => $statutsEngagementKeys[$statutIndice],
                $operateursKeys[$statutIndice] => Auth::user()->matricule
            ]);
        }

        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Engagement ". $engagement->code . " ". $statutsEngagement[$statut][0]. " avec succès"
            , "data" => EngagementService::enrichEngagement($engagement->id)
        ]);
    }

    public function cancelvalider(Request $request){
        $statutsEngagement = Config::get('gesbudget.variables.statut_engagement');
        $statutsEngagementKeys = array_keys($statutsEngagement);
        $operateursKeys = array_keys(Config::get('gesbudget.variables.operateur'));

        $engagementId = $request->id;
        $statut = $request->statut;
        $statutIndice = array_search($statut, $statutsEngagementKeys);

        $engagement = Engagement::findOrFail($engagementId);
        if ($engagement->etat === Config::get('gesbudget.variables.etat_engagement.CLOT')[1]) {
            /** The engagement is closed */
            return response()->json([
                "error" => true
                , "message" => "Cet engagement ". $engagement->code ." est déjà clôturé. Vous ne pouvez annuler de validation."
            ]);
        }

        if ($engagement->etat !== Config::get('gesbudget.variables.etat_engagement.INIT')[1]) {
            /** The engagement doesn't have the INIT state */
            return response()->json([
                "error" => true
                , "message" => "Annulation de validation Impossible. Cet engagement ". $engagement->code .", n'est pas à l'état 'Initié'."
            ]);
        }

        if ( $engagement[$operateursKeys[$statutIndice]] !== Auth::user()->matricule) {
            /** The current validation hasn't been done by the current user */
            return response()->json([
                "error" => true
                , "message" => "Annulation de validation Impossible pour l'engagement ". $engagement->code 
                    .", car que vous n'êtes pas celui qui l'a ". $statutsEngagement[$statutsEngagementKeys[$statutIndice - 1]][0]
            ]);
        }

        if ( $engagement->statut !== $statutsEngagementKeys[$statutIndice]) {
            /** The validation to cancel hasn't been performed */
            return response()->json([
                "error" => true
                , "message" => "Annulation de validation impossible. Cet engagement ". $engagement->code 
                    ." n'a pas été ". $statutsEngagement[$statutsEngagementKeys[$statutIndice ]][0]
            ]);
        }

        if ($engagement->next_statut !== null) {
            /** The engagement has been sent back for correction*/
            return response()->json([
                "error" => true
                , "message" => "Vous ne pouvez pas valider l'engagement ". $engagement->code .". Il a été renvoyé à l'opérateur de saisi pour modification.
                    Celui-ci doit mettre à jour l'engagement afin que vous puissiez valider."
            ]);
        }

        session()->put('CommentEngagement'.Auth::user()->id.$engagementId, $request->comment);
        
        /** We change the 'etat' attribute to the next state if the validation to perform is a 'VALIDF' type of validation */
        if($statut === Config::get('gesbudget.variables.statut_engagement.VALIDF')[1]) {
            
            $engagement->update([
                "statut" => $statutsEngagementKeys[$statutIndice - 1],
                $operateursKeys[$statutIndice] => null,
                "etat" => $etatsEngagementKeys[
                    $engagement->etat === Config::get('gesbudget.variables.etat_engagement.REA')[1] ? $etatIndice : $etatIndice - 1]
            ]);
        } else {
            $engagement->update([
                "statut" => $statutsEngagementKeys[$statutIndice - 1],
                $operateursKeys[$statutIndice] => null
            ]);
        }

        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Annulation de validation réussie pour l'engagement ". $engagement->code
            , "data" => EngagementService::enrichEngagement($engagement->id)
        ]);
    }
}
