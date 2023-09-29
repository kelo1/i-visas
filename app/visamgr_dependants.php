<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class visamgr_dependants extends Model
{
    protected $fillable = [
        'FULL_NAME',
        'GENDER',
        'DOB',
        'RELATIONSHIP',
        'NATIONALITY',
        'PASSPORT_NO',
        'PASSPORT_ISSUED',
        'PASSPORT_EXPIRY',
        'VISA_TYPE_ID',
        'VISA_ISSUED',
        'VISA_EXPIRY',
    ];


    public function application(){
        return $this->hasMany('\App\Application');
    }

}
