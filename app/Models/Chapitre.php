<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapitre extends Model
{
    protected $attributes = [
        'statut' => "actif",
        'code_entreprise' => 'SNHSIEGE'
    ];

    public function rubriques(){
        return $this->hasMany('App\Models\Rubrique');
    }

    public function titre(){
        return $this->belongsTo('App\Models\Titre');
    }

    public function entreprise(){
        return $this->belongsTo('App\Models\Entreprise','code_entreprise', 'code');
    }
}
