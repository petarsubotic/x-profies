<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/shares/{id}', function () {

    $user = new UserController;
    // $demoData = $user->getDemoData();
    // dd($demoData);

    $userID = $user->getUserID(request()->id);
    dd($userID);
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://twitter293.p.rapidapi.com/user/".$userId."/tweets?count=10",
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
        $responseJson = json_decode($response);
        foreach ($responseJson->user->result->timeline_v2->timeline->instructions as $instruction) {
            if ($instruction->type == 'TimelineAddEntries') {
                $entries = $instruction->entries;
            }
        }
        $tweets = [];
        foreach ($entries as $entry) {
            if(isset($entry->content->itemContent)){
                array_push($tweets, $entry->content->itemContent->tweet_results->result->rest_id);
            }
        }
    }

    $profiles = [];
    foreach ($tweets as $tweet){
        $url = "https://twitter293.p.rapidapi.com/tweet/".$tweet."/retweets?count=100";
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
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
            $json = json_decode($response);
            if(isset($json->entries[0])){
                foreach ($json->entries[0]->entries as $entry) {
                    if(isset($entry->content->itemContent->user_results->result->legacy->screen_name)){
                        $user = new stdClass();
                        $user->screen_name = $entry->content->itemContent->user_results->result->legacy->screen_name;
                        $user->name = $entry->content->itemContent->user_results->result->legacy->name;
                        $user->image = $entry->content->itemContent->user_results->result->legacy->profile_image_url_https;

                        // if user already exists in profiles array, increment count
                        if(count($profiles) == 0){
                            $user->count = 1;
                        }
                        foreach ($profiles as $profile){
                            if($profile->screen_name == $user->screen_name){
                                $profile->count++;
                            } else {
                                $user->count = 1;
                            }
                        }
                        array_push($profiles, $user);
                    }
                }
            }
        }
    }

    // Order profiles by count
    usort($profiles, function($a, $b) {
        return $b->count <=> $a->count;
    });

    return \Response::json($profiles, 200);
});