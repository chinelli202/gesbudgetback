<?php

namespace App\Http\Controllers;

use App\Services\RecapService;
use Illuminate\Http\Request;
use stdClass;

class EtatsFonctionnementController extends Controller
{
    public $success_status = 200;
    
    
    public function getRecapSousSectionFonctionnement(RecapService $recapService){
        //$recapssfonctionnement = $recapService->getRecapSousSectionFonctionnement(null,null);
        //$recapssfonctionnement->libelle = "Fonctionnement";
        
        
        $group = new stdClass();
        $group->libelle = "Fonctionnement";
        $group->realisationsMois = 213888873;
        $group->realisationsPrecedentes = 213888873;
        $group->realisationsCumulees = 213888873;
        $group->engagements = 182777769;
        $group->execution = 396666642;
        $group->solde = 12;
        $group->tauxExecution = 21;
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $group]);
    }
}
