<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    public function client(){
        return $this->belongsToMany('\App\Client');
    }
}
