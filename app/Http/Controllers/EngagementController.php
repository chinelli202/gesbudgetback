<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Engagement;
use App\Models\User;
use App\Models\Variable;
use Illuminate\Support\Facades\Config;

class EngagementController extends Controller
{
    private $sucess_status = 200;

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
}
