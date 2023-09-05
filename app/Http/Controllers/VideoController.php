<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class VideoController extends Controller
{
    public function index()
    {
        return Inertia::render('Videos/All',[
            'categorias' => Categoria::orderBy('id','desc')->get(),
            'videos' => Video::orderBy('created_at','desc')->get(),
        ]);
    }

    public function store(Request $request)
    {
        //dd($request->all());

        $request->validate([
            'titulo'=>[
                'required',
                'max:140',
            ],
            'url'=>[
                'required',
            ],
            'categoria_id'=>[
                'required'
            ]
        ]);

        Video::create($request->all());

        return redirect()->route('videos.index');
    }

    public function update(Request $request,Video $video)
    {

        $request->validate([
            'titulo'=>[
                'required',
                'max:140',
            ],
            'url'=>[
                'required',
            ],
            'categoria_id'=>[
                'required'
            ]
        ]);

        $video->update($request->all());

        return redirect()->route('videos.index');
    }
}
