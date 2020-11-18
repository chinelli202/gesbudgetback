<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Imputation extends Model
{
    use LogsActivity;

    /** Only the 'updated' and `deleted` events will get logged automatically
     * 
     * @var array
    */
    protected static $recordEvents = ['updated', 'deleted'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['engagement_id', 'reference', 'montant_ht', 'montant_ttc', 'devise'
        , 'observations', 'statut', 'saisisseur', 'valideur_first', 'valideur_second', 'valideur_final', 'source'
        , 'next_statut'
    ];

    public function engagement(){
        return $this->belongsTo('App\Models\Engagement');
    }
    
    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->causer = Auth::user();
        $actioncode = '';
        if($eventName === 'updated'){
            // TODO : specify the right description depending on the action
        }
    }
}
