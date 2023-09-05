<?php

namespace App\Console\Commands;

use App\Models\Categoria;
use App\Models\Video;
use App\Notifications\SendNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ActualizarCategorias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'miamitv:actualizar {nueva?} {fecha?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar los videos subidos a las categorias en el día de la fecha, si hay una categoria nueva usar 1 caso contrario 0 y definir fecha. Ej: php artisan miamitv:actualizar 1 2023-08-15';

    /**
     * Execute the console command.
     */
    public function handle()
    {

//todo incluir un try catch para finalizar

        $ActualizoCategorias=$this->argument('nueva');
        $ActualizoConFecha=$this->argument('fecha');
        $categoriasActuales=Categoria::all();
        $archivos = array();


        //dd(Carbon::createFromTimestamp(Storage::disk('ftp')->lastModified('Jenny Live 2023/Jenny Live 1617 - 08152023.mp4'))->toDateTimeString());

        //1:Levanto todos las categorias y si hay nuevas

        if($ActualizoCategorias==1 && isset($ActualizoCategorias)) {

            $directorios = Storage::disk('ftp')->allDirectories();

            foreach ($directorios as $key => $directorio) {
                if ($directorio != 'Videos Thumbnails') {
                    if (in_array($directorio, $categoriasActuales->pluck('nombre')->toArray()))
                    {
                        $archivos[$key]['nombre'] = $directorio;
                        $archivos[$key]['updated_at'] = Carbon::createFromTimestamp(Storage::disk('ftp')->lastModified($directorio))->toDateTimeString();

                        //@todo ordenar archivos comparar con la fecha actual para luego tomar los videos de la fecha

                    } else {
                        //@todo insertar nueva categoria con sus videos y todo
                    }

                }
            }
        } else {
            //dd('Estoy aca 2');
            foreach ($categoriasActuales as $key => $directorio) {
                    $archivos[$key]['id'] = $directorio->id;
                    $archivos[$key]['nombre'] = $directorio->nombre;
                    $archivos[$key]['updated_at'] = Carbon::createFromTimestamp(Storage::disk('ftp')->lastModified($directorio->nombre))->toDateTimeString();
            }
        }
        //

        //2: Comparo con la fecha actual y si es = guardo la categoria para actualizar
        if($ActualizoConFecha){
            $fechaActual=Carbon::parse($ActualizoConFecha)->format('Y-m-d');
        } else {
            $fechaActual=Carbon::now()->format('Y-m-d');
        }

        //dd($fechaActual);

        $directoriosActualizados=array();


        foreach ($archivos as $key=>$arch)
        {
            $fecha2=Carbon::parse($arch['updated_at'])->format('Y-m-d');

            if($fechaActual==$fecha2)
            {
                $directoriosActualizados[$key]['id']=$arch['id'];
                $directoriosActualizados[$key]['nombre']=$arch['nombre'];
                $directoriosActualizados[$key]['updated_at']=$arch['updated_at'];
            }
        }

        //3: Obtengo los videos del día de la fecha de las categorias que se actualizaron
        $videosActualizados=array();
        foreach ($directoriosActualizados as $key=>$da)
        {
            $videosActualizados[$key]['categoria']=(object)$da;

            $todosLosVideos=Storage::disk('ftp')->allFiles($da['nombre']);

            $tlv=array();
            $prueba=array();
            foreach ($todosLosVideos as $indice=>$vid)
            {

                $fechaVideo=Carbon::createFromTimestamp(Storage::disk('ftp')->lastModified($vid))->toDateTimeString();

                if($fechaActual == Carbon::parse($fechaVideo)->format('Y-m-d'))
                {
                    $tlv[$indice]['video']=$vid;
                    $tlv[$indice]['fecha']=$fechaVideo;
                }

            }
            $videosActualizados[$key]['videos']=$tlv;
        }

        /*** TESTING
         * dd($directoriosActualizados);
         * dd($videosActualizados);
         *
         **/


        //4: Inserto los videos en videos y modifico la fecha de actualización de la categoria
        foreach ($videosActualizados as $actualizar)
        {
            Categoria::where('id',$actualizar['categoria']->id)
                ->update(['updated_at'=>$actualizar['categoria']->updated_at]);

            if (count($actualizar['videos'])>0)
            {
                foreach ($actualizar['videos'] as $vide)
                {
                    $tit=explode("/",$vide['video']);


                    $titulo=substr($tit[1],0,-4);
                    Video::create([
                        'titulo'=>$titulo,
                        'uri'=>Str::slug($titulo),
                        'url'=>$vide['video'],
                        'cantVisualizaciones'=>rand(10000,50000),
                        'categoria_id'=>$actualizar['categoria']->id,
                        'updated_at'=>$vide['fecha'],
                        'created_at'=>$vide['fecha']
                    ]);

                }
            }


        }

        //5: Envio Notificacion
        if(isset($videosActualizados)) {
            Artisan::call('config:clear');
            Artisan::call('config:cache');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            echo "Se actualizaron los videos";
            echo "Se borro el cache";
            Notification::route('slack',env('SLACK_HOOK'))->notify(new SendNotification(json_encode($videosActualizados)));
        } else {
            echo "No hay videos para actualizar";
            Notification::route('slack',env('SLACK_HOOK'))->notify(new SendNotification('No hay videos para actualizar'));
        }

    }
}
