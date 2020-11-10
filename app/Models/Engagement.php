<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Engagement extends Model
{
    use LogsActivity;

    // Log  all attribute that has changed
    protected static $logAttributes = ['*'];
    
    // Log only attributes that has actually changed after the update
    protected static $logOnlyDirty = true;

    // Prevents from storing empty logs
    protected static $submitEmptyLogs = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'libelle', 'montant_ht', 'montant_ttc', 'devise',  'nature','type', 'etat', 'statut',
        'nb_imputations','cumul_imputations','nb_apurements','cumul_apurements','saisisseur','valideur_first','valideur_second','valideur_final','source'
    ];

    public function tapActivity(Activity $activity, string $eventName)
    {
        // $activity->description = json_encode($activity);
        $actioncode = '';
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

                    $activity->description = Config::get('gesbudget.variables.actions.VALIDP')[0];
                } else if ($oldStatut === 'VALIDP' && $newStatut === 'VALIDS') {
                    /** This is a validation at the second level 
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.VALIDS')[0];
                } else if ($oldStatut === 'VALIDS' && $newStatut === 'VALIDF') {
                    /** This is a validation at the final level 
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.VALIDF')[0];
                } else if ($oldStatut === 'VALIDP' && $newStatut === 'SAISI') {
                    /** This is a Cancelation of a Validation at the first level 
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.CANCEL_VALIDP')[0];
                } else if ($oldStatut === 'VALIDS' && $newStatut === 'VALIDP') {
                    /** This is a Cancelation of a validation at the second level 
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.CANCEL_VALIDS')[0];
                } else if ($oldStatut === 'VALIDF' && $newStatut === 'VALIDS') {
                    /** This is a Cancelation of a validation at the final level 
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.CANCEL_VALIDF')[0];
                } else {
                    $activity->description = 'UNKNOWN_STATUT_CHANGE';
                }
            } else if (isset($activity->properties['attributes']['etat'])) {
                // The 'etat' attribute has changed
                $newEtat = $activity->properties['attributes']['etat'];
                $oldEtat = $activity->properties['old']['etat'];

                if ($oldEtat === 'INIT' && $newEtat === 'PEG') {
                    /** This is a Validation at the first level
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.PREENGAGER')[0];
                } else if ($oldEtat === 'PEG' && $newEtat === 'IMP') {
                    /** This is a validation at the second level
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.IMPUTER')[0];
                } else if ($oldEtat === 'IMP' && $newEtat === 'REA') {
                    /** This is a validation at the second level
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.APURER')[0];
                } else if ($newEtat === 'CLOT') {
                    /** The engagement has been closed
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.CLOSE')[0];
                } else if ($oldEtat === 'CLOT') {
                    /** The engagement has been closed
                     * 
                    */

                    $activity->description = Config::get('gesbudget.variables.actions.RESTORE')[0];
                } else {
                    $activity->description = 'UNKNOWN_ETAT_CHANGE';
                }
            } else if (isset($activity->properties['attributes']['next_statut'])) {
                // The 'etat' attribute has changed
                $newNS = $activity->properties['attributes']['next_statut'];
                $oldNS = $activity->properties['old']['next_statut'];

                if(is_null($newNS)) {
                    $activity->description = Config::get('gesbudget.variables.actions.RESEND')[0] . "_FROM_" .$oldNS;
                } else if (is_null($oldNS)) {
                    $activity->description = Config::get('gesbudget.variables.actions.SEND_BACK')[0] . "_FOR_" .$newNS;
                } else {
                    $activity->description = 'UNKNOWN_SEND_BACK';
                }
            } else {
                $activity->description = Config::get('gesbudget.variables.actions.UPDATE')[0];
            }
        }
    }
}
