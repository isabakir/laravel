<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SmsCode;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    //

    public function index(){
        return response()->json(["user"=>auth()->user()]);
    }
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);


        $user->save();

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->generateApiToken();
            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token
            ]);
        } else {
            return response()->json([
                'message' => 'Login failed'
            ], 400);
        }
    }

    public function createPhoneCode(Request $request)
    {
        $request->validate([
            'phone' => 'required'
        ]);

        $smsCode = new SmsCode();
        $smsCode->phone = $request->phone;
        $smsCode->sms_code = rand(1000, 9999);
        $smsCode->save();

        return response()->json([
            "status"=>"success",
            "code"=>200,
            'message' => 'Phone code created successfully',
            'user' => [
                'phone' => $smsCode->phone,

            ]
        ]);
    }

    /** sms code enter */
    public function enterPhoneCode(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'sms_code' => 'required'
        ]);

        $smsCode = SmsCode::where('phone', $request->phone)
            ->where('sms_code', $request->sms_code)
            ->first();


        if ($smsCode) {
            $smsCode->status = 'verified';
            $smsCode->save();
            $user = User::where('phone', $request->phone)->first();
            if($user){
              //  $user->phone_verified_at = now();
                 $token = $user->generateApiToken();
               // Auth::login($user);

                return response()->json([
                    'message' => 'Phone code verified successfully',
                    "status"=>"success",
                    "code"=>200,
                    'user' => $user,
                    'access_token' => $token,
                ]);
            }else{
                $user=new User();
                $user->phone=$request->phone;
                $user->save();
                return response()->json([
                    'message' => 'Phone code verification success',
                    "status"=>"success",
                    "code"=>200,
                    'user' => []
                ], 400);
            }

        } else {
            return response()->json([
                'message' => 'Phone code verification failed'
            ], 400);
        }
    }
}
