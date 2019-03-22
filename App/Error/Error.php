<?php

namespace Larapio\App\Error;

/**
*	Classe responsável por exibir erros
*/
class Error
{
    public function __construct()
    {
        
    }
    /**
     * Mostra o erro na tela
     * @param string $message
     * @return void
     */
    public static function show($message)
    {
        die($message);
    }
}
