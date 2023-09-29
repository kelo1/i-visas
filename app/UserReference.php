<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserReference extends Model
{
    protected $fillable = [
        'user_id','user_reference_no'
    ];

    public function user(){
        return $this->hasOne('\App\User');
    }
}
