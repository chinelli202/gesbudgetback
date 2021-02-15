<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ligne extends Model
{
    protected $attributes = [
        'statut' => "actif",
        'code_entreprise' => 'SNHSIEGE'
    ];
    public function rubrique(){
        return $this->belongsTo('App\Models\Rubrique');
    }

    public function exerciceBudgetaire(){
        return $this->belongsTo('App\Models\ExerciceBudgetaire');
    }

    public function engagements(){
        return $this->hasMany('App\Models\Engagement');
    }

    public function parent(){
        return $this->belongsTo('App\Models\Ligne', 'parent_id', 'id');
    }

    public function sousLignes(){
        return $this->hasMany('App\Models\Ligne', 'parent_id', 'id');
    }

    public function projets(){
        return $this->hasMany('App\Models\Projet');
    }

    public function entreprise(){
        return $this->belongsTo('App\Models\Entreprise', 'code_entreprise', 'code');
    }

    public function updateStatut(){
        if($this->statut =='draft')
            $this->statut = 'soumis';
        else if($this->statut =='soumis')
            $this->statut = 'validé';
    }
}
