<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriqueExecutionBudget extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'editeur', 'type_edition', 'id_objet_edite', 'type_objet_edite', 'commentaire'
    ];
}
