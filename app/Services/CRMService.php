<?php


namespace App\Services;


use App\Http\Resources\CRM\LeadsBankPaginate;
use App\Http\Resources\CRM\LeadsBankResource;
use App\Http\Resources\CRM\StateResource;
use App\Models\CommissionCart;
use App\Models\ImmediateCart;
use App\Models\LeadsBank;
use App\Models\State;
use Illuminate\Support\Facades\Response;

class CRMService
{
    public function GetStatesAndTransactionType(){
        $state = State::where("country_id",231)->get();
        $transaction_type = [
            "immediate",
            "commission based"
        ];

        $state = StateResource::collection($state);

        $data = [
            "transaction_type" => $transaction_type,
            "states" => $state
        ];

        return $data;
    }

    public function GetLeadsBankBasedOnStateAndTransactionType($request){
        $Leads_bank_check = LeadsBank::select("id")
            ->where(["state_id" => $request->state_id,"transaction_type" => $request->transaction_type])
            ->where(["transaction_type"=>"commission based", "commission_type" => "shared"])
            ->withCount("Clients")->get();

        $leads_bank_ids = [];

        foreach ($Leads_bank_check as $item){
            if($item->clients_count >= 4){
                array_push($leads_bank_ids,$item->id);
            }
        }


        $Leads_bank = LeadsBank::select("id","first_name","last_name","rate","transaction_type","commission_based","commission_type","price_percentage")
        ->where(["state_id" => $request->state_id,"transaction_type" => $request->transaction_type])->orderBy("sort_status","ASC")
            ->where(function ($q){
                $q->where(function ($q){
                    $q->where("transaction_type","immediate")
                        ->orWhere(["transaction_type"=>"commission based", "commission_type" => "exclusive"]);
                })->whereDoesntHave("Clients");
            })->whereNotIn("id",$leads_bank_ids)->paginate(10);

        return LeadsBankPaginate::make($Leads_bank);
    }



    public function addLeadToCart($request){
        $Leads = LeadsBank::where("transaction_type" , $request->transaction_type)
            ->whereIn("id",$request->leads_bank_ids)->get();

        if($request->transaction_type == "immediate"){

            foreach ($Leads as $lead){
                $ImmediateCart = ImmediateCart::where("leads_bank_id",$lead->id)
                    ->where("client_id",$request->client_id)->first();
                if (!$ImmediateCart){
                    ImmediateCart::create([
                        "client_id" => $request->client_id,
                        "leads_bank_id" => $lead->id,
                    ]);
                }
            }

        }elseif ($request->transaction_type == "commission based"){

            foreach ($Leads as $lead){
                $CommissionCart = CommissionCart::where("leads_bank_id",$lead->id)
                    ->where("client_id",$request->client_id)->first();
                if (!$CommissionCart){
                    CommissionCart::create([
                        "client_id" => $request->client_id,
                        "leads_bank_id" => $lead->id
                    ]);
                }
            }

        }

        return [];
    }

    public function showLeadsDetailsInDashboard($client_id){
        $ImmediateCartCount = ImmediateCart::where("client_id",$client_id)->get()->count();
        $CommissionCartCount = CommissionCart::where("client_id",$client_id)->get()->count();

        $data = [
            [
                "title" => "Immediate",
                "number_leads" => $ImmediateCartCount
            ],[
                "title" => "Commission Based",
                "number_leads" => $CommissionCartCount
            ]
        ];

        return $data;
    }

    public function openCartDetails($request){
        if($request->transaction_type == "immediate"){
            $CartDetails = ImmediateCart::where("client_id",$request->client_id)->get();
        }elseif ($request->transaction_type == "commission based"){
            $CartDetails = CommissionCart::where("client_id",$request->client_id)->get();
        }

        $Leads = [];

        foreach ($CartDetails as $detail){
            $LeadsBank = $detail->LeadBank;
            array_push($Leads,$LeadsBank);
        }


        return LeadsBankResource::collection($Leads);
    }

    public function removeLeadFromCart($request){
        if($request->transaction_type == "immediate"){
            $CartDetails = ImmediateCart::where(["client_id"=>$request->client_id,"leads_bank_id" => $request->lead_id])->first();
        }elseif ($request->transaction_type == "commission based"){
            $CartDetails = CommissionCart::where(["client_id"=>$request->client_id,"leads_bank_id" => $request->lead_id])->first;
        }

        if (!$CartDetails){
            return "No Lead by this id";
        }

        $CartDetails->delete();

        return [];
    }
}
