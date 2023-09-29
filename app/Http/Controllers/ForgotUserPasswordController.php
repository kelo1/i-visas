<?php

namespace App\Http\Controllers;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Notifications\ForgotUserPasswordNotification;

class ForgotUserPasswordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function submitForgetPasswordForm(Request $request)
    {

        $email=$request->email;

        $user_id = User::where('email',$email)->value('id');

        $token = Str::random(64);

        $user_name = User::where('id', $user_id)
              ->value('first_name');

          $fields =  $request->validate([
          'email' => 'required|email|exists:users',
              ]);

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
          ]);

         //Send Email to view
         $toAddress = User::where('email',$fields['email'])->value('email');


         $user = User::findOrFail($user_id);
      $user->notify(new ForgotUserPasswordNotification($user, $user_name, $toAddress, $token));



        $response = [
            'token'=>$token,
          'message'=> 'We have e-mailed your password reset link!',

       ];

       return response($response, 201);
    }

     /**
   * Reset Password
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */

    public function submitResetPasswordForm(Request $request)
    {
        //$request->user_id;

        $request->validate([
           // 'email' => 'required|email|exists:users',
            'password' => 'required|string|confirmed',
           // 'password_confirmation' => 'required'
        ]);

        $updatePassword = DB::table('password_resets')
                            ->where([
                             // 'email' => $request->email,
                              'token' => $request->token
                            ])
                            ->first();

        if(!$updatePassword){
            return back()->withInput()->with('error', 'Invalid token!');
        }

        $user = User::where('id', $request->id)
                    ->update(['password' => Hash::make($request->password),
                               'updated_at' => Carbon::now()]);

        DB::table('password_resets')->where(['email'=> $request->email])->delete();


      $response = [
          'message'=> 'Password changed successfully!',

      ];

      return response($response, 201);
    }
}
