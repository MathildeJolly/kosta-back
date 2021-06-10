<?php

namespace App\Http\Controllers;

use App\Http\Resources\AlbumResource;
use App\Models\Album;
use App\Repositories\Eloquent\AlbumRepository;
use App\Repositories\Eloquent\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Image;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Webpatser\Uuid\Uuid;

class AlbumController extends Controller
{
    private $albumRepository;

    public function __construct(AlbumRepository $albumRepository, UserRepository $userRepository)
    {
        $this->albumRepository = $albumRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function all()
    {
        // return $this->albumRepository->getUserAlbums();
        return AlbumResource::collection($this->albumRepository->getUserAlbums());
    }

    public function show($slug)
    {
        $album = $this->albumRepository->findBySlug($slug)->first();
        if (!$album) {
            return $this->returnJsonErreur('Aucun album');
        }

        return new AlbumResource($album);
    }

    public function storeFileForAlbum(Request $request, $id)
    {
        $album = $this->albumRepository->find($id);

        if ($request->photos) {
            $album->addMultipleMediaFromRequest(['photos'])
                ->each(function ($fileAdder, $index) use ($request) {
                    $res = $fileAdder->toMediaCollection('photo');
                    $exif = Image::make(public_path() . '/medias/' . $res->id . '/' . $res->file_name)->exif();
                    if (isset($exif['FileDateTime'])) {
                        $date = Carbon::parse($exif['FileDateTime'])->format('Y-m-d H:i:s');
                        DB::table('media')->where('id', $res->id)->update([
                            'media_date' => $date,
                        ]);
                    }
                }
                );
        }

        collect($album->medias)->groupBy('media_date')->each(function ($iem) {
            $uuid = $iem->filter(function ($item) {
                return $item->chunk_id;
            })->first()->chunk_id;
            if (!$uuid) {
                $uuid = Uuid::generate()->string;
            }
            $iem->each(function ($item) use ($uuid) {
                DB::table('media')->where('id', $item->id)->update([
                    'chunk_id' => $uuid,
                ]);
            });

        });

        return (new AlbumResource($album))->additional(['message' => "Image ajouté"]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);
        $album = $this->albumRepository->store($request->all());

        if ($request->photos) {
            $album->addMultipleMediaFromRequest(['photos'])
                // ->usingFileName(substr(md5(rand()), 0, 7))
                ->each(function ($fileAdder) {
                    $fileAdder->toMediaCollection('photo');
                }
                );
        }

        $album->users()->sync([
            auth()->user()->id
        ]);

        return (new AlbumResource($album))->additional(['message' => "L'album a bien été créé"]);
    }

    public function collaborators(Request $request, $id)
    {
        $album = $this->albumRepository->find($id)->first();
        if (!$album) {
            return $this->returnJsonErreur('Aucun album');
        }

        $collaborator = $this->userRepository->findByEmail($request->email)->first();
        if (!$collaborator) {
            return $this->returnJsonErreur('Aucun utilisateur');
        }

        $album->users()->sync(
            $collaborator->id, false
        );

        return (new AlbumResource($album))->additional(['message' => "Les collaborateurs ont été ajoutés"]);
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
