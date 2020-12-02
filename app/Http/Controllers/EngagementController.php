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
            'ligne_id'          =>          'required|exists:lignes,id',
            'rubrique_id'       =>          'required|exists:rubriques,id',
            'chapitre_id'       =>          'required|exists:chapitres,id'
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
            'ligne_id'          =>          'required|exists:lignes,id',
            'rubrique_id'       =>          'required|exists:rubriques,id',
            'chapitre_id'       =>          'required|exists:chapitres,id'

        ];
    }

    public function getEngagements(Request $request){
        $requestquery = $request->all();
        $requestqueryKeys = array_keys($requestquery);
        $query = array();
        $lignes = $request->lignes ? array_filter(array_map(function ($el) { return (int) $el;}, explode(',', $request->lignes))) : array();

        $saisisseurs = $request->saisisseurs ? array_filter(explode(',', $request->saisisseurs)) : array();
        $valideurs_first = $request->valideurs_first ? array_filter(explode(',', $request->valideurs_first)) : array();
        $valideurs_second = $request->valideurs_second ? array_filter(explode(',', $request->valideurs_second)) : array();
        $valideurs_final = $request->valideurs_final ? array_filter(explode(',', $request->valideurs_final)) : array();
        
        $statutQuery = array_filter(explode(',', $request->latest_statut));
        $etatQuery = array_filter(explode(',', $request->etat));

        foreach ($requestqueryKeys as $key) {
            $value = $requestquery[$key];
            if(!empty($value) 
                && !in_array($key, array('page','limit', 'lignes', 'etat'
                    , 'saisisseurs', 'valideurs_first', 'valideurs_second', 'valideurs_final', 'latest_statut'))) {
                
                array_push($query, [$key, '=', $value]);
            }
        }
        if (sizeof($query) === 0 && sizeof($statutQuery) === 0 && sizeof($etatQuery) === 0
            && sizeof($lignes) === 0
            && sizeof($saisisseurs) === 0
            && sizeof($valideurs_first) === 0 && sizeof($valideurs_second) === 0 && sizeof($valideurs_final) === 0
        ) {
            $total = Engagement::whereNotIn('etat', [Config::get('gesbudget.variables.etat_engagement.CLOT')[1]])
                ->count();
            $engagements = Engagement::whereNotIn('etat', [Config::get('gesbudget.variables.etat_engagement.CLOT')[1]])
                ->orderBy('latest_edited_at', 'desc')
                ->paginate($request->limit)
                ->map(function ($eng) {
                    return EngagementService::enrichEngagement($eng->id);
                });
        } else {
            $preQuery = Engagement::where($query)
                            ->where(function($q) use (&$etatQuery) {
                                if(sizeof($etatQuery) >0 ) {
                                    $q->whereIn('etat', $etatQuery);
                                }
                            })
                            ->where(function($q) use (&$statutQuery) {
                                if(sizeof($statutQuery) >0 ) {
                                    $q->whereIn('latest_statut', $statutQuery);
                                }
                            })
                            ->where(function($q) use (&$lignes) {
                                if(sizeof($lignes) >0 ) {
                                    $q->whereIn('ligne_id', $lignes);
                                }
                            })
                            ->where(function($q) use (&$saisisseurs) {
                                if(sizeof($saisisseurs) >0 ) {
                                    $q->whereIn('saisisseur', $saisisseurs);
                                }
                            })
                            ->where(function($q) use (&$valideurs_first) {
                                if(sizeof($valideurs_first) >0 ) {
                                    $q->whereIn('valideur_first', $valideurs_first);
                                }
                            })
                            ->where(function($q) use (&$valideurs_second) {
                                if(sizeof($valideurs_second) >0 ) {
                                    $q->whereIn('valideur_second', $valideurs_second);
                                }
                            })
                            ->where(function($q) use (&$valideurs_final) {
                                if(sizeof($valideurs_final) >0 ) {
                                    $q->whereIn('valideur_final', $valideurs_final);
                                }
                            });

            $total = $preQuery->count();   
            $engagements = $preQuery->orderBy('latest_edited_at', 'desc')
                ->paginate($request->limit)
                ->map(function ($eng) {
                    return EngagementService::enrichEngagement($eng->id);
                });
        }
        
        return response()->json([
            "status" => $this->success_status,
            "success" => true,
            "data" => $engagements,
            "total" => $total,
            "query" => $query,
            "lignes" => $lignes,
            "saisisseurs" => $saisisseurs,
            "valideurs_first" => $valideurs_first,
            "valideurs_second" => $valideurs_second,
            "valideurs_final" => $valideurs_final,
            "etatQuery" => sizeof($etatQuery),
            "statutQuery" => sizeof($statutQuery),
        ]);
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
            "code_comptabilite" => $request->type .substr(now()->format('ymd-His-u'),0,17),
            "libelle" => $request->libelle,
            "montant_ttc" => $request->montant_ttc,
            "montant_ht" => $request->montant_ht,
            "devise" => $request->devise,
            "type" => $request->type,
            "nature" => $request->nature,
            
            'etat' => Config::get('gesbudget.variables.etat_engagement.INIT')[1],
            'statut' => Config::get('gesbudget.variables.statut_engagement.SAISI')[1],
            'latest_statut' => Config::get('gesbudget.variables.statut_engagement.SAISI')[1],
            'latest_edited_at' => now(),
            
            'nb_imputations' => 0,
            'cumul_imputations' => 0,
            'nb_apurements' => 0,
            'cumul_apurements' => 0,
            'saisisseur' => Auth::user()->matricule,
            'valideur_first' => null,
            'valideur_second' => null,
            'valideur_final' => null,
            'source' => Config::get('gesbudget.variables.source.API')[0],
            'ligne_id' => $request->ligne_id,
            'rubrique_id' => $request->rubrique_id,
            'chapitre_id' => $request->chapitre_id
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
            "ligne_id" => $request->ligne_id,
            'rubrique_id' => $request->rubrique_id,
            'chapitre_id' => $request->chapitre_id,
            "latest_edited_at" => now()
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
            "latest_edited_at" => now()
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
            "latest_edited_at" => now()
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
            "latest_statut" => Config::get('gesbudget.variables.statut_engagement.SAISI')[1],
            "latest_edited_at" => now(),
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
            "next_statut" => null,
            "latest_edited_at" => now()
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
                "latest_statut" => Config::get('gesbudget.variables.statut_engagement.NEW')[1],
                "latest_edited_at" => now(),
                $operateursKeys[$statutIndice] => Auth::user()->matricule,
                "etat" => $etatsEngagementKeys[
                    $engagement->etat === Config::get('gesbudget.variables.etat_engagement.APUR')[1] ? $etatIndice: $etatIndice + 1]
            ]);
        } else {
            $engagement->update([
                "statut" => $statutsEngagementKeys[$statutIndice],
                "latest_statut" => $statutsEngagementKeys[$statutIndice],
                "latest_edited_at" => now(),
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
                "latest_statut" => $statutsEngagementKeys[$statutIndice - 1],
                "latest_edited_at" => now(),
                $operateursKeys[$statutIndice] => null,
                "etat" => $etatsEngagementKeys[
                    $engagement->etat === Config::get('gesbudget.variables.etat_engagement.APUR')[1] ? $etatIndice : $etatIndice - 1]
            ]);
        } else {
            $engagement->update([
                "statut" => $statutsEngagementKeys[$statutIndice - 1],
                "latest_statut" => $statutsEngagementKeys[$statutIndice - 1],
                "latest_edited_at" => now(),
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
