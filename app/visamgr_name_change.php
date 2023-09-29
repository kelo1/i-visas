<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class visamgr_name_change extends Model
{
    protected $fillable = [
        'NAME_CHANGE_ANSWER',
        'NAME_CHANGE_FROM_DATE',
        'NAME_CHANGE_TO_DATE'
    ];
}
