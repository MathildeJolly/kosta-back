<?php


namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;

interface BaseRepository
{
    public function find($id);

    public function all();
}
