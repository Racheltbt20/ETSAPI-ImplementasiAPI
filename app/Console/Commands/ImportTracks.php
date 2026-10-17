<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Track;

class ImportTracks extends Command
{
    protected $signature = 'tracks:import';
    protected $description = 'Import dan cleaning data tracks dari CSV ke database';

    public function handle()
    {
        $path = storage_path('app/private/spotify_tracks.csv');

        if (!file_exists($path)) {
            $this->error('File CSV tidak ditemukan di: ' . $path);
            return;
        }

        $file = fopen($path, 'r');
        $header = fgetcsv($file); 

        $kolomDipakai = ['track_id', 'track_name', 'artist_name', 'album_name', 'year', 'popularity', 'duration_ms', 'language'];

        $totalBaris = 0;
        $totalMasuk = 0;
        $totalSkipNull = 0;
        $totalSkipDuplikat = 0;

        while (($row = fgetcsv($file)) !== false) {
            $totalBaris++;
            $data = array_combine($header, $row);

            // Cleaning 1: ambil kolom yang dipakai (sesuai migration)
            $data = array_intersect_key($data, array_flip($kolomDipakai));

            // Cleaning 2: validasi field penting (gak boleh kosong)
            if (empty($data['track_id']) || empty($data['track_name']) || empty($data['artist_name'])) {
                $totalSkipNull++;
                continue;
            }

            // Cleaning 3: skip kalau track_id udah ada (deduplikasi)
            if (Track::where('track_id', $data['track_id'])->exists()) {
                $totalSkipDuplikat++;
                continue;
            }

            // Cleaning 4: normalisasi tipe data
            Track::create([
                'track_id'    => $data['track_id'],
                'track_name'  => $data['track_name'],
                'artist_name' => $data['artist_name'],
                'album_name'  => $data['album_name'] ?? null,
                'year'        => (int) $data['year'],
                'popularity'  => (int) $data['popularity'],
                'duration_ms' => (int) $data['duration_ms'],
                'language'    => $data['language'] ?? null,
            ]);

            $totalMasuk++;

            if ($totalBaris % 1000 == 0) {
                $this->info("Proses: $totalBaris baris...");
            }
        }

        fclose($file);

        $this->info("=== Import selesai ===");
        $this->info("Total baris dibaca: $totalBaris");
        $this->info("Berhasil masuk: $totalMasuk");
        $this->info("Skip (field kosong): $totalSkipNull");
        $this->info("Skip (duplikat): $totalSkipDuplikat");
    }
}