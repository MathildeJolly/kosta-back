<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Http\Request;

class UserRepository implements BaseRepository {

    private $model;

    public function __construct(User $model){
        $this->model = $model;
    }

    public function find($id)
    {
        // TODO: Implement find() method.
    }

    public function all()
    {
        return $this->model->with('albums')->get();
    }
}
