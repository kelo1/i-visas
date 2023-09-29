<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BranchReference extends Model
{
    protected $fillable = [
        'branch_id','branch_reference_no'
    ];
}
