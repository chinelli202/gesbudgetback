<?php

namespace App\Http\Controllers;

use App\Services\RecapService;
use Illuminate\Http\Request;
use stdClass;

class MaquetteTreeController extends Controller
{

    public $success_status = 200;
    
    public function getDepensesFonctionnementTree(RecapService $service){
        $tree = $service->getTree('Fonctionnement','Dépenses', null);
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]); 
    }

    public function getRecettesFonctionnementTree(RecapService $service){   
        $tree = $service->getTree('Fonctionnement','Recettes',null);
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
    }

    public function getDepensesMandatTree(RecapService $service){
        $tree = $service->getTree('Mandat','Dépenses',null);
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
    }

    public function getRecettesMandatTree(RecapService $service){
        $tree = $service->getTree('Mandat','Recettes',null);
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
    }
    public function getFonctionnementTree(RecapService $service){
        $fonctionnement = new stdClass();
        $fonctionnement->depenses = $service->getTree('Fonctionnement','Dépenses',null);
        $fonctionnement->recettes = $service->getTree('Fonctionnement','Recettes',null);
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $fonctionnement]);
    }
    public function getFonctionnementWithSectionsTree (RecapService $service){
        $fonctionnement = new stdClass();
        $recettes = $service->getTree('Fonctionnement','Recettes',null);
        
        $ss_fonctionnement = $service->getTree('Fonctionnement','Fonctionnement', 'Fonctionnement');
        $ss_investissement = $service->getTree('Fonctionnement','Investissement', 'Investissement');

        $depenses = [$ss_fonctionnement, $ss_investissement];
        $fonctionnement->sections  = [$depenses, $recettes];

        return response()->json(["status" => $this->success_status, "success" => true, "data" => $fonctionnement]);
    }
    public function getMandatTree(RecapService $service){
        $mandat = new stdClass();
        $mandat->depenses = $service->getTree('Mandat','Dépenses', null);
        $mandat->recettes = $service->getTree('Mandat','Recettes',null);
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $mandat]);
    }
    public function getGlobalTree(RecapService $service){}
}
