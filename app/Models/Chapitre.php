<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapitre extends Model
{
    protected $attributes = [
        'statut' => "actif",
        'representation' => 'YDE',
        'entreprise' => 'SNH'
    ];

    public function rubriques(){
        return $this->hasMany('App\Models\Rubrique');
    }

    public function titre(){
        return $this->belongsTo('App\Models\Titre');
    }
}
