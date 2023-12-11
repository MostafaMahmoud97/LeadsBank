<?php

namespace App\Http\Resources\CRM;

use Illuminate\Http\Resources\Json\JsonResource;

class LeadsBankResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "first_name" => $this->first_name,
            "last_name" => $this->last_name,
            "rate" => $this->rate,
            "transaction_type" => $this->transaction_type . ($this->transaction_type == "commission based" ? " | " .$this->commission_based ." | ". $this->commission_type : ""),
            "price_percentage" => $this->transaction_type == "immediate" ? $this->price_percentage : ($this->transaction_type=="commission based" && $this->commission_based != "flat rate" ? $this->price_percentage." %" : $this->price_percentage),        ];
    }
}
