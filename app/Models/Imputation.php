<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Imputation extends Model
{
    use LogsActivity;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'documents' => 'array'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['engagement_id', 'reference', 'montant_ht', 'montant_ttc', 'devise'
        , 'observations', 'statut', 'saisisseur', 'valideur_first', 'valideur_second', 'valideur_final', 'source'
        , 'next_statut', 'documents'
    ];

    public function engagement(){
        return $this->belongsTo('App\Models\Engagement', 'engagement_id', 'code');
    }
}
