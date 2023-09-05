<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * @property  mixed $id
 * @property mixed $nombre
 * @property mixed $uri
 * @property mixed $subscribe
 *
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder create(array $attributes = [])
 * @method public Builder update(array $values)
 * @method static Builder orderBy($column,$value)
 */

class Categoria extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table='categorias';

    protected $fillable=[
        'id',
        'nombre',
        'cod_ftp',
        'uri',
        'subscribe',
        'status',
        'created_by',
        'updated_by',
        'deleted_at'
    ];

    protected $dates=['deleted_at','created_by','updated_by'];

    const ESTADO_ACTIVO=true;
    const ESTADO_INACTIVO=false;


    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public static function getMeCategorias()
    {
            $categorias = json_decode(File::get(resource_path('json/categorias.json')));

            $categoriasAInsertar=array();

            foreach ($categorias as $key=>$item)
            {
                $categoriasAInsertar[$key]['id']=$item->id;
                $categoriasAInsertar[$key]['nombre']=$item->nombre;
                $categoriasAInsertar[$key]['cod_ftp']=$item->id;
                $categoriasAInsertar[$key]['uri']=Str::slug($item->nombre);
                $categoriasAInsertar[$key]['status']=$item->status;
                $categoriasAInsertar[$key]['subscribe']=$item->subscribe;
                $categoriasAInsertar[$key]['created_at']=new \DateTime();
                $categoriasAInsertar[$key]['updated_at']=new \DateTime();
            }

            return $categoriasAInsertar;
    }


}
