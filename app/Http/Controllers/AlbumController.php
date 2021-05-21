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
        return AlbumResource::collection($this->albumRepository->getUserAlbums());
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

        $album = $this->albumRepository->store($request->all());

        $album->users()->sync([
            auth()->user()->id
        ]);

        return (new AlbumResource($album))->additional(['message' => "L'album a bien été créé"]);
    }

    public function delete($id)
    {
        $this->albumRepository->delete($id);
        return response()->json(['message' => "L'album a bien été supprimé"]);
    }

    public function update(Request $request, $id)
    {

        $request->validate([
            'name' => 'required',
        ]);
        return (new AlbumResource($this->albumRepository->update($request->all(), $id)))->additional(['message' => "L'album a bien été mis à jour"]);
    }
}
