<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Projet extends Model
{
    //
    public function chapitre(){
        return $this->belongsTo('App\Models\Chapitre');
    }
}
