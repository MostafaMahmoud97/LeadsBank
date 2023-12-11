<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private $service;
    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function registerUser(RegisterRequest $request){
        return $this->service->registerUser($request);
    }

    public function login(LoginRequest $request){
        return $this->service->login($request);
    }

    public function forgot_password(Request $request){
        $Validator = Validator::make($request->all(),[
            'email' => 'required|email'
        ]);

        if($Validator->fails()){
            return Response::errorResponse($Validator->errors());
        }

        return  $this->service->forgot_password($request);
    }

    public function callback_reset_password(Request $request){
        $Validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'token' => 'required',
        ]);

        if($Validator->fails()){
            return Response::errorResponse($Validator->errors());
        }

        $this->service->callback_reset($request);
    }

    public function reset_password(Request $request){

        return $this->service->reset_password($request);
    }

    public function show(){
        return $this->service->show();
    }

    public function update(UpdateUserRequest $request){
        return $this->service->update($request);
    }

    public function updateLogo(Request $request){
        $Validator = Validator::make($request->all(),[
            'logo' => 'mimes:jpg,png,jpeg|max:2048',
        ]);

        if($Validator->fails()){
            return Response::errorResponse($Validator->errors());
        }

        return $this->service->updateLogo($request);
    }

    public function resetUserPassword(Request $request){
        $Validator = Validator::make($request->all(),[
            "password" => "required|min:8|confirmed",
        ]);

        if($Validator->fails()){
            return Response::errorResponse($Validator->errors());
        }

        return $this->service->resetUserPassword($request);
    }
}
