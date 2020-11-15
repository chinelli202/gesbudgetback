<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriqueEditionBudget extends Model
{
    public function exericeBudgetaire(){
        return $this->belongsTo('App\Models\ExerciceBudgetaire');
    }

    protected $table = 'historiques_editions_budgets';
}
