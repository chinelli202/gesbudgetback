<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rubrique extends Model
{
    protected $attributes = [
        'statut' => "actif"
    ];

    public function lignes(){
        return $this->hasMany('App\Models\Ligne');
    }

    public function chapitre(){
        return $this->belongsTo('App\Models\Chapitre');
    }

    public function entreprise(){
        return $this->belongsTo('App\Models\Entreprise');
    }

    public function updateStatut(){
        if($this->statut =='draft')
            $this->statut = 'soumis';
        else if($this->statut =='soumis')
            $this->statut = 'validé';
	}
}
