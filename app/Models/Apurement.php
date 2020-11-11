<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apurement extends Model
{
    public function engagement(){
        return $this->belongsTo('App\Models\Engagement', 'engagement_id', 'code');
    }
}
