<?php

namespace App\Repositories\Eloquent;

use App\Models\Album;
use App\Repositories\BaseRepository;

class AlbumRepository implements BaseRepository {

    private $model;

    public function __construct( Album $model){
        $this->model = $model;
    }

    public function find($id)
    {
        // TODO: Implement find() method.
    }

    public function all()
    {
        return $this->model->all();
    }

    public function findBySlug($slug)
    {
        return $this->model->where('slug', $slug);
    }
}
