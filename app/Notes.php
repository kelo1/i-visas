<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notes extends Model
{
    public function application(){
        return $this->belongsToMany('\App\visamgr_applications');
    }
}
