<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Imputation extends Model
{
    public function engagement(){
        return $this->belongsTo('App\Models\Engagement');
    }
}
