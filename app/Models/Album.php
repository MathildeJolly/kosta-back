<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Album extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasSlug;
    // Remplir le Fillable avec les différents nom de colonnes de la DB ex : 'name'

    protected $fillable = ['name', 'slug'];
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function (Model $model) {
                return $model->name;
            })
            ->saveSlugsTo('slug');
    }


    public function sortFile()
    {
        return collect($this->medias)->groupBy('media_date')->filter(function ($item, $index){
            return $index;
        })->map(function($op){
            return $op->map(function($media){
                return [
                    $media->collection_name => asset('/medias/'. $media->id . '/'. $media->file_name),
                ];
            });

        });

    }
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function medias()
    {
        return $this->hasMany(Media::class, 'model_id', 'id');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(368)
            ->height(232)
            ->sharpen(10);
    }
}
