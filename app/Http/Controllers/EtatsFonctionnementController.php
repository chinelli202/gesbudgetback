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

    public function getMonthsRecapRubrique(RecapService $recapService, $rubriqueid, Request $request){
        $months = [];
        $params = $this->validateParams($request, $recapService);
        if(!is_null($params)){
            for($i = $params->startmonth; $i <= $params->endmonth; $i++){
                $params->mois = $i;
                $monthrecap = $recapService->getRecapRubrique($rubriqueid, 'mois', $params);
                array_push($months, $monthrecap);
            }
            $recap = new stdClass();
            $recap->months = $months;
            return response()->json(["status" => $this->success_status, "success" => true, "data" => $recap]);
        }
        else return "missiong or incorrect parameters";
        //return $this->getMonthsRecapData($recapService, $rubriqueid, $recapService->getRecapRubrique, $request);
    }

    public function getRecapRubriqueGroupe(RecapService $recapService, $groupename, Request $request){
        $params = $this->validateParams($request, $recapService);
        //validate groupename, make sure its formatted correctly
        $formatedname = str_replace("+", " ", $groupename);
        if(!is_null($params)){
            $recap = $recapService->getRecapRubriqueGroup($formatedname, $request->critere, $params);
            return response()->json(["status" => $this->success_status, "success" => true, "data" => $recap]);
        }
        else return "missing or incorrect parameters";
    }

    public function getMonthsRecapRubriqueGroupe(RecapService $recapService, $groupename, Request $request){
        $months = [];
        $params = $this->validateParams($request, $recapService);
        $formatedname = str_replace("+", " ", $groupename);
        if(!is_null($params)){
            for($i = $params->startmonth; $i <= $params->endmonth; $i++){
                $params->mois = $i;
                $monthrecap = $recapService->getRecapRubriqueGroup($formatedname, 'mois', $params);
                array_push($months, $monthrecap);
            }
            $recap = new stdClass();
            $recap->months = $months;
            return response()->json(["status" => $this->success_status, "success" => true, "data" => $recap]);
        }
        else return "missiong or incorrect parameters";
    }

    public function getRecapChapitre(RecapService $recapService, $chapitreid, Request $request){
        $params = $this->validateParams($request, $recapService);
        if(!is_null($params)){
            $recap = $recapService->getRecapChapitre($chapitreid, $request->critere, $params);
            return response()->json(["status" => $this->success_status, "success" => true, "data" => $recap]);
        }
        else return "missing or incorrect parameters";
    }

    public function getRecapEntreprise(RecapService $recapService, $entreprisecode, Request $request){
        $params = $this->validateParams($request, $recapService);
        if(!is_null($params)){
            $recap = $recapService->getRecapEntreprise($entreprisecode, $request->critere, $params);
            
            return response()->json(["status" => $this->success_status, "success" => true, "data" => $recap]);
        }
        else return "missing or incorrect parameters";
    }

    public function getMonthsRecapChapitre(RecapService $recapService, $chapitreid, Request $request){
        $months = [];
        $params = $this->validateParams($request, $recapService);
        if(!is_null($params)){
            for($i = $params->startmonth; $i <= $params->endmonth; $i++){
                $params->mois = $i;
                $monthrecap = $recapService->getRecapChapitre($chapitreid, 'mois', $params);
                array_push($months, $monthrecap);
            }
            $recap = new stdClass();
            $recap->months = $months;
            return response()->json(["status" => $this->success_status, "success" => true, "data" => $recap]);
        }
        else return "missiong or incorrect parameters";
    }

    public function getRecapLigne(RecapService $recapService, $ligneid, Request $request){
        $params = $this->validateParams($request, $recapService);
        if(!is_null($params)){
            $recap = $recapService->getRecapLigne($ligneid, $request->critere, $params);
            return response()->json(["status" => $this->success_status, "success" => true, "data" => $recap]);
        }
        else return "missing or incorrect parameters";
    }

    public function getMonthsRecapLigne(RecapService $recapService, $ligneid, Request $request){
        $months = [];
        $params = $this->validateParams($request, $recapService);
        if(!is_null($params)){
            for($i = $params->startmonth; $i <= $params->endmonth; $i++){
                $params->mois = $i;
                $monthrecap = $recapService->getRecapLigne($ligneid, 'mois', $params);
                array_push($months, $monthrecap);
            }
            $recap = new stdClass();
            $recap->months = $months;
            return response()->json(["status" => $this->success_status, "success" => true, "data" => $recap]);
        }
        else return "missiong or incorrect parameters";
    }

    public function getRecapSection(RecapService $recapService, $section, $domaine, Request $request){
        $params = $this->validateParams($request, $recapService);
        if(!is_null($params)){
            if(in_array($section, $recapService->sections)){
                Log::info("in array ".$section);
                $params->sectiontype = 'section';
            }
            else{
                Log::info("in array ".$section);
                $params->sectiontype = 'sous_section';
            }
            $params->sectionname = $section;
            //$params->section = $sectionname;
            $params->domaine = $domaine;
            $recap = $recapService->getRecapSection($request->critere, $params);
            return response()->json(["status" => $this->success_status, "success" => true, "data" => $recap]);
        }
        else return "missing or incorrect parameters";
    }

    private function validateParams($request, $recapService){
        Log::info("received new request like this ".implode(',', $request->all()));
        if(isset($request->critere) && in_array($request->critere, $recapService->criteres) && isset($request->param)){
            Log::info("request formed correctly");
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

             if($request->critere == 'intervalle'){
                //TODO RULE : make sure the given month is before current month
                Log::info("start month ".$request->startmonth.", endmonth : ".$request->endmonth);// implode(',', $request->all()));
                if(isset($request->startmonth) && isset($request->endmonth)){
                    $params->startmonth = $request->startmonth;
                    $params->endmonth = $request->endmonth;
                    return $params;
                }
                else return null;
            }
        }
        else return null;   
    }

    public function getMonthsRecapData(RecapService $service, $idparameter, callable $recapfunction, Request $request){
        $months = [];
        $params = $this->validateParams($request, $service);
        if(!is_null($params)){
            for($i = $params->startmonth; $i <= $params->endmonth; $i++){
                $params->mois = $i;
                $monthrecap = $recapfunction($idparameter, 'mois', $params);//$service->getRecapRubrique($service, 'mois', $params);
                array_push($months, $monthrecap);
            }
            $recap = new stdClass();
            $recap->months = $months;
            return response()->json(["status" => $this->success_status, "success" => true, "data" => $recap]);
        }
        else return "missiong or incorrect parameters";
    }
}
