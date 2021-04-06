<?php

namespace App\Services;
use App\Models\Projet;
use Illuminate\Support\Facades\DB;

class ProjetService{

    //crud methods
    public function find($id){
        $projet = Projet::find($id);
        return $projet;
    }

    public function findAll($entreprise_code){
        $projets = DB::table('projets')
            ->join('chapitres', 'projets.chapitre_id', '=', 'chapitres.id')
            ->select('projets.label', 'projets.description', 'projets.id', 'projets.chapitre_id','chapitres.label as chapitre_label','chapitres.description as chapitre_descritption', 'chapitres.entreprise_code')
            ->where('chapitres.entreprise_code', $entreprise_code)->get();
        return $projets;
    }

    public function delete($id){
        $projet = Projet::find($id);
        $projet->delete();
    }

    public function save($projet_p){
        $projet_r = new Projet();
        if(isset($projet_p->id)){
            $projet_r = Projet::find($projet_p->id);
            if(empty($projet_r)){
                return null;
            }
        }
        $projet_r->label = $projet_p->label;
        $projet_r->description = $projet_p->description;
        $projet_r->chapitre_id = $projet_p->chapitre_id;
        return $projet_r->save();
    }
}