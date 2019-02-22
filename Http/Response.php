<?php

namespace Http;

use Http\Session;


class Response
{

    private static $objeto = [];
    
    private static $response = null;

    Private static $unico;

    private function __construct()
    {

    }

    public static function set($response, $key, $unico = false)
    {
        if (!self::$response) {
            self::$objeto[$key] = $response;
            self::$response = new Response();
        }
        return self::$response;
    }

    public static function view($view, $corpo = true)
    {
        if(self::$objeto) {
            extract(self::$objeto);
        }

        $sessao = new Session();

        if($corpo){
            include 'view/header.php';
        }

        include 'view/'.$view.'.php';
        
        if($corpo){
            include 'view/footer.php';
        }
    }
}