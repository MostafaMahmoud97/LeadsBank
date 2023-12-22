<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\UserController;
use \App\Http\Controllers\CountryController;
use \App\Http\Controllers\StateController;
use \App\Http\Controllers\LeadsBankController;
use \App\Http\Controllers\CRMController;
use \App\Http\Controllers\CheckOutController;

Route::post("register-user",[UserController::class,"registerUser"]);
Route::post("login",[UserController::class,"login"]);
Route::post("forgot-password",[UserController::class,"forgot_password"]);
Route::get("callback-reset-password",[UserController::class,"callback_reset_password"]);
Route::post("reset-password",[UserController::class,"reset_password"]);

Route::group(["prefix" => "user","middleware" => "auth:api"],function (){
    Route::get("show",[UserController::class,"show"]);
    Route::put("update",[UserController::class,"update"]);
    Route::put("update-logo",[UserController::class,"updateLogo"]);
    Route::put("reset-user-password",[UserController::class,"resetUserPassword"]);
});

Route::group(["prefix" => "countries","middleware" => "auth:api"],function (){
    Route::get("/",[CountryController::class,"index"]);
    Route::get("country/{country_id}",[CountryController::class,"getStateBasedOnCountry"]);
    Route::get("cities",[CountryController::class,"getCities"]);
});

Route::group(["prefix" => "states","middleware" => "auth:api"],function (){
    Route::get("state/{state_name}",[StateController::class,"getStateBasedOnGoogle"]);
});

Route::group(["prefix" => "leads-bank","middleware" => "auth:api"],function (){
    Route::get("service-type",[LeadsBankController::class,"getServicesType"]);
    Route::post("store",[LeadsBankController::class,"store"]);
    Route::get("available-leads",[LeadsBankController::class,"index_available_leads_bank"]);
    Route::put("change-archive-status/{lead_id}",[LeadsBankController::class,"change_archive_status"]);
    Route::get("show-available-lead/{lead_id}",[LeadsBankController::class,"showAvailableLead"]);
    Route::put("update-available-lead/{lead_id}",[LeadsBankController::class,"updateAvailableLead"]);
    Route::delete("delete-available-lead/{lead_id}",[LeadsBankController::class,"deleteAvailableLead"]);
    Route::get("sold-leads",[LeadsBankController::class,"index_sold_leads"]);
    Route::get("show-sold-lead/{lead_id}",[LeadsBankController::class,"show_sold_leads"]);
});

Route::group(["prefix" => "crm","middleware" => "check-api"],function (){
    Route::get("/states-transaction",[CRMController::class,"GetStatesAndTransactionType"]);
    Route::get("/leads-bank",[CRMController::class,"GetLeadsBankBasedOnStateAndTransactionType"]);
    Route::post("/add-leads-to-cart",[CRMController::class,"addLeadsToCart"]);
    Route::get("/leads-bank-dashboard/{client_id}",[CRMController::class,"showLeadsDetailsInDashboard"]);
    Route::get("/open-cart-details",[CRMController::class,"openCartDetails"]);
    Route::post("/remove-lead-in-cart",[CRMController::class,"removeLeadFromCart"]);
});

Route::group(["prefix" => "pay-leads","middleware" => "check-api"],function (){
    Route::post("/check-out",[CheckOutController::class,"checkOut"]);
    Route::post("/create-client",[CheckOutController::class,"CreateClients"]);
});
