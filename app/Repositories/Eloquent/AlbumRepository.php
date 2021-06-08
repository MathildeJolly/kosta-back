<?php

namespace App\Repositories\Eloquent;

use App\Models\Album;
use App\Repositories\BaseRepository;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Http\Resources\AlbumResource;

class AlbumRepository implements BaseRepository {

    private $model;

    public function __construct( Album $model){
        $this->model = $model;
    }

    public function find($id)
    {
        // TODO: Implement find() method.
        return $this->model->where('id', $id)->firstOrFail();
    }

    public function all()
    {
        return $this->model->with('users')->get();
    }

    public function findBySlug($slug)
    {
        return $this->model->where('slug', $slug)->with('users')->get();
    }

    public function store($data)
    {
        return $this->model->create($data);
    }

    public function delete($id)
    {
        return $this->model->find($id)->delete();
    }

    public function update($data, $id)
    {
        $this->model->find($id)->update($data);
        return $this->model->find($id);
    }

    public function getUserAlbums()
    {
        // foreach (auth()->user()->albums as $album) {
        //     $medias = $album->getMedia('media');
        //     foreach ($medias as $media) {
        //         $mediasUrl[] = $media->getUrl();
        //     }
        //     dd($album);
        //     $albums[] = [$album, $mediasUrl];
        // }

        // return $albums;
        return auth()->user()->albums;
    }
}
