<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class PreScreening extends Model
{
    use Notifiable;

    protected $fillable = [

        'how_we_can_help_you_question',
        'how_we_can_help_you',
        'country_of_residence',
         'residency_question',
         'type_of_permission',
         'expiry',
           'dob',
           'english_proficiency_level',
            'english_proficiency',
            'other_languages',
            'visa_refusal',
            'visa_refusal_reason',
            'in_your_own_words',
            'created_by'

     ];


    public function client(){
        return $this->belongsTo('\App\Client');
    }


}
