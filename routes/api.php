<?php

use App\Services\BotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//
Route::post('/bot', function (Request $request) {

    $bot = new BotService();
    $answer = $bot->generateResponse($request->prompt);

    return $answer;
});
