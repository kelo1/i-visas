<?php

use Illuminate\Database\Seeder;
use App\visamgr_branches;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        visamgr_branches::truncate();

        visamgr_branches::create([
           // 'id'=>1,
            'LOCATION_NAME'=>'UK Immigration Solutions Ltd',
            'LOCATION_CODE'=>'London',
            'ADDRESS1'=>'Kensington Pavilion',
            'ADDRESS2'=>'96 Kensington High Street',
            'TOWN'=>'London',
            'COUNTY'=>NULL,
            'COUNTRY'=>'England',
            'VAT_RATE'=>20,
            'POSTCODE'=>'W8 4SG',
            'TELEPHONE'=>'+442076030092',
            'FAX'=>'+448082800531',
            'EMAIL'=>'admin@i-visas.com',
            'COUNTRY_PREFIX'=>'44',
            'STATUS'=>'ACTIVE',
            // 'ADD_USER'=>'Ajaz',
            // 'ADD_DATE'=>NULL,
            // 'MOD_USER'=>NULL,
            // 'MOD_DATE'=>NULL,

        ]);

        visamgr_branches::create([
            // 'id'=>1,
             'LOCATION_NAME'=>'UK Immigration Solutions Ltd (SH)',
             'LOCATION_CODE'=>'Sheffield',
             'ADDRESS1'=>'Sorby House',
             'ADDRESS2'=>'Spital Hill',
             'TOWN'=>'Sheffield',
             'COUNTY'=>NULL,
             'COUNTRY'=>'England',
             'VAT_RATE'=>20,
             'POSTCODE'=>'S4 7LG',
             'TELEPHONE'=>'+442076030092',
             'FAX'=>'+448082800531',
             'EMAIL'=>'admin@i-visas.com',
             'COUNTRY_PREFIX'=>'44',
             'STATUS'=>'ACTIVE',
             // 'ADD_USER'=>'Ajaz',
             // 'ADD_DATE'=>NULL,
             // 'MOD_USER'=>NULL,
             // 'MOD_DATE'=>NULL,

         ]);


         visamgr_branches::create([
            // 'id'=>1,
             'LOCATION_NAME'=>'UK Immigration Solutions Ltd (Islamabad)',
             'LOCATION_CODE'=>'Islamabad',
             'ADDRESS1'=>'Emirates Tower, 2nd & 3rd Floor',
             'ADDRESS2'=>'M-13, F-7',
             'TOWN'=>'Markaz',
            // 'COUNTY'=>'Islamabad',
             'COUNTRY'=>'Pakistan',
             'VAT_RATE'=>5,
             'POSTCODE'=>NULL,
             'TELEPHONE'=>'+442076030092',
             'FAX'=>'+448082800531',
             'EMAIL'=>'Islamabad@i-visas.com',
             'COUNTRY_PREFIX'=>'92',
             'STATUS'=>'ACTIVE',
             // 'ADD_USER'=>'Ajaz',
             // 'ADD_DATE'=>NULL,
             // 'MOD_USER'=>NULL,
             // 'MOD_DATE'=>NULL,

         ]);


         visamgr_branches::create([
            // 'id'=>1,
             'LOCATION_NAME'=>'UK Immigration Solutions Ltd (Rawalpindi)',
             'LOCATION_CODE'=>'Rawalpindi',
             'ADDRESS1'=>'1st Floor',
             'ADDRESS2'=>'62 Canning Road',
             'TOWN'=>'Sadder',
             'COUNTY'=>'Rawalpindi',
             'COUNTRY'=>'Pakistan',
             'VAT_RATE'=>5,
             'POSTCODE'=>46000,
             'TELEPHONE'=>'+442076030092',
             'FAX'=>'+448082800531',
             'EMAIL'=>'Rawalpindi@i-visas.com',
             'COUNTRY_PREFIX'=>'92',
             'STATUS'=>'ACTIVE',
             // 'ADD_USER'=>'Ajaz',
             // 'ADD_DATE'=>NULL,
             // 'MOD_USER'=>NULL,
             // 'MOD_DATE'=>NULL,

         ]);


         visamgr_branches::create([
            // 'id'=>1,
             'LOCATION_NAME'=>'UK Immigration Solutions Ltd (Kiev)',
             'LOCATION_CODE'=>'Kiev',
             'ADDRESS1'=>'Physckultury Street',
             'ADDRESS2'=>'30-B',
             'TOWN'=>'Kiev',
             'COUNTY'=>NULL,
             'COUNTRY'=>'Ukraine',
             'VAT_RATE'=>0,
             'POSTCODE'=>3150,
             'TELEPHONE'=>'+442076030092',
             'FAX'=>'+448082800531',
             'EMAIL'=>'Karina@i-visas.com',
             'COUNTRY_PREFIX'=>'380',
             'STATUS'=>'ACTIVE',
             // 'ADD_USER'=>'Ajaz',
             // 'ADD_DATE'=>NULL,
             // 'MOD_USER'=>NULL,
             // 'MOD_DATE'=>NULL,

         ]);



         visamgr_branches::create([
            // 'id'=>1,
             'LOCATION_NAME'=>'UK Immigration Solutions Ltd (PS)',
             'LOCATION_CODE'=>'Sindh',
             'ADDRESS1'=>'M/S Travel Lead (Ground Floor)',
             'ADDRESS2'=>'Ali Complex, 23 Empress Road',
             'TOWN'=>'Lahore',
             'COUNTY'=>NULL,
             'COUNTRY'=>'Pakistan',
             'VAT_RATE'=>5,
             'POSTCODE'=>NULL,
             'TELEPHONE'=>'+442076030092',
             'FAX'=>'+448082800531',
             'EMAIL'=>'sindh1@i-visas.com',
             'COUNTRY_PREFIX'=>'92',
             'STATUS'=>'ACTIVE',
             // 'ADD_USER'=>'Ajaz',
             // 'ADD_DATE'=>NULL,
             // 'MOD_USER'=>NULL,
             // 'MOD_DATE'=>NULL,

         ]);

    }
}
