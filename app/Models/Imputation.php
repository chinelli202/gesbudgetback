<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Imputation extends Model
{
    use LogsActivity;

    // Log  all attribute that has changed
    protected static $logAttributes = ['*'];
    
    // Log only attributes that has actually changed after the update
    protected static $logOnlyDirty = true;

    // Prevents from storing empty logs
    protected static $submitEmptyLogs = false;

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
    protected $fillable = ['engagement_id', 'reference', 'montant_ht', 'montant_ttc', 'devise', 'etat'
        , 'observations', 'statut', 'saisisseur', 'valideur_first', 'valideur_second', 'valideur_final', 'source'
        , 'next_statut', 'documents'
    ];

    public function engagement(){
        return $this->belongsTo('App\Models\Engagement', 'engagement_id', 'code');
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        // $commentSessionKey = 'CommentImputation'.Auth::user()->id.$this->id;
        // $activity->comment = session()->pull($commentSessionKey, 'NA');

        if($eventName === 'updated'){
            // TODO : specify the right description depending on the action

            // Handle
            if (isset($activity->properties['attributes']['statut'])) {
                /** The 'Statut' has changed 
                 * So we'll set the description to the corresponding statut's change action : VALIDP, VALIDS, VALIDF 
                */
                $newStatut = $activity->properties['attributes']['statut'];
                $oldStatut = $activity->properties['old']['statut'];

                if ($oldStatut === 'SAISI' && $newStatut === 'VALIDP') {
                    /** This is a Validation at the first level 
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.IMP_VALIDP')[1];
                } else if ($oldStatut === 'VALIDP' && $newStatut === 'VALIDS') {
                    /** This is a validation at the second level 
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.IMP_VALIDS')[1];
                } else if ($oldStatut === 'VALIDS' && $newStatut === 'VALIDF') {
                    /** This is a validation at the final level 
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.IMP_VALIDF')[1];
                } else if ($oldStatut === 'VALIDP' && $newStatut === 'SAISI') {
                    /** This is a Cancelation of a Validation at the first level 
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.IMP_CANCEL_VALIDP')[1];
                } else if ($oldStatut === 'VALIDS' && $newStatut === 'VALIDP') {
                    /** This is a Cancelation of a validation at the second level 
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.IMP_CANCEL_VALIDS')[1];
                } else if ($oldStatut === 'VALIDF' && $newStatut === 'VALIDS') {
                    /** This is a Cancelation of a validation at the final level
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.IMP_CANCEL_VALIDF')[1];
                } else {
                    $activity->description = 'UNKNOWN_STATUT_CHANGE';
                }
            } else if (isset($activity->properties['attributes']['etat'])) {
                $newEtat = $activity->properties['attributes']['etat'];
                $oldEtat = $activity->properties['old']['etat'];
                
                if ($newEtat === 'CLOT') {
                    /** The engagement has been closed
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.IMP_CLOSE')[1];
                } else if ($oldEtat === 'CLOT') {
                    /** The engagement has been closed
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.IMP_RESTORE')[1];
                } else {
                    $activity->description = 'UNKNOWN_ETAT_CHANGE + old:'. $oldEtat . ' new:' . $newEtat;
                }
            } else if (isset($activity->properties['attributes']['next_statut'])) {
                // The 'etat' attribute has changed
                $newNS = $activity->properties['attributes']['next_statut'];
                $oldNS = $activity->properties['old']['next_statut'];

                if(is_null($newNS)) {
                    $activity->description = Config::get('gesbudget.variables.actions.IMP_RESEND')[1] . "_FROM_" .$oldNS;
                } else if (is_null($oldNS)) {
                    $activity->description = Config::get('gesbudget.variables.actions.IMP_SEND_BACK')[1] . "_FOR_" .$newNS;
                } else {
                    $activity->description = 'UNKNOWN_SEND_BACK';
                }
            } else {
                $activity->description = Config::get('gesbudget.variables.actions.IMP_UPDATE')[1];
            }
        }
    }
}
