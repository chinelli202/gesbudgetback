<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LigneArchivee extends Model
{
    public function exerciceBudgetaire(){
        return $this->belongsTo('App\Models\ExerciceBudgetaire');
    }
    protected $table = 'lignes_archivees';
}
