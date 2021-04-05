<?php

namespace App\Http\Controller;

use App\Http\Controllers\Controller;
use App\Services\ProjetService;
use Illuminate\Http\Request;

class ProjetController  extends Controller{

    protected $projetService;
    private $sucess_status = 200;

    public function __construct(ProjetService $projetService) {
        $this->projetService = $projetService;
    }


    public function create(Request $request, ProjetService $service){

    }

    public function findAll(Request $request){
        //params requested by the service here are : entreprise_code, the team the current logged in user belongs to
        
        $projets = $this->projetService->findAll($request->entreprise_code);
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $projets]);
    }
}