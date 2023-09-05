<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PreparandoSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'miamitv:preparando-seeder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepara el seeder para crear luego poder sembrar la base con las categorias y videos de MiamiTv';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (is_null(Cache::get('categoriasNA'))) {
            $directorios = Storage::disk('ftp')->allDirectories();

            $archivos = array();


            foreach ($directorios as $key => $directorio) {
                if ($directorio != 'Videos Thumbnails') {
                    $archivos[$key]['nombre'] = $directorio;
                    $archivos[$key]['uri'] = Str::slug($directorio);
                    $archivos[$key]['subscribe'] = 1;
                    $archivos[$key]['status'] = 1;
                    $archivos[$key]['updated_at'] = Carbon::createFromTimestamp(Storage::disk('ftp')->lastModified($directorio))->toDateTimeString();
                    $videos = Storage::disk('ftp')->allFiles($directorio);

                    $videosCategoria=array();
                    foreach ($videos as $indice=>$video)
                    {
                        $vid=explode("/",$video);

                        $videosCategoria[$indice]['titulo']=substr($vid[1],0,-4);
                        $videosCategoria[$indice]['uri']=Str::slug(substr($vid[1],0,-4));
                        $videosCategoria[$indice]['url']=$video;
                    }

                    $archivos[$key]['videos'] = (object)$videosCategoria;
                }
            }
            Cache::forever('categoriasNA', collect($archivos));
            $categoriasYsusVideos=collect($archivos);

        } else {
            $categoriasYsusVideos = Cache::get('categoriasNA');
        }

        dd($categoriasYsusVideos);
    }
}
