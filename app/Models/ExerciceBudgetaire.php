<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciceBudgetaire extends Model
{
    protected $attributes =[
        'annee_vote' => "1900",
        'date_vote' => "2020-10-26",
        'label' => "Exercice budgétaire",
        'date_debut' => "2021-01-01",
        'date_cloture' => "2021-12-31"
    ];
    public function lignes(){
        return $this->hasMany('App\Models\ligne');
    }

    public function historiques(){
        return $this->hasMany('App\Models\HistoriqueEditionBudget');
    }

    public function lignesArchivees(){
        return $this->hasMany('App\Models\LigneArchivee');
    }

    protected $table = 'exercices_budgetaires';
}
