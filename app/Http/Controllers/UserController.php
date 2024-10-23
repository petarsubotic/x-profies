<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function index(){
        return view('user');
    }

    public function getUserID($username){

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://twitter293.p.rapidapi.com/username/to/id/".$username,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "x-rapidapi-host: twitter293.p.rapidapi.com",
                "x-rapidapi-key: uwbEgmgJSEmshaJxOXbYsK4S6xoNp1mbfGEjsnYVOWK5dBjcjI"
            ],
        ]);
    
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
           return json_decode($response)->userId;
        }
        
    }
}
