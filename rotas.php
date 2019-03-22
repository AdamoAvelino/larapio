<?php

use Larapio\Http\Response;

$router->get('/', function(){
  Response::view('home');
});
