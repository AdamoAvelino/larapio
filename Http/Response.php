<?php

namespace Larapio\Http;

use Larapio\Http\Session;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

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

    public static function view($view)
    {
        if(self::$objeto) {
            extract(self::$objeto);
        }

        $sessao = new Session();

        $template = self::template();
        
        echo $template->render($view.'.html', self::$objeto);
        
    }


    private function template()
    {
        $loader = new FilesystemLoader('view');
        return new Environment($loader);
 
    }
}