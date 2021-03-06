<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class LineCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($request->route()->named('all_lines_and_places'))
        {
            return [
                'lines' => $this->collection
            ];
        }
        else
            return [
                'data' =>$this->collection
            ];
    }
}
