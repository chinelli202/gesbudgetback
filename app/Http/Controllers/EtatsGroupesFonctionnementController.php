<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use stdClass;

class EtatsGroupesFonctionnementController extends Controller
{

    public $success_status = 200;
    public function getGroupe($groupename){
       
        $group = new stdClass();
        $group->libelle = $groupename;
        $group->realisationsMois = 213888873;
        $group->realisationsPrecedentes = 213888873;
        $group->realisationsCumulees = 213888873;
        $group->engagements = 182777769;
        $group->execution = 396666642;
        $group->solde = 12;
        $group->tauxExecution = 21;

        return response()->json(["status" => $this->success_status, "success" => true, "data" => $group]);
    }
}
