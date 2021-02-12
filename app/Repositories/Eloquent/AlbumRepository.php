<?php

namespace App\Repositories\Eloquent;

use App\Models\Album;
use App\Repositories\BaseRepository;
use Illuminate\Http\Request;

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

    public function store($data)
    {
        $this->validate($data, [
            'name' => 'required',
        ]);

        return $this->model->create($data);
    }
}
