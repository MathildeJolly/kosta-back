<?php

namespace App\Http\Controllers;

use App\Http\Resources\AlbumResource;
use App\Repositories\Eloquent\AlbumRepository;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
    private $albumRepository;

    public function __construct(AlbumRepository  $albumRepository)
    {
        $this->albumRepository = $albumRepository;
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return AlbumResource::collection($this->albumRepository->all());
    }
}
