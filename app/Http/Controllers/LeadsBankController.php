<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeadsBank\StoreRequest;
use App\Http\Requests\LeadsBank\UpdateRequest;
use App\Services\LeadsBankService;
use Illuminate\Http\Request;


class LeadsBankController extends Controller
{
    protected $service;
    public function __construct(LeadsBankService $service)
    {
        $this->service = $service;
    }

    public function getServicesType(){
        return $this->service->getServicesType();
    }

    public function store(StoreRequest $request){
        return $this->service->store($request);
    }

    public function index_available_leads_bank(){
        return $this->service->index_available_leads_bank();
    }

    public function change_archive_status($lead_id){
        return $this->service->change_archive_status($lead_id);
    }

    public function showAvailableLead($lead_id){
        return $this->service->showAvailableLead($lead_id);
    }

    public function updateAvailableLead($lead_id,UpdateRequest $request){
        return $this->service->updateAvailableLead($lead_id,$request);
    }

    public function deleteAvailableLead($lead_id){
        return $this->service->deleteAvailableLead($lead_id);
    }

    public function index_sold_leads(){
        return $this->service->index_sold_leads();
    }

    public function show_sold_leads($lead_id){
        return $this->service->show_sold_leads($lead_id);
    }
}
