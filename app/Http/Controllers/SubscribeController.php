<?php

namespace App\Http\Controllers;


use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SubscribeController extends Controller
{
    public function index()
    {

        return Inertia::render('Subscribe',[
            'offer_id_1mes'=>config('offers.offer_id_1mes'),
            'offer_id_3mes'=>config('offers.offer_id_3mes'),
        ]);
    }

    public function login(Request $request)
    {


        $request->validate([
            'email'=>[
                'required',
                'email'
            ],
            'pass'=>[
                'required'
            ]
        ]);


//dd($request->all());
        $client = new Client();

        $response = $client->request('POST', 'https://mediastoreapi.cleeng.com/auths', [
            'body' => '{"offerId":"S677792386_AR","publisherId":"595514937","email":"equibajo@vivaldi.net","password":"emb29456"}',
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
        ]);

        return redirect()->route('app.logueo');
    }
}
