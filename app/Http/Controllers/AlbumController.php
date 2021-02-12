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
    public function all()
    {
        return AlbumResource::collection($this->albumRepository->all());
    }

    public function show($slug)
    {
        return new AlbumResource($this->albumRepository->findBySlug($slug)->first());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);
        return $this->albumRepository->store($request->all());
    }
    public function delete($slug){
        return $this->albumRepository->delete($slug);
    }
}
