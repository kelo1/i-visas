<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Messages extends Model
{
      /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $primaryKey = 'MESSAGE_ID';

    protected $fillable = [
        'MESSAGE',
        'MESSAGE_SUBJECT',
        'MESSAGE_TAG',

    ];

    public function client(){
        return $this->hasMany('\App\Client');
    }
}
