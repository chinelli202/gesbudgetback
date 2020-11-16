<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ligne extends Model
{
    protected $attributes = [
        'statut' => "draft"
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

    public function updateStatut(){
        if($this->statut =='draft')
            $this->statut = 'soumis';
        else if($this->statut =='soumis')
            $this->statut = 'validé';
    }
}
