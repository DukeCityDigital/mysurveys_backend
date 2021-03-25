<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectParticipants extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $r = parent::toArray($request);
//        var_dump($r);
//        exit;
        return $r;
    }

     /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */


    public function with($request)
    {
        return [
            'meta' => [
//                'key' => 'value',
            ],
        ];
    }
}
