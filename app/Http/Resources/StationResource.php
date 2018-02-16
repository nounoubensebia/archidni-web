<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class StationResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($request->route()->named('station_autocomplete'))
        {
            return [
              'id' => $this->id,
              'name' => $this->name,
                'transport_mode_id' => $this->transport_mode_id
            ];
        }
        else
        {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'transport_mode_id' => $this->transport_mode_id,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude
            ];
        }
    }
}
