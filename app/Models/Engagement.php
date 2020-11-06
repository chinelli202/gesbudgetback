<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Engagement extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'libelle', 'montant_ht', 'montant_ttc', 'devise',  'nature','type', 'etat', 'statut',
        'nb_imputations','cumul_imputations','nb_apurements','cumul_apurements','saisisseur','valideur_first','valideur_second','valideur_final','source'
    ];
}
