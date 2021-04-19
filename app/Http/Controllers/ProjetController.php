<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use App\Models\Projet;
use App\Services\ProjetService;
use App\Services\RecapService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use stdClass;

class ProjetController  extends Controller{

    protected $projetService;
    private $success_status = 200;
    protected $projetCreateValidator;

    public function __construct(ProjetService $projetService) {
        $this->projetService = $projetService;

        $this->projetCreateValidator = [

        ];

        
    }


    public function create(Request $request, ProjetService $service){

        $validator = Validator::make($request->all(), [
            'label' => 'required|max:255',
            'description' => 'required',
            'chapitre_id' => 'required'
        ]);
        
        
        if ($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors()]);
        }
        
        $projet = new Projet();
        $projet->label = $request->label;
        $projet->description = $request->description;
        $projet->chapitre_id = $request->chapitre_id;
        $projet_result = $service->save($projet);
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $projet_result]);
       
        if(is_null($projet_result)){
            return response()->json(["error_message" => "save error"]);
        }
        else{
            response()->json(["status" => $this->success_status, "success" => true, "data" => $projet_result]);
        }
    }

    public function index(Request $request){
        //params requested by the service here are : entreprise_code, the team the current logged in user belongs to
        
        $projets = $this->projetService->findAll($request->entreprise_code);
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $projets]);
    }

    public function getMaquette(RecapService $service, Request $request){
        if($this->validateMaquetteParams($request)){
            // $tree = new stdClass();
            // $tree = $service->getTree('Mandat','Dépenses', null, $request->entreprise, $request->representation);
            // return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
            $entreprise = Entreprise::where('code',$request->entreprise_code)->first();//find($entrepriseid);
            if(!empty($entreprise)){
                $tree = new stdClass();
                $tree->levels = 3;
                if($entreprise->hasDomaines){
                    $tree->levels = 4;
                    $groupes = [];
                    $mandat = new stdClass();
                    $mandat->name = 'Mandat';
                    //$mandat->depenses = $service->getTree('Mandat','Dépenses', null, $request->entreprise_code);
                    //$mandat->recettes = $service->getTree('Mandat','Recettes',null, $request->entreprise_code); 
                    $mandat->chapitres = $service->getTree('Mandat',null, null, $request->entreprise_code)->chapitres; 
                    $content = new stdClass();
                    
                    $fonctionnement = new stdClass();
                    $fonctionnement->name = 'Fonctionnement';   
                    //$fonctionnement->depenses = $service->getTree('Fonctionnement','Dépenses',null, $request->entreprise_code);
                    //$fonctionnement->recettes = $service->getTree('Fonctionnement','Recettes',null, $request->entreprise_code);
                    $fonctionnement->chapitres = $service->getTree('Fonctionnement',null ,null, $request->entreprise_code)->chapitres;
                    

                    //$content->fonctionnement = $fonctionnement;
                    //$content->mandat = $mandat;
                    $groupes = [$fonctionnement, $mandat];
                    $content->groupes = $groupes;
                    //$tree->domaines = $domaines;
                    $tree->content = $content;
                    return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
                }
                else{ 
                    //$tree->content = $service->getTree(null,'Dépenses', null, $request->entreprise_code);
                    $tree->levels = 4;
                    $content = new stdClass();
                    $groupes = [];
                    $depenses = new stdClass();
                    $recettes = new stdClass();
                    $depenses->name = "Dépenses";
                    $recettes->name = "Recettes";
                    $depenses->chapitres = $service->getTree(null,'Dépenses', null, $request->entreprise_code);
                    $recettes->chapitres = $service->getTree(null,'Recettes', null, $request->entreprise_code);
                    if (!empty($depenses->chapitres))
                        array_push($sections, $depenses);
                    if (!empty($recettes->chapitres))
                        array_push($sections, $recettes);
                    
                    $content->groupes = $groupes;
                    $tree->content = $content;

                    return response()->json(["status" => $this->success_status, "success" => true, "data" => $tree]);
                }
            }
        }

        else    
            return "badly formed request";
    }

    private function validateMaquetteParams(Request $request){
        if(isset($request->entreprise_code) && in_array($request->entreprise_code, ["SNHDOUALA","SNHSIEGE", "CPSP","SNHKRIBI","ASCH"])){
            return true;
        }
        else 
            return null;
    }
}