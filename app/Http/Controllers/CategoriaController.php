<?php

namespace App\Http\Controllers;

use App\helpers;
use App\Models\Categoria;
use App\Models\Video;
use Carbon\Carbon;
use Cleeng_Api;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use PhpParser\Builder;


class CategoriaController extends Controller
{
    public function index(): Response
    {

        return Inertia::render('Categorias/All',[
           'categorias' => Categoria::orderBy('id','desc')->get(),
        ]);
    }

    /*
     * @property integer id
     * */

    public function webCategorias(Request $request,Categoria $categoria, Video $video, string $fuente =''): Response|RedirectResponse
    {
        $offerId='S677792386_AR'; //config('offers.offer_id_1mes')
        $publisherId='595514937'; //config('offers.publisher')
        $email= $request->email; //'equibajo@vivaldi.net';
        $pass=$request->pass; 'emb29456'

        //dd(Cookie::forget());
        //dd(Cookie::get());
        //dd(Cookie::make('prueba', 'Add Cookie from cnn.com', 30));

        //dd(Cookie::get('prueba'));
//dd(Cookie::forget('accessGranted'));

        if(!Cookie::get('accessGranted')) {
            if(count($request->all())!=0)
            {
                $client = new Client();

                try {
                    $response = $client->request('POST', 'https://mediastoreapi.cleeng.com/auths', [
                        'body' => '{"offerId":"'.$offerId.'","publisherId":"'.$publisherId.'","email":"'.$email.'","password":"'.$pass.'"}',
                        'headers' => [
                            'accept' => 'application/json',
                            'content-type' => 'application/json',
                        ],
                    ]);
                } catch (ClientException $e) {
                    $response=$e->getResponse();
                }
            } else {
                $response=null;
            }


            if (is_null($response) || $response->getStatusCode()!=200) {
                $accessGranted=false;
            } else {
                $accessGranted=true;
                Cookie::queue(Cookie::make('accessGranted',true,120));
            }
        } else {
            $accessGranted=true;
        }

        //$infoSubscripcion=helpers::datosCustomerySubs();



        $cate=null;

        if(is_null($categoria->id) || is_null($video->id))
        {
            $canalesLives=helpers::getMeCanalLive($fuente);

            $canal=$canalesLives->canal;
            $url=$canalesLives->url;

        } else {

            $cate=$categoria->nombre;
            $canal=$video->titulo;
            $url=config('configuracion.serverVideo').$video->url.config('configuracion.m3u8Final');

            if($categoria->subscribe)
            {

                //if (is_null($infoSubscripcion['datosSubs']) || ! $infoSubscripcion['datosSubs']->accessGranted ) {
                if (! $accessGranted ) {
                    return redirect()->route('app.suscribirse');
                }
            }
        }

        $datos = collect([
            'cate' => $cate,
            'canal' => $canal,
            'url' => $url,
//Todo Ver de conseguir los datos a continuación desde cleeng
//            'customer_id'=>(is_null($infoSubscripcion['customer']))?null:$infoSubscripcion['customer']->id,
//            'customer_email'=>(is_null($infoSubscripcion['customer']))?null:$infoSubscripcion['customer']->email,
//            'customer_displayName'=>(is_null($infoSubscripcion['customer']))?null:$infoSubscripcion['customer']->displayName,
//            'accessGranted' => (is_null($infoSubscripcion['datosSubs']))?null:$infoSubscripcion['datosSubs']->accessGranted,
            'accessGranted'=> $accessGranted
        ]);

//dd($datos);

        return Inertia::render('Welcome',[
            'fuente' => $datos,
            'serverImage'=>config('configuracion.cdnImages')
        ]);
    }


    public function store(Request $request): RedirectResponse
    {

        //dd($request->all());

        $request->validate([
            'nombre'=>[
                'required',
                'max:140',
                Rule::unique(Categoria::class)
            ],
            'cod_ftp'=>[
                'required',
                Rule::unique(Categoria::class)
            ]
        ]);

        Categoria::create($request->all());

        return redirect()->route('categorias.index');

    }

    public function update(Request $request,Categoria $categoria)
    {

        $request->validate([
            'nombre'=>[
                'required',
                'max:140',
                Rule::unique(Categoria::class)->ignore($categoria->id)
            ],
            'cod_ftp'=>[
                'required',
                Rule::unique(Categoria::class)->ignore($categoria->id)
            ]
        ]);

        $categoria->update($request->all());

        return redirect()->route('categorias.index');
    }

    public function getMeCategoriasYsusVideos(): mixed
    {
        $categoriasPrueba=Cache::remember('get-me-categoria-y-videos'.\request('page',1),60*60,function () {
            return Categoria::with([
                'videos'=>function($query) {
                    $query->where('status', '=', Video::ESTADO_ACTIVO);
                }
            ])->where('status', '=', Categoria::ESTADO_ACTIVO)->orderBy('updated_at', 'desc')->paginate(10);
        });


        return $categoriasPrueba;
    }
}
