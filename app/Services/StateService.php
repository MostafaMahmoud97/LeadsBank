<?php


namespace App\Services;


use App\Models\State;
use Illuminate\Support\Facades\Response;

class StateService
{
    public function getStateBasedOnGoogle($state_name){
        $states = State::with("Country")->where("name",$state_name)->get();
        return Response::successResponse($states,"states have been fetched success");
    }
}
