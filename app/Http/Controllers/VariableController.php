<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Variable;
use Illuminate\Support\Facades\Log;

class VariableController extends Controller
{
    private $sucess_status = 200;

    public function getVariables(Request $request){
        $cle = $request->cle;
        $code = $request->code;
        $filter = [];

        if($cle){
            array_push($filter, ['cle', '=', $cle]);
        }
        if($code){
            array_push($filter, ['code', '=', $code]);
        }
        $variables = Variable::where($filter)
            ->orderBy('code')
            ->get();
        
        
        Log::info('avant denvoyer la reponse VariableController '. json_encode($variables));
        return response()->json(["status" => $this->sucess_status, "success" => true, "data" => $variables]);
    }
}