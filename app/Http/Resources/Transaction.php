<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Transaction extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "customer_id"=> $this->customer_id,
            "customer_name" => ($this->customer != null ? $this->customer->first_name.' '.$this->customer->last_name: ''),
            "trans_type"=> $this->trans_type == '001'?'Withdrawal':'Deposit',
            "card_id"=> $this->card_id,
            "card_name"=> ($this->card != null) ? $this->card->card_name : '',
            "amount" => $this->amount,
            "no_days" => $this->no_days,
            "trans_by" => $this->trans_by,
            "agent_name" => ($this->agent != null) ? $this->agent->first_name.' '.$this->agent->last_name:'',
            "trans_status" => $this->trans_status,
            "display_created_at" => date("d-M-Y g:i a", strtotime($this->created_at)),
            "created_at" => $this->created_at
        ];
    }
}
