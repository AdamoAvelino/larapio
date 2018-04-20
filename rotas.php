<?php

$router = new Http\Router();

$router->get('produtos/listar/{tamanho}/{valor}/{tipo}', 'ProdutosController.listar');
$router->post('produtos/criar/', 'ProdutosController.create');
$router->get('/', 'PrincipalController.index');
