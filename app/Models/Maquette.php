<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maquette extends Model
{
    protected $attributes = [
        'status' => "created",
        'loadprogress' => 0

    ];
}
