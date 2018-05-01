<?php

namespace App\Controller;

use http\Request;

/**
 * arquivo de classe do controlador Produtos
 */
class ProdutosController
{

    /**
     * [$request description]
     * @var Request
     */
    private $request;

    /**
     * ========================================================================
     * [__construct description]
     * @param Request $request [description]
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * -------------------------------------------------------------------------
     * Método chamado na primeira ação do arquivo rotas.php
     * @return type
     */
    public function listar()
    {
        var_dump($this->request->getQuery());
        echo "listar";
    }

    /**
     * -------------------------------------------------------------------------
     * Medoto chamado na segunda ação do arquivo rotas.php
     * @return type
     */
    public function create()
    {
        var_dump($this->request->getRequest());
        echo "create";
    }
}
