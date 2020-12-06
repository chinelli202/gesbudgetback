<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChapitreService;
use App\Services\DraftBudgetService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class DraftBudgetController extends Controller
{
    // public function show(){

    //     $chapitresrf = ChapitreService::findRecettesFonctionnement();
    //     $chapitresdf = ChapitreService::findDepensesFonctionnement();
    //     $chapitresrm = ChapitreService::findRecettesMandat();
    //     $chapitresdm = ChapitreService::findDepensesMandat();

    //     $chapters = ChapitreService::findAll();
    //         return View::make('elaboration.index')
    //        // ->with('chapitres',$chapters)
    //         ->with('chapitresrf',$chapitresrf)
    //         ->with('chapitresdf',$chapitresdf)
    //         ->with('chapitresrm',$chapitresrm)
    //         ->with('chapitresdm',$chapitresdm);
    // }

    public function create(Request $request, DraftBudgetService $service){
        //create a new budget, build the maquette, return.
        //the maquette will be the only file in a specific folder. empty that folder if there's anything in it before the update
        $service->save($request);
        return 'success';
    }

    public function loadMaquette(Request $request, DraftBudgetService $service){
        //$service->loadMaquette();
        $maquette = $request->input('maquette');
        //$in =$request->input('maquette');
        //in =$request->getContent();
        Log::info('request data : '.$maquette);
        if(!isset($maquette)){
            return 'maquette non fournie';
        }
        else {
            return $service->loadMaquette($maquette);
        }
        //return $in;     
    }

    public function loadProgress(Request $request, DraftBudgetService $service) {
        $maquette = $request->input('maquette');
        if(!isset($maquette)){
            return 'maquette non fournie';
        }
        else {
            return $service->getLoadProgress($maquette);
        }
    } 
}
