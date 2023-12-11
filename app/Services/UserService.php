<?php


namespace App\Services;


use App\Http\Resources\User\ShowDataResource;
use App\Models\Media;
use App\Models\User;
use App\Traits\GeneralFileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class UserService
{
    use GeneralFileService;

    public function registerUser($request){
        $User = User::create([
            "first_name" => $request->first_name,
            "last_name" => $request->last_name,
            "country_code" => $request->country_code,
            "phone" => $request->phone,
            "email" => $request->email,
            "password" => Hash::make($request->password)
        ]);

        if($request->logo){
            $path = "User_logo/";
            $file_name = $this->SaveFile($request->logo,$path);
            $type = $this->getFileType($request->logo);

            Media::create([
                'mediable_type' => $User->getMorphClass(),
                'mediable_id' => $User->id,
                'title' => "Logo",
                'type' => $type,
                'directory' => $path,
                'filename' => $file_name
            ]);
        }


        auth()->setUser($User);

        $token = Auth::user()->createToken('passport_token')->accessToken;
        $user = Auth::user();

        $data = ['user' => $user, 'token' => $token];
        return Response::successResponse($data,"User has been registered success");
    }

    public function login($request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('passport_token')->accessToken;

            return Response::successResponse(["user" => $user, "token" => $token], 'User login successfully.');
        } else {
            return Response::errorResponse('Unauthorised.');
        }
    }


    public function forgot_password($request){

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if($status == Password::RESET_LINK_SENT){
            return Response::successResponse([],$status);
        }

        if ($status == Password::RESET_THROTTLED){
            return Response::errorResponse('reset message is sent to mail');
        }elseif ($status == Password::INVALID_USER){
            return Response::errorResponse('this user not found');
        }

        return Response::errorResponse($status);
    }

    public function callback_reset(Request $request){

        dd($request);
        //return redirect()->to('https://www.customer.boxbyld.tech/reset-password?token='.$request->token."&email=".$request->email);
    }

    public function reset_password(Request $request){
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed'
        ]);

        $status = Password::reset(
            $request->only('email','password','password_confirmation','token'),
            function ($user) use ($request){
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60)
                ])->save();
            }
        );

        if ($status == Password::PASSWORD_RESET){
            return Response::successResponse([],"password reset successfully");
        }

        return Response::errorResponse($status,[],500);
    }

    public function show(){
        $user_id = Auth::id();
        $user = User::with("media")->find($user_id);
        return Response::successResponse(ShowDataResource::make($user),"user has been fetched success");
    }

    public function update($request){
        $user = Auth::user();
        $user->update($request->all());
        return Response::successResponse($user,"user has been updated success");
    }

    public function updateLogo($request){
        $user = Auth::user();
        $media = $user->media;

        $media->delete();

        if($request->logo){
            $path = "User_logo/";
            $file_name = $this->SaveFile($request->logo,$path);
            $type = $this->getFileType($request->logo);

            Media::create([
                'mediable_type' => $user->getMorphClass(),
                'mediable_id' => $user->id,
                'title' => "Logo",
                'type' => $type,
                'directory' => $path,
                'filename' => $file_name
            ]);
        }

        return Response::successResponse($user,"user logo has been updated success");

    }

    public function resetUserPassword($request){
        $user = Auth::user();
        $user->update([
            "password" => Hash::make($request->password)
        ]);
        return Response::successResponse($user,"password has been reset success");
    }


}
