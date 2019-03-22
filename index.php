<?php

//Inclui o arquivo que possui a classe de autoloader
require_once __DIR__ . '/vendor/autoload.php';

use Larapio\Http\Router;
use Larapio\Http\Request;
use Larapio\Http\Dispatch;

//Instancia a classe de autoloader

//$autoloader = new Autoloader();

//Registra no php para usar esse autoloader
//$autoloader->registrar();

//Configuração de rotas 
$router = new Router();
require_once 'rotas.php';

//Cria uma instância da classe Request
$request = new Request();

//Cria uma instancia da classe router passando o request
$router->setRequest($request);

//Cria uma instancia da classe dispatch passando router
$dispatch = new Dispatch($router);


try {
    //Método responsável por carregar o recurso com base na URL
    $dispatch->run();
} catch (Exception $ex) {
    //Caso algum erro aconteça usamos essa classe para exibir.
    Larapio\App\Error\Error::show($ex->getMessage());
}
