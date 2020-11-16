<?php

namespace App\Http\Controllers;

use App\Services\RecapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use stdClass;

class EtatsFonctionnementController extends Controller
{
    public $success_status = 200;
    
    
    public function getRecapSousSectionFonctionnement(RecapService $recapService){
        $recapssfonctionnement = $recapService->getRecapSousSectionFonctionnement(null,null);
        $recapssfonctionnement->libelle = "Fonctionnement";
        
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $recapssfonctionnement]);
    }

    public function getRecapRubrique(RecapService $recapService, $rubriqueid, Request $request){
        //prepare parameters
        $params = $this->validateParams($request, $recapService);
        if(!is_null($params)){
            $recap = $recapService->getRecapRubrique($rubriqueid, $request->critere, $params);
            return response()->json(["status" => $this->success_status, "success" => true, "data" => $recap]);
        }
        else return "missing or incorrect parameters";
    }   

    public function getMonthsRecapRubrique($rubriqueid){

    }

    public function getRecapRubriqueGroupe(RecapService $recapService, $groupename, Request $request){
        $params = $this->validateParams($request, $recapService);
        if(!is_null($params)){
            $recap = $recapService->getRecapRubriqueGroup($groupename, $request->critere, $params);
            return response()->json(["status" => $this->success_status, "success" => true, "data" => $recap]);
        }
        else return "missing or incorrect parameters";
    }

    public function getMonthsRecapRubriqueGroupe($groupename){

    }

    public function getRecapChapitre(RecapService $recapService, $chapitreid, Request $request){
        $params = $this->validateParams($request, $recapService);
        if(!is_null($params)){
            $recap = $recapService->getRecapChapitre($chapitreid, $request->critere, $params);
            return response()->json(["status" => $this->success_status, "success" => true, "data" => $recap]);
        }
        else return "missing or incorrect parameters";
    }

    public function getMonthRecapChapitre($chapitreid){

    }

    public function getRecapLigne(RecapService $recapService, $ligneid, Request $request){
        $params = $this->validateParams($request, $recapService);
        if(!is_null($params)){
            $recap = $recapService->getRecapLigne($ligneid, $request->critere, $params);
            return response()->json(["status" => $this->success_status, "success" => true, "data" => $recap]);
        }
        else return "missing or incorrect parameters";
    }

    public function getMonthRecapLigne($ligneid){

    }

    private function validateParams($request, $recapService){
        Log::info("received new request like this".implode(',', $request->all()));
        if(isset($request->critere) && in_array($request->critere, $recapService->criteres) && isset($request->param)){
            $params = new stdClass();
            if($request->critere == 'jour'){
                //TODO RULE : make sure the given date is before current date and in current year
                    $params->jour = $request->param;
                    return $params;
            }
            else if($request->critere == 'mois'){
                //TODO RULE : make sure the given month is before current month
                $params->mois = $request->param;
                return $params;

            } else if($request->critere == 'rapport_mensuel'){
                //TODO RULE : make sure the given month is before current month
                $params->mois = $request->param;
                return $params;
            }
        }
        else return null;   
    }
}
