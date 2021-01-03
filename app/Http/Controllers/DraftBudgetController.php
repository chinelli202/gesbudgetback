<?php

namespace App\Http\Controllers;

use App\Models\Maquette;
use Illuminate\Http\Request;
use App\Services\ChapitreService;
use App\Services\DraftBudgetService;
use App\Services\ElaborationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use stdClass;

class DraftBudgetController extends Controller
{

    public function create(Request $request, ElaborationService $service){
        //create a new budget, build the maquette, return.
        //the maquette will be the only file in a specific folder. empty that folder if there's anything in it before the update
        $saved = $service->save($request);
        $response = new stdClass();
        $response->message = "Budget Created Successfully";
        $response->id = $saved->id;
        return response()->json(["status" => $this->success_status, "success" => true, "data" => $response]);
        return 'success';
    }

    public function initMaquetteprocessing(Request $request, ElaborationService $service){
        //$service->loadMaquette();
        $maquetteid = $request->input('maquetteid');
        //$in =$request->input('maquette');
        //in =$request->getContent();
        Log::info('request data : '.$maquetteid);
        if(!isset($maquette)){
            return 'maquette non fournie';
        }
        else {
            
            return $service->loadMaquette($maquetteid);
        }
    }

    public function getLoadProgress(Request $request, ElaborationService $service) {
        $maquette = $request->input('maquetteid');
        if(!isset($maquette)){
            return 'maquette non fournie';
        }
        else {
            $response = new stdClass();
            $response->id = $maquette->id;
            $response->step = $maquette->step;
            $response->status = $maquette->status;
            $response->name = $maquette->name;
            return response()->json(["status" => $this->success_status, "success" => true, "data" => $response]);
            //return $service->getLoadProgress($maquette);
        }
    } 

    public function upload(Request $request){

        $file = $request->file('maquette');
        if($request->hasFile('maquette')){
            Log::info("did find file in request");
        }
        else {
            Log::info("didnt find file in request");
        }
        foreach($request->input() as $key=>$value){
            //Log::info("input key ".$key." input value ".$value);
            Log::info("input key ".$key);
            if(is_array($value)){
                foreach($value as $elt){
                    Log::info("input array value ".$elt);
                }
            }
            else
                Log::info("input value ".$value);
        }
        $name = $file->getClientOriginalName();
        
        $fileName = 'uf-'.time().'-'.$name;//$file->getClientOriginalExtension().'-'.time().'.'.$file->getClientOriginalExtension();
        
        // $path = $file->store('public/files');

        //save the file
        $path = $file->storeAs('public/files',$fileName);
        
        $maquette = new Maquette();
        $maquette->name = $fileName;
        $maquette->path = $path;
        $maquette->save();
        Log::info('Successfully uploaded file'.$fileName);

        //for now, this maquette returns the maquette's filename
       return $file->name;
    }
}
