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

    public function updateOrder(Request $request, $slug, $chunk)
    {
        $album = Album::where('slug', $slug)->firstOrFail();

        $medias = DB::table('media')->where('model_id', $album->id)->where('chunk_id', $chunk)->get();
        if ($request->has('order')) {
            collect($request->get('order'))->each(function ($item) use ($medias) {
                $media = $medias->first(function ($e) use ($item) {
                    return $e->id === $item['id'];
                });
                DB::table('media')->where('id', $media->id)->update([
                    'order' => $item['place']
                ]);
            });
        }

        return new AlbumResource($album);

    }

    public function updateChunkOrder(Request $request, $slug)
    {
        $album = Album::where('slug', $slug)->firstOrFail();

        if ($request->has('order')) {
            collect($request->get('order'))->each(function ($item){
                DB::table('media')->where('chunk_id', $item['id'])->update([
                    'chunk_order' => $item['place']
                ]);
            });
        }

        return new AlbumResource($album);

    }

    public function storeFileForAlbum(Request $request, $id)
    {
        $album = $this->albumRepository->find($id);
        $media = $album->medias;
        $chunk = $media->isNotEmpty() ? $media->groupBy('chunk_id')->count() : 0;
        if ($request->photos) {
            $album->addMultipleMediaFromRequest(['photos'])
                ->each(function ($fileAdder, $index) use ($request, $chunk) {
                    $res = $fileAdder->toMediaCollection('photo');
                    $exif = Image::make(public_path() . '/medias/' . $res->id . '/' . $res->file_name)->exif();
                    if (isset($exif['FileDateTime'])) {
                        $date = Carbon::parse($exif['FileDateTime'])->format('Y-m-d H:i:s');
                        DB::table('media')->where('id', $res->id)->update([
                            'media_date' => $date,
                            //'order'       => $index + 1,
                            //'chunk_order' => $chunk
                        ]);
                    }
                }
                );
        }
        $album = Album::find($id);
        collect($album->medias)->groupBy('media_date')->each(function ($iem, $index) {
            $uuid = $iem->filter(function ($item) {
                return $item->chunk_id;
            })->first();

            $uuid = !$uuid ? Uuid::generate()->string : $uuid->chunk_id;

            $iem->each(function ($item, $index) use ($uuid) {
                DB::table('media')->where('id', $item->id)->update([
                    'chunk_id' => $uuid,
                ]);
            });
        });
        collect(Album::find($id)->medias)->groupBy('chunk_id')->values()->each(function ($chunk, $chunkIndex) {
            $chunk->each(function ($item, $index) use ($chunkIndex) {
                if (!$item->order) {
                    $item->order = $index + 1;
                    $item->save();
                }
                if (!$item->chunck_order) {
                    $item->chunk_order = $chunkIndex;
                    $item->save();
                }
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
