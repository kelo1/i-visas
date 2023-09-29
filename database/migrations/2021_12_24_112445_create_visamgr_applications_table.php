<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisamgrApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visamgr_applications', function (Blueprint $table) {
            //$table->integer('APPLICATION_ID')->primary()->index();
            $table->id('APPLICATION_ID');
            $table->integer('CLIENT_ID');
            $table->integer('APPTYPE_ID')->unique();
            $table->string('EXTSTATUS')->nullable();
            $table->string('TITLE')->nullable();
            $table->string('FIRSTNAME')->nullable();
            $table->string('MIDDLENAME')->nullable();
            $table->string('LASTNAME')->nullable();
            $table->date('DOB')->nullable();
            $table->string('GENDER')->nullable();
            $table->string('NATIONALITY')->nullable();
            $table->string('PASSPORT_NO')->nullable();
            $table->date('PASSPORT_ISSUED')->nullable();
            $table->date('PASSPORT_EXPIRY')->nullable();
            $table->string('ISSUING_AUTHORITY')->nullable();
            $table->string('PLACE_OF_ISSUE')->nullable();
            $table->integer('CURRENT_VISA_TYPE_ID')->default(0);
            $table->date('CURRENT_VISA_ISSUED')->nullable();
            $table->date('CURRENT_VISA_EXPIRY')->nullable();
            $table->string('CURRENT_IN_UK')->nullable();
            $table->string('COMPANY_NAME')->nullable();
            $table->string('COMPANY_POSITION')->nullable();
            $table->string('ADDRESS1')->nullable();
            $table->string('ADDRESS2')->nullable();
            $table->integer('TIME_LIVED_AT_ADDRESS')->default(0);
            $table->date('DATE_MOVED_IN_ADDRESS')->nullable();
            $table->string('TOWN')->nullable();
            $table->string('COUNTY')->nullable();
            $table->string('POSTCODE')->nullable();
            $table->string('COUNTRY')->nullable();
            $table->string('TELEPHONE')->nullable();
            $table->string('MOBILE')->nullable();
            $table->string('EMAIL')->nullable();
            $table->string('NOTES')->nullable();
            $table->string('ADD_USER')->nullable();
            $table->date('ADD_DATE')->nullable();
            $table->string('MOD_USER')->nullable();
            $table->date('MOD_DATE')->nullable();
            //Client office and default user
            $table->integer('CLIENT_OFFICE')->default(1);

            //-------------------------------------//
            //$table->integer('TYPE_OF_APPLICATION');
            $table->string('NAME_CHANGE_QUESTION')->nullable();
            // $table->string('NAME_CHANGE_ANSWER')->nullable();
            // $table->date('NAME_CHANGE_FROM_DATE')->nullable();
            // $table->date('NAME_CHANGE_TO_DATE')->nullable();
            //Personal InfO
            //Name change Referenced in visamgr_name_change table
            $table->string('COUNTRY_OF_BIRTH')->nullable();
            $table->string('PLACE_OF_BIRTH')->nullable();
            $table->string('OTHER_NATIONALITY_QUESTION')->nullable();
            //Name change Referenced in visamgr_other_nationality table
            $table->string('BRP_QUESTION')->nullable();
            $table->string('BRP_NUMBER')->nullable();
            $table->date('BRP_ISSUE_DATE')->nullable();
            $table->date('BRP_EXPIRY_DATE')->nullable();
            $table->string('NATIONAL_ID_QUESTION')->nullable();
            $table->string('NATIONAL_ID_NO')->nullable();
            $table->string('NAME_MOTHER')->nullable();
            $table->date('DOB_MOTHER')->nullable();
            $table->string('NATIONALITY_MOTHER')->nullable();
            $table->string('PLACE_OF_BIRTH_MOTHER')->nullable();
            $table->string('NAME_FATHER')->nullable();
            $table->date('DOB_FATHER')->nullable();
            $table->string('NATIONALITY_FATHER')->nullable();
            $table->string('PLACE_OF_BIRTH_FATHER')->nullable();
            $table->string('UK_NI_QUESTION')->nullable();
            $table->string('UK_NI')->nullable();
            $table->string('UK_DRIVER_LICENSE_QUESTION')->nullable();
            $table->string('UK_DRIVER_LICENSE')->nullable();
            //Accomodation
            $table->string('LOCATION_NAME')->nullable();
            $table->string('LOCATION_CODE')->nullable();
            $table->string('FAX')->nullable();
            $table->string('VATRATE')->nullable();
            $table->string('COUNTRY_PREFIX')->nullable();
            $table->string('NUMBER_OF_OTHERROOMS')->nullable();


            $table->string('HOME_QUESTION_ANSWER')->nullable();
            $table->string('LANDLORD_NAME')->nullable();
            $table->string('LANDLORD_EMAIL')->nullable();
            $table->string('LANDLORD_MOBILE')->nullable();
            $table->string('LANDLORD_ADDRESS1')->nullable();
            $table->string('LANDLORD_ADDRESS2')->nullable();
            $table->string('LANDLORD_LOCATION_NAME')->nullable();
            $table->string('LANDLORD_LOCATION_CODE')->nullable();
            $table->string('LANDLORD_TOWN')->nullable();
            $table->string('LANDLORD_COUNTY')->nullable();
            $table->string('LANDLORD_POSTCODE')->nullable();
            $table->string('LANDLORD_COUNTRY_PREFIX')->nullable();
            $table->string('LANDLORD_COUNTRY')->nullable();
            $table->string('LANDLORD_FAX')->nullable();
            $table->string('LANDLORD_VRATE')->nullable();
            $table->integer('NUMBER_OF_BEDROOMS')->nullable();
            $table->string('WHO_LIVES_THERE')->nullable();
            $table->string('PREVIOUS_ADDRESS1')->nullable();
            $table->string('PREVIOUS_ADDRESS2')->nullable();
            $table->string('PREVIOUS_LOCATION_NAME')->nullable();
            $table->string('PREVIOUS_LOCATION_CODE')->nullable();
            $table->string('PREVIOUS_COUNTRY_PREFIX')->nullable();
            $table->string('PREVIOUS_FAX')->nullable();
            $table->string('PREVIOUS_VRATE')->nullable();
            $table->string('TOWN_PREVIOUS')->nullable();
            $table->string('COUNTY_PREVIOUS')->nullable();
            $table->string('POSTCODE_PREVIOUS')->nullable();
            $table->string('COUNTRY_PREVIOUS')->nullable();
            //Family Settlement Visa
            $table->string('MARITAL_STATUS')->nullable();
            $table->date('DATE_OF_MARRIAGE')->nullable();
            $table->string('WHERE_YOU_GOT_MARRIED')->nullable();
            $table->string('NAME_OF_SPOUSE')->nullable();
            $table->date('DOB_SPOUSE')->nullable();
            $table->string('NATIONALITY_SPOUSE')->nullable();
            $table->string('PASSPORT_SPOUSE')->nullable();
            $table->string('WHERE_YOU_MET')->nullable();
            $table->string('WHERE_RELATIONSHIP_BEGAN')->nullable();
            $table->date('WHEN_LAST_YOU_SAW_EACHOTHER')->nullable();
            $table->string('LIVE_TOGETHER_QUESTION')->nullable();
            $table->date('DATE_LIVING_TOGETHER')->nullable();
            $table->string('DO_YOU_HAVE_CHILDREN')->nullable();//NUMBER_OF_DEPENDENT_CHILDREN
            $table->integer('NUMBER_OF_DEPENDENT_CHILDREN')->nullable();
            //Other attributes of children are referenced in visamgr_dependants table
            $table->string('MARRIED_BEFORE_QUESTION')->nullable();
            $table->string('PARTNER_MARRIED_BEFORE')->nullable();
            $table->string('DO_YOU_HAVE_FAMILY_IN_HOME_COUNTRY')->nullable();
            //English Language
            $table->string('HAVE_DEGREE_IN_ENGLISH')->nullable();
            $table->string('QUALIFICATION_QUESTION')->nullable(); //HAVE_DEGREE_IN_ENGLISH
            $table->string('OTHER_CERTIFICATE')->nullable();
            $table->string('PASSED_RECOGNIZED_TEST')->nullable();
            $table->string('WHAT_TEST_DID_YOU_PASS')->nullable();
            //Other qualifications are referenced in visamgr_qualifications table

            //Employment
            //Name change Referenced in visamgr_employment table
            $table->string('EMPLOYMENT_QUESTION')->nullable();
            //Travel
            //Other attributes are referenced in create_temp_visamgr_locations table  ARE_YOU_IN_UK:
            $table->string('ARE_YOU_IN_UK')->nullable();
            $table->date('WHEN_DID_YOU_ENTER_UK')->nullable();
            $table->string('DID_YOU_ENTER_LEGALLY')->nullable();
            $table->text('VISA_REASON')->nullable();
            $table->date('VISA_START_DATE')->nullable();//VISA_END_DATE
            $table->date('VISA_END_DATE')->nullable();
            $table->string('VISA_STATUS')->nullable();
            $table->string('OUT_OF_THE_UK_BEFORE')->nullable();
            $table->longText('ANY_OTHER_COUNTRY_VISITED')->nullable();
            $table->string('ENTERED_UK_MEANS')->nullable();
            $table->text('REASON_FOR_ILEGAL_ENTRY')->nullable();
            $table->string('EVER_STAYED_BEYOND_EXPIRY')->nullable();
            $table->text('REASON_FOR_STAYING_BEYOND_EXPIRY')->nullable();
            $table->string('BREACHED_CONDITION_FOR_LEAVE')->nullable();
            $table->string('BREACH_COUNTRY')->nullable();
            $table->text('REASON_FOR_BREACH')->nullable();
            $table->string('WORK_WITHOUT_PERMIT')->nullable();
            $table->text('REASON_FOR_WORK_WITHOUT_PERMIT')->nullable();
            $table->string('RECEIVED_PUBLIC_FUNDS')->nullable();
            $table->text('REASON_RECEIVING_FUNDS')->nullable();
            $table->string('GIVE_FALSE_INFO')->nullable();
            $table->text('REASON_FOR_FALSE_INFO')->nullable();
            $table->string('USED_DECEPTION')->nullable();
            $table->text('REASON_FOR_DECEPTION')->nullable();
            $table->string('BREACHED_OTHER_LAWS')->nullable();
            $table->text('REASON_FOR_BREACHING__LAWS')->nullable();
            $table->string('VISA_REFUSAL_QUESTION')->nullable();
            $table->text('REASON_FOR_REFUSAL')->nullable();
            $table->string('PERMISSION_REFUSAL')->nullable();
            $table->text('REASON_FOR_PERMISSION_REFUSAL')->nullable();
            $table->string('ASYLUM_REFUSAL')->nullable();
            $table->text('REASON_FOR_ASYLUM_REFUSAL')->nullable();
            $table->string('EVER_DEPORTED')->nullable();
            $table->text('REASON_FOR_DEPORTATION')->nullable();
            $table->string('EVER_BANNED')->nullable();
            $table->text('REASON_FOR_BAN')->nullable();
            //Character
            $table->string('CRIMINAL_OFFENSE')->nullable();

            //attributes are referenced in visamgr_character table

            //Membership
           // $table->string('MEMBERSHIP')->nullable();
            //attributes are referenced in visamgr_memberships table

              //Maintenance
              //$table->string('MAINTENANCE')->nullable();
              //attributes are referenced in visamgr_memberships table

            //Document Upload
            $table->string('PASSPORT_UPLOAD')->nullable();
            $table->string('DEPENDENT_PASSPORT_UPLOAD')->nullable();
            $table->string('UTILITY_BILL_UPLOAD')->nullable();
            $table->string('BRP_UPLOAD')->nullable();
            $table->string('PREVIOUS_VISA_UPLOAD')->nullable();
            $table->string('REFUSAL_LETTER_UPLOAD')->nullable();
            $table->string('EDUCATIONAL_CERT_UPLOAD')->nullable();
            $table->string('ENGLISH_CERT_UPLOAD')->nullable();
            $table->string('MARRIAGE_CERT_UPLOAD')->nullable();
            $table->string('BANK_STATEMENT_UPLOAD')->nullable();
            $table->string('MOTIVATIONAL_LETTER_UPLOAD')->nullable();
            $table->string('RESUME_UPLOAD')->nullable();
            $table->string('ACADEMIC_TRANSCRIPTS_UPLOAD')->nullable();
            $table->string('CAS_LETTERS_UPLOAD')->nullable();
            $table->string('RECOMMENDATION_LETTERS_UPLOAD')->nullable();
            $table->string('RESEARCH_PROPOSAL_UPLOAD')->nullable();
            $table->string('OTHER_UPLOAD')->nullable();
            $table->integer('APPSTATUS')->default(0);
            $table->string('created_by')->nullable();
            $table->string('USER')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visamgr_applications');
    }
}
