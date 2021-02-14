<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use App\Services\RecapService;
use Illuminate\Http\Request;
use stdClass;

class MaquetteTreeController extends Controller
{

    public $success_status = 200;
    
    public function getDepensesFonctionnementTree(RecapService $service){
        $tree = $service->getTree('Fonctionnement','Dépenses', null, null, null);
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]); 
    }

    public function getRecettesFonctionnementTree(RecapService $service){   
        $tree = $service->getTree('Fonctionnement','Recettes',null, null, null);
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
    }

    public function getDepensesMandatTree(RecapService $service){
        $tree = $service->getTree('Mandat','Dépenses',null, null, null);
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
    }

    public function getRecettesMandatTree(RecapService $service){
        $tree = $service->getTree('Mandat','Recettes',null, null, null);
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
    }
    public function getFonctionnementTree(RecapService $service){
        $fonctionnement = new stdClass();
        $fonctionnement->depenses = $service->getTree('Fonctionnement','Dépenses',null, null, null);
        $fonctionnement->recettes = $service->getTree('Fonctionnement','Recettes',null, null, null);
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $fonctionnement]);
    }
    public function getFonctionnementWithSectionsTree (RecapService $service){
        $fonctionnement = new stdClass();
        //$recettes = $service->getTree('Fonctionnement','Recettes',null);
        $fonctionnement->recettes = $service->getTree('Fonctionnement','Recettes',null, null, null);
        
        $ss_fonctionnement = $service->getTree('Fonctionnement','Fonctionnement', 'Fonctionnement', null, null);
        $ss_investissement = $service->getTree('Fonctionnement','Investissement', 'Investissement', null, null);

        $depenses = new stdClass;
        $depenses->section = 'Dépenses';
        $depenses->sections = [$ss_fonctionnement, $ss_investissement];
        $fonctionnement->depenses = $depenses;
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $fonctionnement]);
    }
    public function getMandatTree(RecapService $service){
        $mandat = new stdClass();
        $mandat->depenses = $service->getTree('Mandat','Dépenses', null, null, null);
        $mandat->recettes = $service->getTree('Mandat','Recettes',null, null, null);
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $mandat]);
    }
    public function getGlobalTree(RecapService $service){}

    public function getRepresentationAndEntrepriseTree(Request $request, RecapService $service){
        if($this->validateParams($request)){
            $tree = new stdClass();
            $tree = $service->getTree('Mandat','Dépenses', null, $request->entreprise, $request->representation);
            return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
        }

        else    
            return "badly formed request";
    }

    public function getEntrepriseTree(RecapService $service, $entrepriseid){

        $entreprise = Entreprise::find($entrepriseid);
        if(!isEmpty($entreprise)){
            $tree = new stdClass();
            $tree->levels = 3;
            if($entreprise->hasDomains){
                $tree->levels = 4;
                $domaines = [];
            }
            else{
    
            }
        }
        else    
            return "badly formed request";

        // if($this->validateParams($request)){
        //     $tree = new stdClass();
        //     $tree = $service->getTree('Mandat','Dépenses', null, $request->entreprise, $request->representation);
        //     return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
        // }

        // else    
        //     return "badly formed request";
    }

    private function validateParams(Request $request){
        if(isset($request->representation) && isset($request->entreprise) && in_array($request->representation, ["DLA","YDE"]) 
        && in_array($request->entreprise, ["SNH","CPSP"])){
            return true;
        }
        else 
            return null;
    }
}
