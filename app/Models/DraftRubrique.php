<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftRubrique extends Model
{
    protected $attributes = [
        'statut' => "draft"
    ];

    public function lignes(){
        return $this->hasMany('App\Models\DraftLigne');
    }

    public function chapitre(){
        return $this->belongsTo('App\Models\DraftChapitre');
    }

    public function updateStatut(){
        if($this->statut =='draft')
            $this->statut = 'soumis';
        else if($this->statut =='soumis')
            $this->statut = 'validé';
	}
}
