<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ForgotUserPasswordController;
use App\Http\Controllers\PreScreeningController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MessageUserController;
use App\Http\Controllers\ValidatePhoneNumber;
use App\Http\Controllers\ValidateEmailController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\Tracking_TypeController;
use App\Http\Controllers\generatePDFController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\GroupsController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\EmploymentController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\NameChangeController;
use App\Http\Controllers\NameofPeopleatAddressController;
use App\Http\Controllers\NumberofChildrenController;
use App\Http\Controllers\OtherNationalityController;
use App\Http\Controllers\PeopleAtHomeController;
use App\Http\Controllers\QualificationController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\TravelFiveController;
use App\Http\Controllers\PreviousMarriageController;
use App\Http\Controllers\PartnerMarriedBeforeController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\InvoicingController;
use App\Http\Controllers\VisaTypeController;
use App\Http\Controllers\UserReferenceController;
use App\Http\Controllers\BranchReferenceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//Client Routes
Route::post('/client/ivisas/register', [ClientController::class, 'store'])->name('register_client');

Route::post('client/login', [ClientController::class, 'login'])->name('login');

Route::post('client/otp/validate', [ValidatePhoneNumber::class, 'validateOTP'])->name('validate_otp');
Route::post('client/resend/otp', [ValidatePhoneNumber::class, 'resendOTP'])->name('resend_otp');
Route::post('client/email/validate', [ValidateEmailController::class, 'validateEmail'])->name('validate_email');
Route::post('client/ivisas/email/resend', [ValidateEmailController::class, 'resendemail'])->name('resend_email');

//Password Reset - Client
Route::post('client/password/forgotpassword', [ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('client_forgot_password');
Route::post('client/password/resetpassword', [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('client_reset_password');

  //Password Reset - User
Route::post('user/password/forget_password', [ForgotUserPasswordController::class, 'submitForgetPasswordForm'])->name('user_forgot_password');
Route::post('user/password/reset_password', [ForgotUserPasswordController::class, 'submitResetPasswordForm'])->name('user_reset_password');


//Routes to be protected via Sanctum


//---------------------------------------------------------------------------------------------------------//

//User Routes


Route::post('user/login', [UserController::class, 'login'])->name('login');

Route::get('/company/info', [SettingsController::class, 'index'])->name('company_info');

//---------------------------------------------------------------------------------------------------------//

//Protected routes which require authentication
Route::group(['middleware'=>['auth:sanctum']],function(){

    //User Routes
    Route::get('/user/all/users', [UserController::class, 'index'])->name('user');
    Route::get('/user/search/{name}', [UserController::class, 'search'])->name('search_user');
    Route::get('user/show/{id}', [UserController::class, 'show'])->name('show_user');

    //Routes to be protected via Sanctum
    Route::post('user/create_user', [UserController::class, 'createUser'])->name('create_user');
    Route::post('user/create/client', [UserController::class, 'createClient'])->name('create_client');
    Route::post('user/client_setup/prescreening/saveprescreendraft', [PreScreeningController::class, 'savePrescreenDraft'])->name('prescreen_save_draft'); //put
    Route::post('user/client_setup/prescreening/submitprescreen', [UserController::class, 'submitPrescreen'])->name('submit_prescreen'); //put
    Route::post('user/client_setup/application/submitapplication', [UserController::class, 'submitApplication'])->name('submit_application');


    Route::delete('/user', [UserController::class, 'destroy'])->name('delete_user');

    Route::post('user/logout', [UserController::class, 'logout'])->name('logout');
    Route::get('/user/allusers', [UserController::class, 'getAllUsers'])->name('all_users');
    Route::post('/user/update', [UserController::class, 'update'])->name('update_user'); //put

    //Roles
    Route::post('/role/create', [RoleController::class, 'store'])->name('create_role');
    Route::get('/role', [RoleController::class, 'index'])->name('role');
    Route::get('/role/show/{id}', [RoleController::class, 'show'])->name('show_role');
    Route::post('/role/update', [RoleController::class, 'update'])->name('update_role');  //put
    Route::post('/role/delete', [RoleController::class, 'destroy'])->name('delete_role');  //put



    //Client Routes
    Route::get('/client/all/{id}', [ClientController::class, 'getAllClients'])->name('client');
    Route::delete('/client/{id}', [ClientController::class, 'destroy'])->name('delete_client');
//    Route::get('/client/searchbytype/{search}', [ClientController::class, 'search'])->name('search_client');
    Route::get('/client/searchbytype/{search}/{id}', [ClientController::class, 'search'])->name('search_client');
    Route::get('/client/{id}', [ClientController::class, 'show'])->name('show_client');
    Route::post('/client/searchby/location', [ClientController::class, 'clientbyLocation'])->name('search_by_client_location');
    Route::get('/client/get/top30clients/{id}', [ClientController::class, 'top30Clients'])->name('top_30_clients');
    Route::get('/client/verified/clients/{id}', [ClientController::class, 'verifiedClients'])->name('verified_clients');
    Route::post('/client/clientdetails/perapplicationand/prescreening', [ClientController::class, 'clientDetailsPerApplicationandPrescreening'])->name('client_details_per_application_and_prescreening');
    Route::post('client/clientsession/logout', [ClientController::class, 'logout'])->name('logout');
    Route::post('/client/office/update', [ClientController::class, 'updateCLientOffice'])->name('update_client_office');
    Route::post('/client/group/update', [ClientController::class, 'updateGroup'])->name('update_client_group');
    Route::post('/client/agent/update', [ClientController::class, 'updateUser'])->name('update_client_agent');
    Route::post('/client/update/client_update', [ClientController::class, 'update'])->name('update_client'); //
    Route::post('/application/checkappcreationstatus', [ApplicationController::class, 'CheckApplicationCreationStatus'])->name('check_application_creation_status');
   // Route::post('/client/getallclients', [ApplicationController::class, 'getAllClients'])->name('check_application_creation_status');

    // Client Pre-Screening Routes
    //Route::get('prescreening/{id}', [PreScreeningController::class, 'index'])->name('prescreen');
    Route::get('prescreening/all/{userid}', [PreScreeningController::class, 'index'])->name('prescreen');
    Route::post('prescreening/saveprescreendraft', [PreScreeningController::class, 'savePrescreenDraft'])->name('prescreen_save_draft'); //put
    Route::post('prescreening/submitprescreen', [PreScreeningController::class, 'submitPrescreen'])->name('submit_prescreen'); //put
   // Route::put('/prescreening/{id}', [PreScreeningController::class, 'update'])->name('update_prescreen_client');
    Route::post('/prescreening/approvescreening', [PreScreeningController::class, 'approveScreening'])->name('approve_prescreening'); //put
    Route::post('/prescreening/declinescreening', [PreScreeningController::class, 'declineScreening'])->name('decline_preScreening'); //put
    Route::post('/prescreening/prescreeningbyclientlocation', [PreScreeningController::class, 'preScreeningByClientLocation'])->name('search_prescreen_by_client_location');
    Route::post('/prescreening/top30prescreenings/{id}', [PreScreeningController::class, 'top30Prescreening'])->name('top_30_prescreenings');
    Route::get('/prescreening/searchbytype/{search}/{id}', [PreScreeningController::class, 'search'])->name('search_prescreen_client');
    Route::post('/prescreening/updatebyid/{id}', [PreScreeningController::class, 'update'])->name('update_prescreen_client'); //put
    Route::get('/prescreening/{id}', [PreScreeningController::class, 'searchByClientID'])->name('search_prescreen_byclientID');

    // Application Routes

    Route::get('application/{id}', [ApplicationController::class, 'index'])->name('application');
    Route::post('application/createapplication', [ApplicationController::class, 'createApplication'])->name('create_application');
    Route::post('application/saveapplicationdraft', [ApplicationController::class, 'saveApplicationDraft'])->name('save_application_draft');
    Route::post('application/submitapplication', [ApplicationController::class, 'submitApplication'])->name('submit_application');
    Route::post('application/applicationupload', [ApplicationController::class, 'applicationUploads'])->name('upload_application');
    Route::post('/application/update/{id}', [ApplicationController::class, 'update'])->name('update_application');
    Route::delete('/application/delete/{id}', [ApplicationController::class, 'destroy'])->name('delete_application');
    Route::get('/application/show/{id}', [ApplicationController::class, 'show'])->name('show_application');
    Route::get('/application/showapplicationbyclient/{id}', [ApplicationController::class, 'showApplicationIdbyClient'])->name('show_application_client');
    Route::get('/application/show_by_application_id/{id}', [ApplicationController::class, 'showByApplicationID'])->name('show_by_application_id');
    Route::get('/application/searchbyclientid/{name}/{clientid}', [ApplicationController::class, 'search'])->name('search_application');
    Route::get('/application/searchbyuser/{name}/{useriid}', [ApplicationController::class, 'searchbyUser'])->name('search_application_by_user');
   // Route::post('/application/application_uploads/', [ApplicationController::class, 'retrieveUploads'])->name('retrieve_application_uploads');
    Route::get('/application/search_application_client/{id}', [ApplicationController::class, 'search_application_client'])->name('search_application_client');
    Route::post('/application/approveapplication/', [ApplicationController::class, 'approveApplication'])->name('approve_application');
    Route::post('/application/declineapplication/', [ApplicationController::class, 'declineApplication'])->name('decline_application');
    Route::post('/application/return_application_draft/', [ApplicationController::class, 'returnApplicationDraft'])->name('return_application_draft');
    Route::post('/application/applicationbyclientLocation', [ApplicationController::class, 'ApplicationByClientLocation'])->name('search_application_by_client_location');
    Route::get('/application/top30Applications/{id}', [ApplicationController::class, 'top30Applications'])->name('top30Applications');
    Route::get('/application/applicationsinreview/{id}', [ApplicationController::class, 'applicationsInReview'])->name('applications_in_review');
    Route::post('/application/application_uploads/{id}', [ApplicationController::class, 'retrieveUploads'])->name('retrieve_application_uploads');
    Route::post('/application/download_file', [ApplicationController::class, 'download'])->name('download_uploads');
    Route::post('/application/application_document/delete', [ApplicationController::class, 'deleteUpload'])->name('delete_upload');

    //Other Attributes
    //Character
    Route::post('character/update', [CharacterController::class, 'updateCharacter'])->name('update_character_details');
    Route::post('character/', [CharacterController::class, 'getCharacter'])->name('get_character_details');
    Route::delete('character/{application_id}', [CharacterController::class, 'destroy'])->name('delete_character_details');
    //Employment
    Route::post('employment/update', [EmploymentController::class, 'updateEmployement'])->name('update_employment_details');
    Route::post('employment/', [EmploymentController::class, 'getEmployment'])->name('get_employment_details');
    Route::delete('character/{application_id}', [EmploymentController::class, 'destroy'])->name('delete_employment_details');
    //Membership
    Route::post('membership/update', [MembershipController::class, 'updateMembership'])->name('update_membership_details');
    Route::post('membership/', [MembershipController::class, 'getMembership'])->name('get_memembership_details');
    Route::delete('membership/{application_id}', [MembershipController::class, 'destroy'])->name('delete_membership_details');
    //Name_change
    Route::post('namechange/', [NameChangeController::class, 'getNameChange'])->name('get_name_change_details');
    Route::post('namechange/update', [NameChangeController::class, 'updateNameChange'])->name('update_name_change_details');
    Route::delete('namechange/{application_id}', [NameChangeController::class, 'destroy'])->name('delete_name_change_details');
    //People_at_address
    Route::post('nameofpeopleataddress/', [NameofPeopleatAddressController::class, 'getnameofpeopleataddress'])->name('get_name_change_details');
    Route::post('nameofpeopleataddress/update', [NameofPeopleatAddressController::class, 'updatenameofpeopleataddress'])->name('update_name_change_details');
    Route::delete('nameofpeopleataddress/{application_id}', [NameofPeopleatAddressController::class, 'destroy'])->name('delete_name_change_details');
    //Children
    Route::post('children/', [NumberofChildrenController::class, 'getChildren'])->name('get_children_details');
    Route::post('children/update', [NumberofChildrenController::class, 'updateChildren'])->name('update_children_details');
    Route::delete('children/{application_id}', [NumberofChildrenController::class, 'destroy'])->name('delete_children');
    //Other_Nationality
    Route::post('other_nationality/', [OtherNationalityController::class, 'getOtherNationality'])->name('get_other_nationality_details');
    Route::post('other_nationality/update', [OtherNationalityController::class, 'updateOtherNationality'])->name('update_other_nationality_details');
    Route::delete('other_nationality/{application_id}', [OtherNationalityController::class, 'destroy'])->name('delete_other_nationality');
    //People_at_home
    Route::post('people_at_home/', [PeopleAtHomeController::class, 'getPeopleAtHome'])->name('get_people_at_home_details');
    Route::post('people_at_home/update', [PeopleAtHomeController::class, 'updatePeopleAtHome'])->name('update_people_at_home_details');
    Route::delete('people_at_home/{application_id}', [PeopleAtHomeController::class, 'destroy'])->name('delete_people_at_home');
    //Qualification
    Route::post('qualification/', [QualificationController::class, 'getQualification'])->name('get_qualification_details');
    Route::post('qualification/update', [QualificationController::class, 'updateQualification'])->name('update_qualification_details');
    Route::delete('qualification/{application_id}', [QualificationController::class, 'destroy'])->name('delete_qualification');
    //Travel
    Route::post('travel/', [TravelController::class, 'getTravel'])->name('get_travel_details');
    Route::post('travel/update', [TravelController::class, 'updateTravel'])->name('update_travel_details');
    Route::delete('travel/{application_id}', [TravelController::class, 'destroy'])->name('delete_travel');
    //TravelFive
    Route::post('travelfive/', [TravelFiveController::class, 'getTravelFive'])->name('get_travelfive_details');
    Route::post('travelfive/update', [TravelFiveController::class, 'updateTravelFive'])->name('update_travelfive_details');
    Route::delete('travelfive/{application_id}', [TravelFiveController::class, 'destroy'])->name('delete_travelfive');

    //Previous Marriage
    Route::post('previousmarriage/', [PreviousMarriageController::class, 'getprevious_marriages'])->name('get_previous_marriages_details');
    Route::post('previousmarriage/update', [PreviousMarriageController::class, 'updateprevious_marriages'])->name('update_previous_marriages_details');
    Route::delete('previousmarriage/{application_id}', [PreviousMarriageController::class, 'destroy'])->name('delete_previous_marriages');
    //Previous Partner
    Route::post('partnermarriedbefore/', [PartnerMarriedBeforeController::class, 'getPartnerMarriedBefore'])->name('get_partner_married_before_details');
    Route::post('partnermarriedbefore/update', [PartnerMarriedBeforeController::class, 'updatePartnerMarriedBefore'])->name('update_partner_married_before_details');
    Route::delete('partnermarriedbefore/{application_id}', [PartnerMarriedBeforeController::class, 'destroy'])->name('delete_partner_married_before');

    //Messages
    Route::post('message/sendclientmessage', [MessageController::class, 'sendClientMessage'])->name('send_message_client');
    Route::get('/message/showmessagesbyuserid/{id}', [MessageUserController::class, 'showMessagesbyUserID'])->name('show_messages_userid');
    Route::post('/message/replyusermessage', [MessageController::class, 'replyUserMessage'])->name('reply_user_message');
    Route::get('/message/showmessagesbyclientid/{id}', [MessageController::class, 'showMessagesbyClientID'])->name('show_messages_clientid');
    Route::get('/message/showmessagesbyclientidreceived/{id}', [MessageController::class, 'showMessagesbyClientIDReceived'])->name('show_messages_clientid_received');
    Route::post('/message/viewclientmessage', [MessageController::class, 'viewClientmessage'])->name('view_client_message');
    Route::get('message/', [MessageController::class, 'index'])->name('message');
    Route::post('/message/sendusermessage', [MessageUserController::class, 'sendUserMessage'])->name('send_message_user');
    Route::post('/message/viewusermessage', [MessageUserController::class, 'viewUserMessage'])->name('view_user_message');
    Route::post('/message/replyclientmessage', [MessageUserController::class, 'replyClientMessage'])->name('reply_client_message');
    Route::get('/message/{id}', [MessageController::class, 'showMessage'])->name('show_message');
    Route::get('/message/showmessagesbyuseridreceived/{id}', [MessageUserController::class, 'showMessagesbyUserIDReceived'])->name('show_messages_userid_received');
    Route::post('/message/client/deletemessage', [MessageController::class, 'deleteMessage'])->name('delete_message');
    Route::get('/message/user/{id}', [MessageUserController::class, 'showUserMessage'])->name('show_user_message');
    Route::get('/message/showmessagesfromallclients/{userid}', [MessageUserController::class, 'showMessagesFromAllClients'])->name('show_messages_from_clients');
    Route::post('/message/user/deletemessage', [MessageUserController::class, 'deleteUserMessage'])->name('delete_user_message');

    Route::get('/message/searchreceived/{id}/{keyword}', [MessageController::class, 'searchRecevied'])->name('search_client_recevied');
    Route::get('/message/searchsent/{id}/{keyword}', [MessageController::class, 'searchSent'])->name('search_client_sent');

    Route::get('/message/searchreceiveduser/{userid}/{keyword}', [MessageUserController::class, 'searchReceviedUser'])->name('search_User_recevied');
    Route::get('/message/searchsentuser/{userid}/{keyword}', [MessageUserController::class, 'searchSentUser'])->name('search_User_sent');

    Route::post('/message/viewclientreceivedmessage', [MessageController::class, 'viewClientReceivedMessage'])->name('view_client_received_message');
    Route::post('/message/viewclientsentmessage', [MessageController::class, 'viewClientSentMessage'])->name('view_client_sent_message');

    Route::post('/message/user/retagmessage', [MessageUserController::class, 'retagUserMessage'])->name('retag_user_message');

    //Locations
    Route::post('/location/add', [LocationController::class, 'store'])->name('create_location');
    Route::get('/location', [LocationController::class, 'index'])->name('location');
    Route::get('/location/{id}', [LocationController::class, 'showLocationDetails'])->name('show_location_details');
    Route::get('/location/client/{id}', [LocationController::class, 'showClientLocation'])->name('show_client_locations');
    Route::post('/location/client/update', [LocationController::class, 'updateClientLocation'])->name('update_client_location'); //put
    Route::get('/location/search/{search}', [LocationController::class, 'search'])->name('search_client_location');
    Route::post('/location/update', [LocationController::class, 'update'])->name('update_location'); //put
    Route::get('/location/get/branch', [LocationController::class, 'LocationBranch'])->name('location_branch');
    //Tracking
    Route::post('/tracking/create', [TrackingController::class, 'store'])->name('create_tracking');
    Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking');
    Route::get('/tracking/showbyclientid/{id}', [TrackingController::class, 'showTrackingbyClientID'])->name('show_tracking_details_by_client_id');
    Route::get('/tracking/showbyapplicationid/{id}', [TrackingController::class, 'showTrackingbyApplicationID'])->name('show_tracking_details_by_app_id');
    Route::post('/tracking/update', [TrackingController::class, 'update'])->name('update_tracking');  //put
    Route::post('/tracking/delete/{id}', [TrackingController::class, 'destroy'])->name('delete_tracking');  //put
    Route::get('/tracking/byTrackingid/{id}', [TrackingController::class, 'showTrackingbyID'])->name('show_tracking_by_id');
    Route::get('/tracking/search/{search}', [TrackingController::class, 'search'])->name('search_tracking');


    //Courrier
    Route::post('/courrier/add_courrier', [Tracking_TypeController::class, 'store'])->name('add_courrier');
    Route::get('/courrier', [Tracking_TypeController::class, 'index'])->name('courrier');
    Route::get('/courrier/{id}', [Tracking_TypeController::class, 'show'])->name('show_courrier_details');
    Route::post('/courrier/update', [Tracking_TypeController::class, 'update'])->name('update_courrier');  //put
    Route::post('/courrier/{id}', [Tracking_TypeController::class, 'destroy'])->name('delete_courrier');  //put
    Route::get('/courrier/search/{search}', [Tracking_TypeController::class, 'search'])->name('search_courrier');

    //Generate PDF
    Route::get('/generatepdf/prescreening', [generatePDFController::class, 'generatePrescreeningPDF'])->name('generate_prescreening_pdf');
    Route::get('/generatepdf/application', [generatePDFController::class, 'generateApplicationPDF'])->name('generate_application_pdf');


    //Notes
    Route::post('/notes/create', [NotesController::class, 'store'])->name('create_tracking');
    Route::get('/notes', [NotesController::class, 'index'])->name('tracking');
   // Route::get('/notes/show/{id}', [NotesController::class, 'show'])->name('show_tracking_details');
   // Route::put('/notes/update', [NotesController::class, 'update'])->name('update_tracking');
    Route::post('/notes/delete', [NotesController::class, 'destroy'])->name('delete_tracking');  //put


    //Groups
    Route::post('/groups/create', [GroupsController::class, 'store'])->name('create_group');
    Route::post('/groups/client/assign_client_group', [GroupsController::class, 'assignClientGroup'])->name('assign_client_group');
    Route::get('/groups', [GroupsController::class, 'index'])->name('group');
    Route::get('/groups/show/{id}', [GroupsController::class, 'show'])->name('show_groups');
    Route::post('/groups/update', [GroupsController::class, 'update'])->name('update_group');  //put
    Route::post('/groups/client/group_change', [GroupsController::class, 'updateClientGroup'])->name('update_client_group');
    Route::post('/groups/client/retrieve_group', [GroupsController::class, 'getClientGroup'])->name('get_client_group');
    Route::post('/groups/delete', [GroupsController::class, 'destroy'])->name('delete_group');  //put

    //Branches
    Route::post('/branch/create', [BranchController::class, 'store'])->name('create_branch');
    Route::get('/branch', [BranchController::class, 'index'])->name('branch');
    Route::get('/branch/branchbyuserid/{id}', [BranchController::class, 'branchbyuserid'])->name('branch_by_user_id');
    //branchbyuserid
    Route::get('/branch/show/{id}', [BranchController::class, 'show'])->name('show_branch');
    Route::post('/branch/update', [BranchController::class, 'update'])->name('update_branch');  //put
    Route::post('/branch/delete', [BranchController::class, 'destroy'])->name('delete_branch');  //put
    Route::get('/branch/search/{search}', [BranchController::class, 'search'])->name('search_branch');

    //Settings
    Route::get('/company', [SettingsController::class, 'index'])->name('company_info');
    Route::post('/company/setup', [SettingsController::class, 'store'])->name('setup_company');
    Route::post('/company/update', [SettingsController::class, 'update'])->name('update_company_details');
    Route::post('/company/get_company_details', [SettingsController::class, 'getCompanyDetails'])->name('get_company_details');
    //Route::get('/company/show', [SettingsController::class, 'show'])->name('show_company_details');
    Route::post('/company/logo/', [SettingsController::class, 'retrieveLogo'])->name('retrieve_company_logo');
    Route::post('/company/delete', [SettingsController::class, 'delete'])->name('delete_company_details');


    //Invoice
    Route::get('/invoice', [InvoicingController::class, 'index'])->name('invoice');
    Route::get('/invoice/clientid/{id}', [InvoicingController::class, 'InvoiceByClientID'])->name('invoice_client_id');
    Route::get('/invoice/byappid/{appid}', [InvoicingController::class, 'InvoiceByAppID'])->name('invoice_client_id');
    Route::post('/invoice/generate', [InvoicingController::class, 'generateInvoice'])->name('generate_invoice');
    Route::post('/invoice/editInvoice', [InvoicingController::class, 'editInvoice'])->name('generate_invoice');
    Route::post('/invoice/addonInvoice', [InvoicingController::class, 'addonInvoice'])->name('add_onto_invoice');
    Route::post('/invoice/payInvoice', [InvoicingController::class, 'payInvoice'])->name('pay_invoice');
    Route::post('invoice/generatePDF', [InvoicingController::class, 'genInvoicePDF'])->name('pdf_invoice');
    //Billing
    Route::get('/billing', [BillingController::class, 'index'])->name('all_billing_items');
    Route::post('/billing/setup', [BillingController::class, 'store'])->name('setup_billing');
    Route::post('/billing/update', [BillingController::class, 'update'])->name('update_billing_items');
    Route::post('/billing/delete', [BillingController::class, 'destroy'])->name('delete_billing_items');
    Route::get('/billing/show/{id}', [BillingController::class, 'show'])->name('show_billitem');
	Route::get('/billing/activebills', [BillingController::class, 'ActiveBillItems'])->name('active_billing');
    Route::get('/billing/search/{search}', [BillingController::class, 'search'])->name('search_billitems');


    //Visa Type
    Route::get('/visa_type', [VisaTypeController::class, 'index'])->name('all_visa_types');
    Route::post('/visa_type/setup', [VisaTypeController::class, 'store'])->name('create_visa_type');
    Route::post('/visa_type/update', [VisaTypeController::class, 'update'])->name('update_visa_type');
    Route::post('/visa_type/delete', [VisaTypeController::class, 'destroy'])->name('delete_visa_type');
    Route::get('/visa_type/show/{id}', [VisaTypeController::class, 'show'])->name('show_visa_type');


    //User Reference
    Route::get('/user_reference', [UserReferenceController::class, 'index'])->name('all_user_references');
    Route::post('/user_reference/setup', [UserReferenceController::class, 'store'])->name('create_user_reference');
    Route::post('/user_reference/update', [UserReferenceController::class, 'update'])->name('update_user_reference');
    Route::post('/user_reference/delete', [UserReferenceController::class, 'destroy'])->name('delete_user_reference');
    Route::get('/user_reference/show/{id}', [UserReferenceController::class, 'show'])->name('show_user_reference');


     //Branch Reference
     Route::get('/branch_reference', [BranchReferenceController::class, 'index'])->name('all_branch_references');
     Route::post('/branch_reference/setup', [BranchReferenceController::class, 'store'])->name('create_branch_reference');
     Route::post('/branch_reference/update', [BranchReferenceController::class, 'update'])->name('update_branch_reference');
     Route::post('/branch_reference/delete', [BranchReferenceController::class, 'destroy'])->name('delete_branch_reference');
     Route::get('/branch_reference/show/{id}', [BranchReferenceController::class, 'show'])->name('show_branch_reference');


});







Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


// Route::middleware('auth:api')->get('/client', function (Request $request) {
//     return $request->client();
// });
