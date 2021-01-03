<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftChapitre extends Model
{
    protected $attributes = [
        'statut' => "draft"
    ];

    public function rubriques(){
        return $this->hasMany('App\Models\DraftRubrique');
    }

    public function titre(){
        return $this->belongsTo('App\Models\DraftTitre');
    }
}
