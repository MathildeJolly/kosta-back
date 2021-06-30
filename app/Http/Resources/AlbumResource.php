<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AlbumResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'users' => UserResource::collection($this->whenLoaded('users')),
            'sort' => $this->sortFile(),
            'medias' => $this->getMediaOrdered(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
