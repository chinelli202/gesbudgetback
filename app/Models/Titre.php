<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Titre extends Model
{
    protected $attributes = [
        'label' => "Libelle Titre",
        'description' => "Description Titre",
        'statut' => 'actif'

    ];

    public function chapitres() {
        return $this->hasMany('App\Models\Chapitre');
    }
}
