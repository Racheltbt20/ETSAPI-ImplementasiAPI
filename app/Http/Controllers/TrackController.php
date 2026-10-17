<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET /api/tracks
     */
    public function index()
    {
        $tracks = Track::paginate(20);
        return response()->json($tracks);
    }

    /**
     * Store a newly created resource in storage.
     * PUT/PATCH /api/tracks/{id}
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'track_id'    => 'required|string|unique:tracks,track_id',
            'track_name'  => 'required|string',
            'artist_name' => 'required|string',
            'album_name'  => 'nullable|string',
            'year'        => 'required|integer',
            'popularity'  => 'required|integer',
            'duration_ms' => 'required|integer',
            'language'    => 'nullable|string',
        ]);

        $track = Track::create($validated);

        return response()->json($track, 201);
    }

    /**
     * Display the specified resource.
     * GET /api/tracks/{id}
     */
    public function show(string $id)
    {
        $track = Track::find($id);

        if (!$track) {
            return response()->json(['message' => 'Track tidak ditemukan'], 404);
        }

        return response()->json($track);
    }

    /**
     * Update the specified resource in storage.
     * PUT/PATCH /api/tracks/{id}
     */
    public function update(Request $request, string $id)
    {
        $track = Track::find($id);

        if (!$track) {
            return response()->json(['message' => 'Track tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'track_name'  => 'sometimes|string',
            'artist_name' => 'sometimes|string',
            'album_name'  => 'nullable|string',
            'year'        => 'sometimes|integer',
            'popularity'  => 'sometimes|integer',
            'duration_ms' => 'sometimes|integer',
            'language'    => 'nullable|string',
        ]);

        $track->update($validated);

        return response()->json($track);
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/tracks/{id}
     */
    public function destroy(string $id)
    {
        $track = Track::find($id);

        if (!$track) {
            return response()->json(['message' => 'Track tidak ditemukan'], 404);
        }

        $track->delete();

        return response()->json(['message' => 'Track berhasil dihapus']);
    }

    /**
     * Searching berdasarkan artis/album/judul lagu/tahun/bahasa
     * POST /api/tracks/search
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'artist_name' => 'sometimes|string',
            'album_name'  => 'sometimes|string',
            'track_name'  => 'sometimes|string',
            'year'        => 'sometimes|integer',
            'language'    => 'sometimes|string',
        ]);

        if (empty($validated)) {
            return response()->json(['message' => 'Isi minimal salah satu field untuk mencari'], 422);
        }

        $query = Track::query();

        if (isset($validated['artist_name'])) {
            $query->where('artist_name', 'like', '%' . $validated['artist_name'] . '%');
        }

        if (isset($validated['album_name'])) {
            $query->where('album_name', 'like', '%' . $validated['album_name'] . '%');
        }

        if (isset($validated['track_name'])) {
            $query->where('track_name', 'like', '%' . $validated['track_name'] . '%');
        }

        if (isset($validated['year'])) {
            $query->where('year', $validated['year']);
        }

        if (isset($validated['language'])) {
            $query->where('language', $validated['language']);
        }

        $tracks = $query->get();

        if ($tracks->isEmpty()) {
            return response()->json(['message' => 'Track tidak ditemukan'], 404);
        }

        return response()->json($tracks);
    }
}
