<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Projet extends Model
{
    //
    public function ligne(){
        return $this->belongsTo('App\Models\Ligne');
    }
}
