<?php

namespace App\Http\Resources;

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
            'medias' => $this->medias->map(function (Media $media) {
                    return [
                        $media->collection_name => asset('/media/album/' . $media->file_name)
                    ];
                })->collapse(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
