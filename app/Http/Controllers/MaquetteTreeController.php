<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use App\Services\RecapService;
use Illuminate\Http\Request;
use stdClass;

class MaquetteTreeController extends Controller
{

    public $success_status = 200;
    
    public function getDepensesFonctionnementTree(Request $request, RecapService $service){
        $tree = $service->getTree('Fonctionnement','Dépenses', null, null, $request->entreprise_code);
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]); 
    }

    public function getRecettesFonctionnementTree(Request $request, RecapService $service){   
        $tree = $service->getTree('Fonctionnement','Recettes',null, null, null);
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
    }

    public function getDepensesMandatTree(Request $request, RecapService $service){
        $tree = $service->getTree('Mandat','Dépenses',null, null, $request->entreprise_code);
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
    }

    public function getRecettesMandatTree(Request $request, RecapService $service){
        $tree = $service->getTree('Mandat','Recettes',null, null, $request->entreprise_code);
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
    }

    public function getFonctionnementTree(Request $request, RecapService $service){
        $fonctionnement = new stdClass();
        $fonctionnement->depenses = $service->getTree('Fonctionnement','Dépenses', null, $request->entreprise_code);
        $fonctionnement->recettes = $service->getTree('Fonctionnement','Recettes', null, $request->entreprise_code);
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $fonctionnement]);
    }

    public function getFonctionnementWithSectionsTree (Request $request, RecapService $service){
        $fonctionnement = new stdClass();
        //$recettes = $service->getTree('Fonctionnement','Recettes',null);
        $fonctionnement->recettes = $service->getTree('Fonctionnement','Recettes', null, $request->entreprise_code);
        
        $ss_fonctionnement = $service->getTree('Fonctionnement','Fonctionnement', 'Fonctionnement', $request->entreprise_code);
        $ss_investissement = $service->getTree('Fonctionnement','Investissement', 'Investissement', $request->entreprise_code);

        $depenses = new stdClass;
        $depenses->section = 'Dépenses';
        $depenses->sections = [$ss_fonctionnement, $ss_investissement];
        $fonctionnement->depenses = $depenses;
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $fonctionnement]);
    }

    public function getMandatTree(Request $request, RecapService $service){
        $mandat = new stdClass();
        $mandat->depenses = $service->getTree('Mandat','Dépenses', null, $request->entreprise_code);
        $mandat->recettes = $service->getTree('Mandat','Recettes',null, $request->entreprise_code);
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

    public function getEntrepriseTree(RecapService $service, Request $request){

        if($this->validateParams($request)){
            // $tree = new stdClass();
            // $tree = $service->getTree('Mandat','Dépenses', null, $request->entreprise, $request->representation);
            // return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
            $entreprise = Entreprise::where('code',$request->entreprise_code)->first();//find($entrepriseid);
            if(!empty($entreprise)){
                $tree = new stdClass();
                $tree->levels = 3;
                if($entreprise->hasDomaines){
                    $tree->levels = 4;
                 //   $domaines = [];
                    $mandat = new stdClass();
                    $mandat->name = 'Mandat';
                    //$mandat->depenses = $service->getTree('Mandat','Dépenses', null, $request->entreprise_code);
                    //$mandat->recettes = $service->getTree('Mandat','Recettes',null, $request->entreprise_code); 
                    $mandat = $service->getTree('Mandat',null, null, $request->entreprise_code); 
                    $content = new stdClass();
                    
                    $fonctionnement = new stdClass();
                    $fonctionnement->name = 'Fonctionnement';   
                    //$fonctionnement->depenses = $service->getTree('Fonctionnement','Dépenses',null, $request->entreprise_code);
                    //$fonctionnement->recettes = $service->getTree('Fonctionnement','Recettes',null, $request->entreprise_code);
                    $fonctionnement = $service->getTree('Fonctionnement',null ,null, $request->entreprise_code);
                    
                    $content->fonctionnement = $fonctionnement;
                    $content->mandat = $mandat;
                    //$domaines = [$fonctionnement, $mandat];
    
                    //$tree->domaines = $domaines;
                    $tree->content = $content;
                    return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
                }
                else{
                    $tree->content = $service->getTree(null,'Dépenses', null, $request->entreprise_code);
                    return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
                }
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
        if(isset($request->entreprise_code) && in_array($request->entreprise_code, ["SNHDOUALA","SNHSIEGE", "CPSP","SNHKRIBI"])){
            return true;
        }
        else 
            return null;
    }
}
