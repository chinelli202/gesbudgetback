<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Titre extends Model
{
    protected $attributes = [
        'label' => "Libelle Titre",
        'description' => "Description Titre",
        'statut' => 'actif',
        'code_entreprise' => 'SNHSIEGE'
    ];

    public function chapitres() {
        return $this->hasMany('App\Models\Chapitre');
    }

    public function entreprise(){
        return $this->belongsTo('App\Models\Entreprise', 'code_entreprise', 'code');
    }
}
