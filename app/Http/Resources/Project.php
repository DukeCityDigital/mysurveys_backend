<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class Project extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        date_default_timezone_set('America/Denver');
        return [
            'project_title' => $this->project_title,
            'description' => $this->description,
            'responsible_person' => $this->responsible_person,
            'state' => $this->state,
            'start_state' => $this->start_state,
            'link' => $this->link,
            // 'link_method' => $this->link_method,
            'payout_type' => $this->payout_type,
            'max_payout' => $this->max_payout,
            'exp_payout' => $this->exp_payout,
            'desired_sample_size' => $this->desired_sample_size,
            'desired_num_invitations' => $this->desired_num_invitations,
            'payout_type' => $this->payout_type,
            'created_at' => date($this->created_at),
            'defaultend' => date($this->defaultend),
            'defaultstart' => date($this->defaultstart),
            'creator_userid' => $this->creator_userid,
            'id' => $this->id,
            'quota' => $this->quota,
        ];
    }
}
