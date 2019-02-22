<?php

use Http\Response;

// $router->get('/', 'PrincipalController.index');
$router->get('/', function(){
  Response::view('home');
});
