<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FilterResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'inputType' => $this->inputType ?? '',
            'sub' => (isset($this->sub) && count($this->sub)) ? FilterResource::collection($this->sub) : [],
            'filtertype' => $this->filtertype ?? '',
            'hide_all_checkbox' => $this->hideAllCheckBox()
        ];
    }

    // to hide "All" checkbox button for input type 'radio'
    public function hideAllCheckBox()
    {
        if (isset($this->inputType) && $this->inputType == 'radio') {
            return true;
        }
        return false;
    }
}
