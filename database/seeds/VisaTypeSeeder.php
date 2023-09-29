<?php

use Illuminate\Database\Seeder;
use App\visamgr_apptypes;
use Carbon\Carbon;

class VisaTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        visamgr_apptypes::truncate();

        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'AN1',
            'APPSUBCAT_NAME'=>'NATURALISATION',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);

        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'B(OS) B(OTA)',
            'APPSUBCAT_NAME'=>'REGISTRATION',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);

        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'DRP',
            'APPSUBCAT_NAME'=>'DERIVETIVE RESIDENCE PERMIT',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);

        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'ECHR',
            'APPSUBCAT_NAME'=>'EUROPEAN COURT OF HUMAN RIGHTS APPLICATION',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);

        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'EEA1',
            'APPSUBCAT_NAME'=>'APPLICATION FOR REGISTRATION CERTIFICATE',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);

        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'EEA2',
            'APPSUBCAT_NAME'=>'APPLICATION FOR RESIDENCE CARD',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);

        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'EEA3',
            'APPSUBCAT_NAME'=>'APPLICATION FOR A DOCUMENT CERTIFYING PERMANENT RESIDENCE',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);

        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'EEA4',
            'APPSUBCAT_NAME'=>'APPLICATION FOR PERMANENT RESIDENCE CARD',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);

        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'FLR(0)',
            'APPSUBCAT_NAME'=>'OUTSIDE OF THE IMMIGRATION RULES - EXTENSION OF VISIT VISA/DOMESTIC WORKER',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);

        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'FLR(M)',
            'APPSUBCAT_NAME'=>'SPOUSE OF A PERSON PRESENT AND SETTLED',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);

        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'FLR(PR)',
            'APPSUBCAT_NAME'=>'PRIVATE LIFE',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'FTT APPEAL - IN COUNTRY APPEAL',
            'APPSUBCAT_NAME'=>'FIRST TIER TRIBUNAL APPEAL',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'FTT APPEAL - OUT OF COUNTRY',
            'APPSUBCAT_NAME'=>NULL,
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);



        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'FTT APPEAL - UPPER TRIBUNAL RECON',
            'APPSUBCAT_NAME'=>NULL,
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);



        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'MN1',
            'APPSUBCAT_NAME'=>'CHILDREN NATURALISATION',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'MN1',
            'APPSUBCAT_NAME'=>'REGISTRATION OF CHILDREN',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'NTL',
            'APPSUBCAT_NAME'=>'NO TIME LIMIT',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'PBS DEPENDANT',
            'APPSUBCAT_NAME'=>'PBS DEPENDNT FOR T1/2/4',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'REFUSAL',
            'APPSUBCAT_NAME'=>'Refusal',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'ROA',
            'APPSUBCAT_NAME'=>'APPLICATION FOR CERTIFICATE OF ENTITLEMENT TO RIGHT OF ABODE',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'SET (F)',
            'APPSUBCAT_NAME'=>'SETTLEMENT AS  FAMILY MEMBER',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'SET (LR)',
            'APPSUBCAT_NAME'=>'SETTLEMENT LONG RESIDENCY',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'SET (M)',
            'APPSUBCAT_NAME'=>'SETTLEMENT AS A SPOUSE',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);

        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'SET (O)',
            'APPSUBCAT_NAME'=>'SET O EMPLOYMENT AND OTHER PURPOSES',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'SMS',
            'APPSUBCAT_NAME'=>'SPONSOR LICENCE APPLICATION',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'TIER',
            'APPSUBCAT_NAME'=>'GENERAL',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'TIER1',
            'APPSUBCAT_NAME'=>'ENTREPRENEUR',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'TIER1',
            'APPSUBCAT_NAME'=>'GENERAL',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'TIER1',
            'APPSUBCAT_NAME'=>'GRADUATE ENTREPRENUER',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'TIER1',
            'APPSUBCAT_NAME'=>'INVESTOR',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'TIER2',
            'APPSUBCAT_NAME'=>'GENERAL',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'TIER2',
            'APPSUBCAT_NAME'=>'INTRA COMPANY TRANSFER',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'TIER2',
            'APPSUBCAT_NAME'=>'MINISTER OF RELIGION',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'TIER2',
            'APPSUBCAT_NAME'=>'SPORTSPERSON',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);

        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'TIER4',
            'APPSUBCAT_NAME'=>'STUDENT VISA',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'TIER5',
            'APPSUBCAT_NAME'=>'TEMPORATY WORKER - CHARITY WORKER',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'TOC',
            'APPSUBCAT_NAME'=>'TRANSFER OF CONDITIONS',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'VAF 10',
            'APPSUBCAT_NAME'=>'PBS APPLICATION',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'VAF 1A',
            'APPSUBCAT_NAME'=>'VISIT VISA',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);


        visamgr_apptypes::create([
            'APPTYPE_NAME'=>'VAF 4A',
            'APPSUBCAT_NAME'=>'SETTLEMENT',
            'STATUS'=>1,
            'USER'=>'SYS_ADMIN',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()

        ]);
    }
}
