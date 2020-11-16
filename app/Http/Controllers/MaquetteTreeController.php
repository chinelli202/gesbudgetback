<?php

namespace App\Http\Controllers;

use App\Services\RecapService;
use Illuminate\Http\Request;
use stdClass;

class MaquetteTreeController extends Controller
{

    public $success_status = 200;
    
    public function getDepensesFonctionnementTree(RecapService $service){
        $tree = $service->getTree('Fonctionnement','Dépenses');
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]); 
    }

    public function getRecettesFonctionnementTree(RecapService $service){   
        $tree = $service->getTree('Fonctionnement','Recettes');
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
    }

    public function getDepensesMandatTree(RecapService $service){
        $tree = $service->getTree('Mandat','Dépenses');
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
    }

    public function getRecettesMandatTree(RecapService $service){
        $tree = $service->getTree('Mandat','Recettes');
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
    }
    public function getFonctionnementTree(RecapService $service){
        $fonctionnement = new stdClass();
        $fonctionnement->depenses = $service->getTree('Fonctionnement','Dépenses');
        $fonctionnement->recettes = $service->getTree('Fonctionnement','Recettes');
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $fonctionnement]);
    }
    public function getMandatTree(RecapService $service){
        $mandat = new stdClass();
        $mandat->depenses = $service->getTree('Mandat','Dépenses');
        $mandat->recettes = $service->getTree('Mandat','Recettes');
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $mandat]);
    }
    public function getGlobalTree(RecapService $service){}
}
