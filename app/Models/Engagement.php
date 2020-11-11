<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Engagement extends Model
{
    public function ligne(){
        return $this->belongsTo('App\Models\Ligne');
    }

    public function imputations(){
        return $this->hasMany('App\Models\Imputation');
    }

    public function apurements(){
        return $this->hasMany('App\Models\Apurement', 'engagement_id', 'code');
    }
}
