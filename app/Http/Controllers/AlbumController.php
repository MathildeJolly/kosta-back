<?php

namespace App\Http\Controllers;

use App\Http\Resources\AlbumResource;
use App\Mail\InviteToAlbum;
use App\Models\Album;
use App\Models\Invitation;
use App\Repositories\Eloquent\AlbumRepository;
use App\Repositories\Eloquent\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

    public function decline(Request $request, $hash)
    {
        $invitation = Invitation::where('hash', $hash)->first();
        if ($invitation && $invitation->status === Invitation::WAITING) {
            $invitation->status = Invitation::DECLINE;
            $invitation->save();
        }

        return redirect('/')->with('success', "Vous n'avez pas rejoins l'album");
    }

    public function join(Request $request, $hash)
    {
        $invitation = Invitation::where('hash', $hash)->first();
        if ($invitation && $invitation->status === Invitation::WAITING) {
            $album = $this->albumRepository->find($invitation->fk_album_id)->first();
            $album->users()->attach([$invitation->fk_receiver_id]);
            $album->save();

            $invitation->status = Invitation::ACCEPTED;
            $invitation->save();
        }


        return redirect('/')->with('success', "Vous avez rejoins l'album");
    }

    public function inviteCollaborateur(Request $request, $slug)
    {
        $album = $this->albumRepository->findBySlug($slug)->first();
        foreach ($request->get('email') as $user) {
            if ($user['email']) {

                $userModel = $this->userRepository->findByEmail($user['email'])->first();
                if (!$userModel) {
                    $this->sendMailForCreateUser($user['email'], $album);

                    return response()->json(['message' => "Le mail de création d'un utilisateur à bien été envoyé"]);

                }
                $already = Invitation::where('fk_receiver_id', $userModel->id)->where('fk_album_id', $album->id)->where('fk_sender_id', auth()->user()->id)->first();

                if ($already) {
                    return $this->returnJsonErreur('Une invitation à déjà été envoyé');
                }
                $invitation = new Invitation();
                $invitation->fk_sender_id = auth()->user()->id;
                $invitation->fk_receiver_id = $userModel->id;
                $invitation->fk_album_id = $album->id;
                $invitation->hash = $this->unique_random((new Invitation())->getTable(), 'hash', 32);
                $invitation->save();
                try {
                    Mail::to($userModel->email)->send(new InviteToAlbum($invitation));
                } catch (\Exception $e) {
                    return $this->returnJsonErreur("Erreur dans l'envoie du mail");
                }
            }
        }

        return response()->json(['message' => "Le mail a bien été envoyé"]);

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

    public function updateOrderOfFile(Request $request, $slug, $chunk, $id)
    {
        $album = $this->albumRepository->findBySlug($slug)->first();
        if (!$album) {
            $this->returnJsonErreur('Aucun album trouvée');
        }
        $place = DB::table('media')->where('chunk_id', $chunk)->get()->sortBy('order')->last();
        DB::table('media')->where('id', $id)->update([
            'chunk_id'   => $chunk,
            'media_date' => $place->media_date,
            'order'      => $place->order + 1,
        ]);

        $album = $this->albumRepository->findBySlug($slug)->first();


        return new AlbumResource($album);

    }

    public function updateChunkOrder(Request $request, $slug)
    {
        $album = $this->albumRepository->findBySlug($slug)->first();

        if ($request->has('order')) {
            collect($request->get('order'))->each(function ($item) {
                DB::table('media')->where('chunk_id', $item['id'])->update([
                    'chunk_order' => $item['place']
                ]);
            });
        }

        return new AlbumResource($album);

    }

    public function storeFileForAlbum(Request $request, $slug)
    {
        $album = $this->albumRepository->findBySlug($slug)->first();
        $media = $album->medias;
        $chunk = $media->isNotEmpty() ? $media->groupBy('chunk_id')->count() : 0;

        if ($request->file('file')) {
            $exif = Image::make($request->file('file'))->exif();
            $fileAdder = $album->addMediaFromRequest('file');
            $res = $fileAdder->toMediaCollection('photo');
            if ($exif['DateTimeOriginal']) {
                $date = Carbon::parse($exif['DateTimeOriginal'])->format('Y-m-d H:i:s');
                DB::table('media')->where('id', $res->id)->update([
                    'media_date' => $date,
                    //'order'       => $index + 1,
                    //'chunk_order' => $chunk
                ]);
            }
        }

        $album = Album::where('slug', $slug)->first();
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
        collect(Album::where('slug', $slug)->first()->medias)->groupBy('chunk_id')->values()->each(function ($chunk, $chunkIndex) {
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

    public function storeFileForAlbumWithChunk(Request $request, $slug)
    {

        $album = $this->albumRepository->findBySlug($slug)->first();
        $media = $album->medias;
        $chunk = $request->get('name');
        $uuid = Uuid::generate()->string;
        $last = DB::table('media')->where('model_id', $album->id)->get()->sortBy('chunk_order')->last()->chunk_order + 1;
        if ($request->file('file')) {
            $exif = Image::make($request->file('file'))->exif();
            $fileAdder = $album->addMediaFromRequest('file');
            $res = $fileAdder->toMediaCollection('photo');
            if ($exif['DateTimeOriginal']) {
                $date = Carbon::parse($exif['DateTimeOriginal'])->format('Y-m-d H:i:s');
                DB::table('media')->where('id', $res->id)->update([
                    'media_date' => $date,
                    //'order'       => $index + 1,
                    //'chunk_order' => $chunk
                ]);
            }
        }
        $album = Album::where('slug', $slug)->first();


        return (new AlbumResource($album))->additional(['message' => "Chunk crée"]);
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

    public function collaborators(Request $request, $slug)
    {
        $album = $this->albumRepository->findBySlug($slug)->first();
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
