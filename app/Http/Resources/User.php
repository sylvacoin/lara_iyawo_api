<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->user_group_id == 4 )
        {
            return [
                "id" => $this->id,
                "first_name" => $this->first_name,
                "last_name" => $this->last_name,
                "gender" => $this->gender,
                "user_group_id" => $this->user_group_id,
                "role_name" => $this->usergroup != null ? $this->usergroup->user_group : null,
                "phone"=> $this->phone,
                "email"=> $this->email,
                "handler_id"=> $this->handler_id,
                "customer_no"=> $this->customer_no,
                "balance"=> $this->balance,
                "w_balance"=> $this->w_balance,
                "has_alert"=> $this->has_alert,
                "is_flagged" => $this->is_flagged
            ];
        }else{
            return [
                "id" => $this->id,
                "first_name" => $this->first_name,
                "last_name" => $this->last_name,
                "gender" => $this->gender,
                "user_group_id" => $this->user_group_id,
                "role_name" => $this->usergroup != null ? $this->usergroup->user_group : null,
                "phone"=> $this->phone,
                "email"=> $this->email,
                "is_flagged" => $this->is_flagged,
                "user_details" => $this->userdetail,
            ];
        }

    }
}
