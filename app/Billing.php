<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    protected $primaryKey = 'id';

    protected $fillable = [
        'DESCRIPTION',
        'VAT_APPLICABLE',
        'isACTIVE',

    ];
}
