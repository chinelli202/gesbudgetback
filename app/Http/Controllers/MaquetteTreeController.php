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
                else{ // a few upgrades
                      // first, we'll collect the sections data, and then evaluate. If more than one section is found to have data in it, we'll 
                      // organize the entreprise data in sections. If not, we'll check if the sections has more than one chapitre. If that is the case,
                      // then we'll organize the entreprise data in chapitres or in rubriques.

                    //$tree->content = $service->getTree(null,'Dépenses', null, $request->entreprise_code);
                    $tree->levels = 4;
                    $content = new stdClass();
                    $sections = [];
                    $depenses = $service->getTree("Fonctionnement",'Dépenses', null, $request->entreprise_code);
                    $recettes = $service->getTree("Fonctionnement",'Recettes', null, $request->entreprise_code);
                    if (!empty($depenses->chapitres))
                        array_push($sections, $depenses);
                    if (!empty($recettes->chapitres))
                        array_push($sections, $recettes);
                    
                    if(count([$sections]) > 1){
                        $content->group = "sections";
                        $content->sections = $sections;

                    }

                    if(count($sections[0]->chapitres) > 1 ){
                        if(count($sections[0]->chapitres) < 7 ){
                            $content->group = "chapitres";
                        }
                        else{
                            $content->group = "rubriques";
                        }
                    }

                    else{
                        $content->group = "rubriques";
                    }
                    $tree->content = $content;

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
        if(isset($request->entreprise_code) && in_array($request->entreprise_code, ["SNHDLA","SNHSIEGE", "CPSP","SNHKRIBI","ASCH"])){
            return true;
        }
        else 
            return null;
    }
}
