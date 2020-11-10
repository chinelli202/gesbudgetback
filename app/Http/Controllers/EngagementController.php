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
    private $sucess_status = 200;
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

    public function getEngagements(Request $request){
        $etat = $request->etat;

        $engagements = Engagement::where('etat', $etat)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(["status" => $this->sucess_status, "success" => true, "data" => $engagements]);
    }

    public function getEngagement(Request $request){
        $engagementId = $request->id;

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

        return response()->json(["status" => $this->sucess_status, "success" => true, "data" => $engagement]);
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
        return response()->json(["status" => $this->sucess_status, "success" => true, "message" => "Engagement '". $engagement->code ."'mis à jour avec succès'"]);
    }

    public function close(Request $request){
        $engagementId = $request->id;
        $engagement = Engagement::findOrFail($engagementId);
        session(['Engagement'.$engagementId, $request->commentaire]);
        
        $engagement->update([
            "etat" => Config::get('gesbudget.variables.actions.CLOSE')[0],
        ]);
        return response()->json(["status" => $this->sucess_status, "success" => true, "message" => "Engagement '". $engagement->code ."'clôturé avec succès'"]);
    }

    public function resendUpdate(Request $request){
        $engagementId = $request->id;
        $validator = Validator::make($request->all(), $this->engagementUpdateValidator);

        if($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors()]);
        }

        $engagement = Engagement::findOrFail($engagementId);

        /** TODO : do something with the comment 
         * $request->commentaire
        */

        $engagement->update([
            "libelle" => $request->libelle,
            "montant_ttc" => $request->montant_ttc,
            "montant_ht" => $request->montant_ht,
            "devise" => $request->devise,
            "nature" => $request->devise,
            "type" => $request->type,
            "next_statut" => null
        ]);
        return response()->json(["status" => $this->sucess_status, "success" => true, "message" => "Engagement '". $engagement->code ."'mis à jour avec succès'"]);
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
            ->log(Config::get('gesbudget.variables.actions.ADD_COMMENT')[0]);

        return response()->json([
            "status" => $this->sucess_status
            , "success" => true
            , "message" => "Engagement '". $engagement->code ."'mis à jour avec succès'"
        ]);
    }

    public function sendBack(Request $request){
        $engagementId = $request->id;
        $engagement = Engagement::findOrFail($engagementId);

        /** TODO : do something with the comment 
         * $request->commentaire
        */

        $engagement->update([
            "next_statut" => null
        ]);
        return response()->json(["status" => $this->sucess_status, "success" => true, "message" => "Engagement '". $engagement->code ."'mis à jour avec succès'"]);
    }
}
