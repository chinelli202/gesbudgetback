<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Projet;
use App\Services\ProjetService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

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
}