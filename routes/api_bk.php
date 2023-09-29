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
Route::post('/client/register', [ClientController::class, 'store'])->name('register_client');

Route::post('client/login', [ClientController::class, 'login'])->name('login');

Route::post('client/validateotp', [ValidatePhoneNumber::class, 'validateOTP'])->name('validate_otp');
Route::post('client/resendotp', [ValidatePhoneNumber::class, 'resendOTP'])->name('resend_otp');
Route::post('client/validatemail', [ValidateEmailController::class, 'validateEmail'])->name('validate_email');
Route::post('client/resendemail', [ValidateEmailController::class, 'resendemail'])->name('resend_email');

//Routes to be protected via Sanctum


//---------------------------------------------------------------------------------------------------------//

//User Routes

Route::post('user/login', [UserController::class, 'login'])->name('login');


//---------------------------------------------------------------------------------------------------------//

//Protected routes which require authentication
Route::group(['middleware'=>['auth:sanctum']],function(){

    //User Routes
    Route::get('/user', [UserController::class, 'index'])->name('user');
    Route::get('/user/search/{name}', [UserController::class, 'search'])->name('search_user');
    Route::get('user/show', [UserController::class, 'show'])->name('show_user');

    //Routes to be protected via Sanctum
    Route::post('user/issuperadmin', [UserController::class, 'isSuperAdmin'])->name('create_superAdmin');
    Route::post('user/isadmin', [UserController::class, 'isAdmin'])->name('create_Admin');

    Route::post('/user/uppdate/{id}', [UserController::class, 'update'])->name('update_user');
    Route::delete('/user/delete', [UserController::class, 'destroy'])->name('delete_user');
    //Password Reset - User
    Route::post('/password/forget_password', [ForgotUserPasswordController::class, 'submitForgetPasswordForm'])->name('user_forgot_password');
    Route::post('/password/reset_password', [ForgotUserPasswordController::class, 'submitResetPasswordForm'])->name('user_reset_password');

    Route::post('user/logout', [UserController::class, 'logout'])->name('logout');
    Route::get('/user/allusers', [UserController::class, 'getAllUsers'])->name('all_users');

    //Client Routes
    Route::get('/client', [ClientController::class, 'index'])->name('client');
    Route::post('/client/update/{id}', [ClientController::class, 'update'])->name('update_client');
    Route::delete('/client/delete/{id}', [ClientController::class, 'destroy'])->name('delete_client');
    Route::get('/client/searchbytype/{search}', [ClientController::class, 'search'])->name('search_client');
    Route::get('/client/show/{id}', [ClientController::class, 'show'])->name('show_client');
    Route::post('/client/searchbylocation/', [ClientController::class, 'clientbyLocation'])->name('search_by_client_location');
    Route::post('/client/top30clients', [ClientController::class, 'top30Clients'])->name('top_30_clients');
    Route::post('/client/verifiedclients', [ClientController::class, 'verifiedClients'])->name('verified_clients');
    Route::post('/client/clientdetailsperapplicationandprescreening', [ClientController::class, 'clientDetailsPerApplicationandPrescreening'])->name('client_details_per_application_and_prescreening');
    Route::post('/client/logout', [ClientController::class, 'logout'])->name('logout');
    Route::post('/client/updateoffice/', [ClientController::class, 'updateCLientOffice'])->name('update_client_office');
    Route::post('/client/updategroup', [ClientController::class, 'updateGroup'])->name('update_client_group');
    Route::post('/client/updateagent', [ClientController::class, 'updateUser'])->name('update_client_agent');


    // Client Pre-Screening Routes
    Route::get('prescreening', [PreScreeningController::class, 'index'])->name('prescreen');
    Route::post('prescreening/saveprescreendraft', [PreScreeningController::class, 'savePrescreenDraft'])->name('prescreen_save_draft');
    Route::post('prescreening/submitprescreen', [PreScreeningController::class, 'submitPrescreen'])->name('submit_prescreen');
   // Route::put('/prescreening/{id}', [PreScreeningController::class, 'update'])->name('update_prescreen_client');
    Route::post('/prescreening/approvescreening', [PreScreeningController::class, 'approveScreening'])->name('approve_prescreening');
    Route::post('/prescreening/declinescreening', [PreScreeningController::class, 'declineScreening'])->name('decline_preScreening');
    Route::get('/prescreening/{id}', [PreScreeningController::class, 'searchByClientID'])->name('search_prescreen_byclientID');
    Route::post('/prescreening/searchbytype/{search}', [PreScreeningController::class, 'search'])->name('search_prescreen_client');
    Route::post('/prescreening/updatebyid/{id}', [PreScreeningController::class, 'update'])->name('update_prescreen_client');
    Route::post('/prescreening/prescreeningbyclientlocation', [PreScreeningController::class, 'preScreeningByClientLocation'])->name('search_prescreen_by_client_location');
    Route::post('/prescreening/top30prescreenings', [PreScreeningController::class, 'top30Prescreening'])->name('top_30_prescreenings');

    // Application Routes
    Route::get('application/', [ApplicationController::class, 'index'])->name('application');
    Route::post('application/createapplication', [ApplicationController::class, 'createApplication'])->name('create_application');
    Route::post('application/saveapplicationdraft', [ApplicationController::class, 'saveApplicationDraft'])->name('save_application_draft');
    Route::post('application/submitapplication', [ApplicationController::class, 'submitApplication'])->name('submit_application');
    Route::post('application/applicationupload', [ApplicationController::class, 'applicationUploads'])->name('upload_application');
    Route::post('/application/update/{id}', [ApplicationController::class, 'update'])->name('update_application');
    Route::delete('/application/delete/{id}', [ApplicationController::class, 'destroy'])->name('delete_application');
    Route::get('/application/show/{id}', [ApplicationController::class, 'show'])->name('show_application');
    Route::get('/application/showapplicationbyclient/{id}', [ApplicationController::class, 'showApplicationIdbyClient'])->name('show_application_client');
    Route::get('/application/show_by_application_id/{id}', [ApplicationController::class, 'showByApplicationID'])->name('show_by_application_id');
    Route::get('/application/search/{name}', [ApplicationController::class, 'search'])->name('search_application');
    Route::get('/application/application_uploads/{path}', [ApplicationController::class, 'retrieveUploads'])->name('retrieve_application_uploads');
    Route::get('/application/search_application_client/{id}', [ApplicationController::class, 'search_application_client'])->name('search_application_client');
    Route::post('/application/approveapplication/{id}', [ApplicationController::class, 'approveApplication'])->name('approve_application');
    Route::post('/application/applicationbyclientLocation', [ApplicationController::class, 'ApplicationByClientLocation'])->name('search_application_by_client_location');
    Route::post('/application/top30Applications', [ApplicationController::class, 'top30Applications'])->name('top30Applications');
    Route::post('/application/applicationsinreview', [ApplicationController::class, 'applicationsInReview'])->name('applications_in_review');
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
    Route::get('/message/showclientmessage/{id}', [MessageController::class, 'showMessage'])->name('show_message');
    Route::get('/message/showmessagesbyuseridreceived/{id}', [MessageUserController::class, 'showMessagesbyUserIDReceived'])->name('show_messages_userid_received');
    Route::delete('/message/deleteclientmessage/{id}', [MessageController::class, 'deleteMessage'])->name('delete_message');
    Route::get('/message/showusermessage/{id}', [MessageUserController::class, 'showUserMessage'])->name('show_user_message');
    Route::get('/message/showmessagesfromallclients/{sender}', [MessageUserController::class, 'showMessagesFromAllClients'])->name('show_messages_from_clients');
    Route::delete('/message/deleteusermessage/{id}', [MessageUserController::class, 'deleteUserMessage'])->name('delete_user_message');

    Route::get('/message/searchreceived/{id}/{keyword}', [MessageController::class, 'searchRecevied'])->name('search_client_recevied');
    Route::get('/message/searchsent/{id}/{keyword}', [MessageController::class, 'searchSent'])->name('search_client_sent');

    Route::get('/message/searchreceiveduser/{id}/{keyword}', [MessageUserController::class, 'searchReceviedUser'])->name('search_User_recevied');
    Route::get('/message/searchsentuser/{id}/{keyword}', [MessageUserController::class, 'searchSentUser'])->name('search_User_sent');

    //Locations
    Route::post('/location/add', [LocationController::class, 'store'])->name('create_location');
    Route::get('/location', [LocationController::class, 'index'])->name('location');
    Route::get('/location/show/{id}', [LocationController::class, 'showLocationDetails'])->name('show_location_details');
    Route::get('/location/client/{id}', [LocationController::class, 'showClientLocation'])->name('show_client_locations');
    Route::post('/location/update/{id}', [LocationController::class, 'update'])->name('update_location');
    Route::post('/location/client/update', [LocationController::class, 'updateClientLocation'])->name('update_client_location');
    Route::get('/location/search/{search}', [LocationController::class, 'search'])->name('search_client_location');

    //Tracking
    Route::post('/tracking/create', [TrackingController::class, 'store'])->name('create_tracking');
    Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking');
    Route::get('/tracking/showbyclientid/{id}', [TrackingController::class, 'showTrackingbyClientID'])->name('show_tracking_details_by_client_id');
    Route::get('/tracking/showbyapplicationid/{id}', [TrackingController::class, 'showTrackingbyApplicationID'])->name('show_tracking_details_by_app_id');
    Route::post('/tracking/update', [TrackingController::class, 'update'])->name('update_tracking');
    Route::post('/tracking/{id}', [TrackingController::class, 'destroy'])->name('delete_tracking');



    //Courrier
    Route::post('/courrier/add_courrier', [Tracking_TypeController::class, 'store'])->name('add_courrier');
    Route::get('/courrier', [Tracking_TypeController::class, 'index'])->name('courrier');
    Route::get('/courrier/show/{id}', [Tracking_TypeController::class, 'show'])->name('show_courrier_details');
    Route::post('/courrier/update', [Tracking_TypeController::class, 'update'])->name('update_courrier');
    Route::post('/courrier/delete/{id}', [Tracking_TypeController::class, 'destroy'])->name('delete_courrier');


    //Generate PDF
    Route::get('/generatepdf/prescreening', [generatePDFController::class, 'generatePrescreeningPDF'])->name('generate_prescreening_pdf');
    Route::get('/generatepdf/application', [generatePDFController::class, 'generateApplicationPDF'])->name('generate_application_pdf');


    //Notes
    Route::post('/notes/create', [NotesController::class, 'store'])->name('create_tracking');
    Route::get('/notes', [NotesController::class, 'index'])->name('tracking');
   // Route::get('/notes/show/{id}', [NotesController::class, 'show'])->name('show_tracking_details');
   // Route::put('/notes/update', [NotesController::class, 'update'])->name('update_tracking');
    Route::post('/notes/delete', [NotesController::class, 'destroy'])->name('delete_tracking');


    //Groups
    Route::post('/groups/create', [GroupsController::class, 'store'])->name('create_group');
    Route::get('/groups', [GroupsController::class, 'index'])->name('group');
    Route::get('/groups/show/{id}', [GroupsController::class, 'show'])->name('show_groups');
    Route::post('/groups/update', [GroupsController::class, 'update'])->name('update_group');
    Route::post('/groups/delete', [GroupsController::class, 'destroy'])->name('delete_group');

});







Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


// Route::middleware('auth:api')->get('/client', function (Request $request) {
//     return $request->client();
// });
