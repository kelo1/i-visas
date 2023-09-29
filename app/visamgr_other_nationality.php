<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class visamgr_other_nationality extends Model
{

    protected $fillable = [
        'OTHER_NATIONALITY',
        'OTHER_NATIONALITY_FROM_DATE',
        'OTHER_NATIONALITY_TO_DATE'
    ];

    public function application(){
        return $this->hasMany('\App\Application');
    }
}
