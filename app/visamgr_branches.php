<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class visamgr_branches extends Model
{
    protected $fillable = [
            'COUNTRY',
            'LOCATION_NAME',
            'LOCATION_CODE',
            'ADDRESS1',
            'ADDRESS2',
            'TOWN',
            'COUNTY',
            'POSTCODE',
            'TELEPHONE',
            'DEFAULT_USER',
            'FAX',
            'EMAIL',
            'VAT_RATE',
            'STATUS'
    ];

    public function user(){
        return $this->belongsToMany('\App\visamgr_branches');
    }
}
