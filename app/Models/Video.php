<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Categoria;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * @property mixed $id
 * @property mixed $url
 * @property mixed $uri
 * @property mixed $titulo
 */
class Video extends Model
{
    use HasFactory;
    use SoftDeletes;

    const ESTADO_ACTIVO=1;
    const ESTADO_INACTIVO=0;


    protected $table='videos';

    /**
     * Fillable.
     *
     * @var array
     */
    protected $fillable=[
        'titulo',
        'uri',
        'offer_id',
        'drm',
        'precio',
        'img_thumb',
        'poster',
        'url',
        'status',
        'categoria_id',
        'freeVideoHasta',
        'cantVisualizaciones',
        'created_by',
        'updated_by'
    ];
    protected $dates=['deleted_at','created_by','updated_by'];


    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }


    public static function getMeVideos()
    {
        $videos = json_decode(File::get(resource_path('json/videos.json')));

        $videosAInsertar=array();

        foreach ($videos as $key=>$item)
        {
            $videosAInsertar[$key]['titulo']=$item->titulo;
            $videosAInsertar[$key]['uri']=Str::slug($item->titulo);
            $videosAInsertar[$key]['img_thumb']=$item->img_thumb;
            $videosAInsertar[$key]['poster']=$item->poster;
            $videosAInsertar[$key]['status']=$item->status;
            $videosAInsertar[$key]['url']=$item->url;
            $videosAInsertar[$key]['categoria_id']=$item->categoria_id;
            $videosAInsertar[$key]['created_at']=new \DateTime();
            $videosAInsertar[$key]['updated_at']=new \DateTime();
        }

        return $videosAInsertar;
    }


}
