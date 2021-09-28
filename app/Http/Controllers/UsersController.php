<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Users;

class UsersController extends Controller
{
  public function __construct()
  {
    //  $this->middleware('auth:api');
  }
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function authenticate(Request $request)
  {
    $this->validate($request, [
      'email' => 'required',
      'password' => 'required'
    ]);
    //  dd($request->all());
    $user = Users::where('email', $request->input('email'))->first();
    // Prevent from appearing error when user does not exist
    if (empty($user)) {
      return response()->json(['status' => 'fail', 'msg' => 'The user does not exist.'], 401);
    }
    if (Hash::check($request->input('password'), $user->password)) {
      $apikey = base64_encode(str_random(40));
      // dd($apikey); // Y0RDZE1FTDlaczFMdW5OcGVhTmhyT05LOW8yRjNkTVJQOFZqQ2o1TA==
      Users::where('email', $request->input('email'))->update(['api_key' => "$apikey"]);;
      return response()->json(['status' => 'success', 'api_key' => $apikey]);
    } else {
      return response()->json(['status' => 'fail', 'msg' => 'Email or password is not correct.'], 401);
    }
  }

  /**
   * User register
   * For ref: vendor/illuminate/database/Eloquent/Builder.php
   */
  public function register(Request $request)
  {
    // Validate params of user submited
    $this->validate($request, [
      'name' => 'required',
      'email' => 'required',
      'password' => 'required',
    ]);

    // Judgement: unique of email and name
    $user = Users::where('email', $request->input('email'))
    ->orWhere('name', $request->input('name'))
    ->first();
    if($user){
      return response()->json(['status' => 'fail', 'msg' => 'Name or email exists already.']);
    }

    // Store user info
    $hashPwd = Hash::make($request->input('password'));
    $ret = Users::insert(array_merge($request->all(), ['password' => $hashPwd]));
    if($ret){
      return response()->json(['status' => 'success', 'msg' => 'Register successully']);
    }
    return response()->json(['status' => 'fail', 'msg' => 'Register failed, please try later']);
  }
}
