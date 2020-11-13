<?php

namespace App\Http\Controllers;

use App\Services\RecapService;
use Illuminate\Http\Request;
use stdClass;

class EtatsFonctionnementController extends Controller
{
    public $success_status = 200;
    
    
    public function getRecapSousSectionFonctionnement(RecapService $recapService){
        $recapssfonctionnement = $recapService->getRecapSousSectionFonctionnement(null,null);
        $recapssfonctionnement->libelle = "Fonctionnement";
        
        
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $recapssfonctionnement]);
    }
}
