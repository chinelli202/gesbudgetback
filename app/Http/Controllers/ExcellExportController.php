<?php

namespace App\Http\Controllers;

use App\Services\RecapService;
use App\Utils\ExcellParser;
use App\Utils\MonthExcellParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use stdClass;

class ExcellExportController extends Controller
{
    public function exportRubrique(Request $request, $rubriqueid, ExcellParser $parser, RecapService $recapService){
        $params = $this->validateParams($request, $parser);
        if(!is_null($params)){
            //set filename, request type, set baniere
            $recap = $recapService->getRecapChapitre($rubriqueid, $request->critere, $params);
            $params->baniere = $recap->libelle;
            $params->type = 'rubrique';
            //$params->filename = "rapport_".$params->critere."_".$params->jour.".xlsx";
            //$params->filename = "rapport_".$params->critere."_".$params->jour.".xlsx";
            
            if($params->critere == 'jour'||$params->critere == 'rapport_mensuel'){
                $params->filename = "rapport_".$params->critere."_".$params->jour.".xlsx";
                $parser->toExcell($recap, $params);  
            }

            if($params->critere == 'mois'){
                $params->filename = "rapport_".$params->critere."_".$params->mois.".xlsx";
                $monthparser = new MonthExcellParser();
                $monthparser->toExcell($recap, $params);  
            }

            if($params->critere == 'intervalle'){
                $params->filename = "rapport_".$params->critere."_".$params->startmonth."_".$params->endmonth.".xlsx";
                $intervalleparser = new MonthExcellParser();
                $intervalleparser->toExcell($recap, $params);
            }
           
            return "file correctly saved";
            //return response()->json(["status" => $this->success_status, "success" => true, "data" => $recap]);
        }
        else return "missing or incorrect parameters";
    }

    public function exportChapitre(Request $request, $chapitreid, ExcellParser $parser, RecapService $recapService){
        $params = $this->validateParams($request, $parser);
        if(!is_null($params)){
            //set filename, request type, set baniere
            $recap = $recapService->getRecapChapitre($chapitreid, $request->critere, $params);
            $params->baniere = $recap->libelle;
            $params->type = 'chapitre';
            //$params->filename = "rapport_".$params->critere."_".$params->jour.".xlsx";
            //$params->filename = "rapport_".$params->critere."_".$params->jour.".xlsx";
            
            if($params->critere == 'jour'||$params->critere == 'rapport_mensuel'){
                $params->filename = "rapport_".$params->critere."_".$params->jour.".xlsx";
                $parser->toExcell($recap, $params);  
            }

            if($params->critere == 'mois'){
                $params->filename = "rapport_".$params->critere."_".$params->mois.".xlsx";
                $monthparser = new MonthExcellParser();
                $monthparser->toExcell($recap, $params);  
            }

            if($params->critere == 'intervalle'){
                $params->filename = "rapport_".$params->critere."_".$params->startmonth."_".$params->endmonth.".xlsx";
                $intervalleparser = new MonthExcellParser();
                $intervalleparser->toExcell($recap, $params);
            }
           
            return "file correctly saved";
            //return response()->json(["status" => $this->success_status, "success" => true, "data" => $recap]);
        }
        else return "missing or incorrect parameters";
    } 

    public function exportIntervalleChapitre(){

    }

    public function exportSection(Request $request, $section, $domaine, ExcellParser $parser, RecapService $recapService){
        $params = $this->validateParams($request, $parser);
        if(!is_null($params)){
            //set sectiontype and section name //$parts = explode("-", $section, 2);
            if(in_array($section, $recapService->sections)){
                Log::info("in array ".$section);
                $params->sectiontype = 'section';
            }
            else{
                Log::info("not in array ".$section);
                $params->sectiontype = 'sous_section';
            }
            $params->sectionname = $section;

            //set filename, request type, set baniere
            $params->domaine = $domaine;
            $recap = $recapService->getRecapSection($request->critere, $params);
            $params->baniere = $recap->libelle;
            $params->type = 'rubrique';
            //$params->filename = "rapport_".$params->critere."_".$params->jour.".xlsx";
            //$params->filename = "rapport_".$params->critere."_".$params->jour.".xlsx";
            
            if($params->critere == 'jour'||$params->critere == 'rapport_mensuel'){
                $params->filename = "rapport_".$params->critere."_".$params->jour.".xlsx";
                $parser->toExcell($recap, $params);  
            }

            if($params->critere == 'mois'){
                $params->filename = "rapport_".$params->critere."_".$params->mois.".xlsx";
                $monthparser = new MonthExcellParser();
                $monthparser->toExcell($recap, $params);  
            }

            if($params->critere == 'intervalle'){
                $params->filename = "rapport_".$params->critere."_".$params->startmonth."_".$params->endmonth.".xlsx";
                $intervalleparser = new MonthExcellParser();
                $intervalleparser->toExcell($recap, $params);
            }
           
            return "file correctly saved";
            //return response()->json(["status" => $this->success_status, "success" => true, "data" => $recap]);
        }
        else return "missing or incorrect parameters";
    }

    public function exportIntervalleSection(){

    }

    private function validateParams($request, $parser){
        Log::info("received new request like this ".implode(',', $request->all()));
        
        if(isset($request->critere) && in_array($request->critere, $parser->criteres) && isset($request->param)){
            Log::info("request formed correctly");
            $params = new stdClass();
            if($request->critere == 'jour'){
                //TODO RULE : make sure the given date is before current date and in current year
                    $params->critere = 'jour';
                    $params->jour = $request->param;
                    return $params;
            }
            else if($request->critere == 'mois'){
                //TODO RULE : make sure the given month is before current month
                $params->critere = 'mois';
                $params->mois = $request->param;
                return $params;

            } else if($request->critere == 'rapport_mensuel'){
                //TODO RULE : make sure the given month is before current month
                $params->critere = 'rapport_mensuel';
                $params->mois = $request->param;
                return $params;
            }

             if($request->critere == 'intervalle'){
                //TODO RULE : make sure the given month is before current month
                Log::info("start month ".$request->startmonth.", endmonth : ".$request->endmonth);// implode(',', $request->all()));
                if(isset($request->startmonth) && isset($request->endmonth)){
                    $params->critere = 'intervalle';
                    $params->startmonth = $request->startmonth;
                    $params->endmonth = $request->endmonth;
                    return $params;
                }
                else return null;
            }
        }
        else return null;   
    }
}
