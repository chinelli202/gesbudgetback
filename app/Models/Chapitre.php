<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapitre extends Model
{
    protected $attributes = [
        'statut' => "actif",
        'entreprise_code' => 'SNHSIEGE'
    ];

    public function rubriques(){
        return $this->hasMany('App\Models\Rubrique');
    }

    public function titre(){
        return $this->belongsTo('App\Models\Titre');
    }

    public function entreprise(){
        return $this->belongsTo('App\Models\Entreprise','entreprise_code', 'code');
    }
}
