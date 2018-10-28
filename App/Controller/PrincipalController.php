<?php

namespace App\Controller;

use Http\Response;

/**
 * Arquivo de controlador
 */
class PrincipalController {

    /**
     * Controlador indicado na propriedade default do arquivo de rotas
     * @return void
     */
    public function index() {
        Response::view('home');
    }

}
