<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Engagement;

class Apurement extends Model {
    
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
    protected $fillable = ['engagement_id', 'libelle', 'reference_paiement', 'type_paiement', 'montant_ht', 'montant_ttc', 'devise'
        , 'observations', 'statut', 'etat', 'saisisseur', 'valideur_first', 'valideur_second', 'valideur_final', 'source'
        , 'next_statut', 'documents', 'entreprise_code'
    ];

    public function engagement(){
        return $this->belongsTo('App\Models\Engagement', 'engagement_id', 'code');
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        // $commentSessionKey = 'CommentApurement'.Auth::user()->id.$this->id;
        // $activity->comment = session()->pull($commentSessionKey, 'NA');

        if($eventName === 'updated'){
            // TODO : specify the right description depending on the action
            // Handle
            if (isset($activity->properties['attributes']['statut'])) {
                /** The 'statut' has changed so we also change the 'latest_statut' and 'latest_edited_at'
                 * attributes of the corresponding engagement
                 */
                $newStatut = $activity->properties['attributes']['statut'];
                $oldStatut = $activity->properties['old']['statut'];

                /** The 'Statut' has changed 
                 * So we'll set the description to the corresponding statut's change action : VALIDP, VALIDS, VALIDF 
                */

                if ($oldStatut === 'SAISI' && $newStatut === 'VALIDP') {
                    /** This is a Validation at the first level 
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.APUR_VALIDP')[1];
                } else if ($oldStatut === 'VALIDP' && $newStatut === 'VALIDS') {
                    /** This is a validation at the second level 
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.APUR_VALIDS')[1];
                } else if ($oldStatut === 'VALIDS' && $newStatut === 'VALIDF') {
                    /** This is a validation at the final level 
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.APUR_VALIDF')[1];
                } else if ($oldStatut === 'VALIDP' && $newStatut === 'SAISI') {
                    /** This is a Cancelation of a Validation at the first level 
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.APUR_CANCEL_VALIDP')[1];
                } else if ($oldStatut === 'VALIDS' && $newStatut === 'VALIDP') {
                    /** This is a Cancelation of a validation at the second level 
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.APUR_CANCEL_VALIDS')[1];
                } else if ($oldStatut === 'VALIDF' && $newStatut === 'VALIDS') {
                    /** This is a Cancelation of a validation at the final level
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.APUR_CANCEL_VALIDF')[1];
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

                    $activity->description = Config::get('gesbudget.variables.actions.APUR_CLOSE')[1];
                } else if ($oldEtat === 'CLOT') {
                    /** The engagement has been closed
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.APUR_RESTORE')[1];
                } else {
                    $activity->description = 'UNKNOWN_ETAT_CHANGE + old:'. $oldEtat . ' new:' . $newEtat;
                }
            } else if (isset($activity->properties['attributes']['next_statut'])) {
                // The 'etat' attribute has changed
                $newNS = $activity->properties['attributes']['next_statut'];
                $oldNS = $activity->properties['old']['next_statut'];

                if(is_null($newNS)) {
                    $activity->description = Config::get('gesbudget.variables.actions.APUR_RESEND')[1] . "_FROM_" .$oldNS;
                } else if (is_null($oldNS)) {
                    $activity->description = Config::get('gesbudget.variables.actions.APUR_SEND_BACK')[1] . "_FOR_" .$newNS;
                } else {
                    $activity->description = 'UNKNOWN_SEND_BACK';
                }
            } else {
                $activity->description = Config::get('gesbudget.variables.actions.APUR_UPDATE')[1];
            } 
        }
    }
}
