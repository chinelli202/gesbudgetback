<?php

namespace App\Http\Controller;

use App\Http\Controllers\Controller;
use App\Services\ProjetService;
use Illuminate\Http\Request;

class ProjetController  extends Controller{

    protected $projetService;

    public function __construct(ProjetService $projetService) {
        $this->projetService = $projetService;
    }


    public function create(Request $request, ProjetService $service){

    }

    public function findAll(Request $request){

    }
    
}