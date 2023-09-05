<?php

namespace App;

use App\Models\Categoria;
use App\Models\Video;
use Carbon\Carbon;
use Cleeng_Api;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class helpers
{

    public static function categoriasYsusVideos(): mixed
    {
        $esAdministrable = config('configuracion.administrable');



        if (is_null(Cache::get('categorias'))) {

            $categoriasYsusVideos=Categoria::with([
                'videos'=>function($query) {
                    $query->where('status', '=', Video::ESTADO_ACTIVO);
                }
            ])->where('status', '=', Categoria::ESTADO_ACTIVO)->orderBy('updated_at', 'desc')->simplePaginate(15);



                $categoriasYsusVideos = collect($categoriasYsusVideos);
                Cache::forever('categorias', $categoriasYsusVideos);
        } else {
                $categoriasYsusVideos = Cache::get('categorias');
            }

dd($categoriasYsusVideos->paginate(15));
        return $categoriasYsusVideos;
    }

    public static function datosCustomerySubs(): Collection
    {
        $offer_id_1mes = config('offers.offer_id_1mes');
        $offer_id_3mes = config('offers.offer_id_3mes');

        $cleengApi = new Cleeng_Api();
        $customer = null;

        if ($cleengApi->getCustomerToken() != '') {
            $customer = $cleengApi->getCustomer();
        }

        if ($customer != null) {

            if ($cleengApi->isAccessGranted($offer_id_1mes)) {
                $datos = collect([
                    'customer' => $customer,
                    'datosSubs' => $cleengApi->getAccessStatus($offer_id_1mes)
                ]);
            } else {

                if ($cleengApi->isAccessGranted($offer_id_3mes)) {
                    $datos = collect([
                        'customer' => $customer,
                        'datosSubs' => $cleengApi->getAccessStatus($offer_id_3mes)
                    ]);
                } else {
                    //Logueado pero no esta subscripto
                    $datos = collect([
                        'customer' => $customer,
                        'datosSubs' => null
                    ]);
                }
            }
        } else {

            $datos = collect([
                'customer' => null,
                'datosSubs' => null
            ]);
        }

        return $datos;
    }

    public static function getMeCanalLive(string $fuente): object
    {

        switch ($fuente) {
            case 'miami-tv-latino':
                //MiamiTV Latino
                $canal='MiamiTV Latino';
                $url='https://59ec5453559f0.streamlock.net/Latino/smil:WEB/playlist.m3u8';
                break;
            case 'miami-tv':
                //MiamiTV
                $canal='MiamiTV';
                $url='https://59ec5453559f0.streamlock.net/miamitv/smil:WEB/playlist.m3u8';
                break;
            case 'jenny-live':
                //MiamiTV
                $canal='Jenny Live';
                $url='https://59ec5453559f0.streamlock.net/JennyLive/smil:WEB/playlist.m3u8';
                break;
            case 'sun-beach-tv':
                //SunBeachTV
                $canal='Sun Beach TV';
                $url='https://59ec5453559f0.streamlock.net/blog/2323/playlist.m3u8';
                break;
            case 'sun-beach-tv-247':
                //SunBeachTVBlog
                $canal='Sun Beach TV 24/7';
                $url='https://59ec5453559f0.streamlock.net/jennyforyou/smil:WEB/playlist.m3u8';
                break;
            default:
                //MiamiTV - Latino
                $canal='MiamiTV Latino';
                $url='https://59ec5453559f0.streamlock.net/Latino/smil:WEB/playlist.m3u8';
                break;
        }

        return (object)['canal'=>$canal,'url'=>$url];
    }
}
