<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Engagement;
use App\Models\User;
use App\Models\Variable;
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
        // . montant_ttc = montant_ht*tva
        // . valideur_... required en fonction du statut de l'engagement
        // . nb_... & cumul_... required ou >0 en fonction du statut de l'engagement
        // . source exists in l'ensemble des valeurs possible de source
        $this->engagementCreateValidator = [
            'code'              =>          'required|alpha_dash|unique:engagements',
            'libelle'           =>          'required',
            'montant_ht'        =>          'required',
            'montant_ttc'       =>          'required',
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
            'source'            =>          'required'
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
            'source'            =>          'required'
        ];
    }

    function enrichEngagement($engagementId){
        $engagement = Engagement::findOrFail($engagementId);
        $saisisseur = User::where('matricule', $engagement->saisisseur)->first();
        $valideurP = User::where('matricule', $engagement->valideur_first)->first();
        $valideurS = User::where('matricule', $engagement->valideur_second)->first();
        $valideurF = User::where('matricule', $engagement->valideur_final)->first();

        $devise = Variable::where('code', $engagement->devise)->first();
        $nature = Variable::where('code', $engagement->nature)->first();
        $type = Variable::where('code', $engagement->type)->first();
        $etat = Variable::where('code', $engagement->etat)->first();
        $statut = Variable::where('code', $engagement->statut)->first();

        $engagement["saisisseur_name"] = $saisisseur->name;
        $engagement["valideurp_name"] = $valideurP->name ?? '';
        $engagement["valideurs_name"] = $valideurS->name ?? '';
        $engagement["valideurf_name"] = $valideurF->name ?? '';

        $engagement["devise_libelle"] = $devise->libelle ?? '';
        $engagement["nature_libelle"] = $nature->libelle ?? '';
        $engagement["type_libelle"] = $type->libelle ?? '';
        $engagement["etat_libelle"] = $etat->libelle ?? '';
        $engagement["statut_libelle"] = $statut->libelle ?? '';

        return $engagement;
    }

    public function getEngagements(Request $request){
        $etat = $request->etat;

        $engagements = Engagement::where('etat', $etat)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(["status" => $this->success_status, "success" => true, "data" => $engagements]);
    }

    public function getEngagement(Request $request){
        $engagementId = $request->id;
        $engagement = $this->enrichEngagement($engagementId);
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $engagement]);
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
            "nature" => $request->nature
        ]);
        $engagement = $this->enrichEngagement($engagement->id);
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
            return response()->json(["error" => true, "message" => "Cet engagement ". $engagement->code ." déjà clôturé"]);
        }
        session()->put('CommentEngagement'.Auth::user()->id.$engagementId, $request->comment);
        
        $engagement->update([
            "etat" => Config::get('gesbudget.variables.etat_engagement.CLOT')[1],
        ]);
        $engagement = $this->enrichEngagement($engagement->id);
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
        $engagement = $this->enrichEngagement($engagement->id);
        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Engagement ". $engagement->code ." restauré avec succès"
            , "data" => $engagement]);
    }
    
    public function addComment(Request $request){
        $engagementId = $request->id;
        $engagement = Engagement::findOrFail($engagementId);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($engagement)
            ->tap(function(Activity $activity) use (&$request) {
                $activity->comment = $request->comment;
            })
            ->log(Config::get('gesbudget.variables.actions.ADD_COMMENT')[1]);
        $engagement = $this->enrichEngagement($engagement->id);
        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Commentaire ajouté à l'Engagement ". $engagement->code ." avec succès"
            , "data" => $engagement
        ]);
    }

    public function sendBack(Request $request){
        $engagementId = $request->id;
        $engagement = Engagement::findOrFail($engagementId);

        session()->put('CommentEngagement'.Auth::user()->id.$engagementId, $request->comment);
        $engagement->update([
            "next_statut" => Config::get('gesbudget.variables.statut_engagement.SAISI')[1]
        ]);
        $engagement = $this->enrichEngagement($engagement->id);
        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Engagement ". $engagement->code ." renvoyé avec succès." //$engagement->next_statut 
            , "data" => $engagement
        ]);
    }

    public function resendUpdate(Request $request){
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
            "nature" => $request->devise,
            "type" => $request->type,
            "next_statut" => null
        ]);
        $engagement = $this->enrichEngagement($engagement->id);
        return response()->json([
            "status" => $this->success_status
            , "success" => true, "message" => "Engagement ". $engagement->code ." mis à jour avec succès'"
            , "data" => $engagement
        ]);
    }

    public function validerPreeng(Request $request){
        $statutsEngagement = Config::get('gesbudget.variables.statut_engagement');
        $statutsEngagementKeys = array_keys($statutsEngagement);
        $operateursKeys = array_keys(Config::get('gesbudget.variables.operateur'));

        $engagementId = $request->id;
        $statut = $request->statut;
        $statutIndice = array_search($statut, $statutsEngagementKeys);

        /** Previous blocking statut */
        if ( $statutIndice === array_search(Config::get('gesbudget.variables.statut_engagement.VALIDS')[1], $statutsEngagementKeys) + 1) {
            /** Since the VALIDS status is not required, we will return the statut previous VALIDS */
            $prevRequiredStatut =  $statutsEngagementKeys[$statutIndice - 2];
        } else {
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

        if ($engagement->statut !== $prevRequiredStatut) {
            /** The engagement has been sent back for correction*/
            return response()->json([
                "error" => true
                , "message" => "Vous ne pouvez pas valider l'engagement ". $engagement->code ." en cet état.
                    Celui-ci doit d'abord être ". $statutsEngagement[$prevRequiredStatut][0]
            ]);
        }

        session()->put('CommentEngagement'.Auth::user()->id.$engagementId, $request->comment);
        
        $engagement->update([
            "statut" => $statutsEngagementKeys[$statutIndice],
            $operateursKeys[$statutIndice] => Auth::user()->matricule
        ]);
        $engagement = $this->enrichEngagement($engagement->id);
        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Engagement ". $engagement->code . " ". $statutsEngagement[$statut][0]. " avec succès"
            , "data" => $engagement
        ]);
    }

    public function cancelValiderPreeng(Request $request){
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
                , "message" => "Annulation de validation au 1er niveau Impossible. Cet engagement ". $engagement->code .", n'est pas à l'état 'Initié'."
            ]);
        }

        if ( $engagement[$operateursKeys[$statutIndice]] !== Auth::user()->matricule) {
            /** The current validation hasn't been done by the current user */
            return response()->json([
                "error" => true
                , "message" => "Annulation de validation au 1er niveau Impossible pour l'engagement ". $engagement->code 
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
        
        $engagement->update([
            "statut" => $statutsEngagementKeys[$statutIndice - 1],
            $operateursKeys[$statutIndice] => null
        ]);
        $engagement = $this->enrichEngagement($engagement->id);
        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Annulation de validation réussie pour l'engagement ". $engagement->code
            , "data" => $engagement
        ]);
    }

    public function cancelValiderpPeg(Request $request){
        $engagementId = $request->id;
        $engagement = Engagement::findOrFail($engagementId);
        if ($engagement->etat === Config::get('gesbudget.variables.etat_engagement.CLOT')[1]) {
            /** The engagement is closed */
            return response()->json([
                "error" => true
                , "message" => "Cet engagement ". $engagement->code ." est déjà clôturé. Vous ne pouvez annuler de validation."
            ]);
        }
        
        if ($engagement->etat !== Config::get('gesbudget.variables.etat_engagement.INIT')[1]) {
            /** The engagement doesn't have the INIT state*/
            return response()->json([
                "error" => true
                , "message" => "Annulation de validation au 1er niveau Impossible. Cet engagement ". $engagement->code .", n'est pas à l'état 'Initié'."
            ]);
        }
        
        if ($engagement->statut !== Config::get('gesbudget.variables.statut_engagement.VALIDP')[1]) {
            /** The engagement doesn't have the INIT state */
            return response()->json([
                "error" => true
                , "message" => "Annulation de validation au 1er niveau Impossible. Cet engagement ". $engagement->code .", n'a pas été validé au 1er niveau."
            ]);
        }
        
        if ($engagement->valideur_first !== Auth::user()->matricule) {
             /** The current user validated the engagement */
            return response()->json([
                "error" => true
                , "message" => "Vous ne pouvez pas annuler la validation de l'engagement ". $engagement->code .", Car vous ne l'avez initié."
            ]);
        }

        if ($engagement->next_statut !== null) {
            /** The current user initiated the engagement */
            return response()->json([
                "error" => true
                , "message" => "Vous ne pouvez pas annuler la validation de l'engagement ". $engagement->code .".
                    Il a été renvoyé à l'opérateur de saisie pour modification.
                    Celui-ci doit mettre à jour l'engagement afin que vous puissiez annuler votre validation."
            ]);
        }
        session()->put('CommentEngagement'.Auth::user()->id.$engagementId, $request->comment);
        
        $engagement->update([
            "statut" => Config::get('gesbudget.variables.statut_engagement.SAISI')[1],
            "valideur_first" => null
        ]);
        $engagement = $this->enrichEngagement($engagement->id);
        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Annulation de validation au 1er niveau réussie pour l'engagement ". $engagement->code
            , "data" => $engagement
        ]);
    }

    public function validersPreeng(Request $request){
        $engagementId = $request->id;
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
                , "message" => "Cet engagement ". $engagement->code .", n'est pas à l'état 'Initié' donc ne peut être validé au second niveau."
            ]);
        }

        if ($engagement->valideur_first === Auth::user()->matricule) {
            /** The current user initiated the engagement */
            return response()->json([
                "error" => true
                , "message" => "Vous ne pouvez pas valider au second niveau l'engagement ". $engagement->code ." que vous avez validé au premier niveau."
            ]);
        }

        if ($engagement->next_statut !== null) {
            /** The current user initiated the engagement */
            return response()->json([
                "error" => true
                , "message" => "Vous ne pouvez pas valider l'engagement ". $engagement->code .". Il a été renvoyé à l'opérateur de saisie pour modification.
                    Celui-ci doit mettre à jour l'engagement afin que vous puissiez valider."
            ]);
        }
        session()->put('CommentEngagement'.Auth::user()->id.$engagementId, $request->comment);
        
        $engagement->update([
            "statut" => Config::get('gesbudget.variables.statut_engagement.VALIDS')[1],
            "valideur_second" => Auth::user()->matricule
        ]);
        $engagement = $this->enrichEngagement($engagement->id);
        return response()->json([
            "status" => $this->success_status
            , "success" => true
            , "message" => "Engagement ". $engagement->code ." validé au second niveau avec succès"
            , "data" => $engagement
        ]);
    }

}
