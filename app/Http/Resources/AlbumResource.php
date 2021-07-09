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
            'description' => $this->description,
            'users' => UserResource::collection($this->whenLoaded('users')),
            'sort' => $this->sortFile(),
            'medias' => $this->getMediaOrdered(),
            'cover' => !$this->medias->isEmpty() ? str_replace("https", 'http',str_replace('storage', 'media', $this->medias->first()->getFullUrl())) : [],
            'preview' => $this->medias->map(function($media) {
                return str_replace("https","http", str_replace('storage', 'media', $media->getFullUrl()));
            })->skip(1)->take(4),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
