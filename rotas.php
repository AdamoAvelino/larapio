<?php

use Http\Response;

$router->get('/', function(){
  Response::view('home');
});
