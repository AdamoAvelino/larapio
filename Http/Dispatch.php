<?php

namespace Larapio\Http;

use Larapio\Http\Router;
use Larapio\App\Error\Error;
use Larapio\Http\Session;
use Larapio\Http\Response;
use Larapio\App\Controller\LoginController;

/**
 * Classe (Padrão Factory): Responsável por carregar o controlador, método com
 * seus respectivos atributos configurados no Objeto Router
 */
class Dispatch
{

    /**
     * ---------------------------------------------------------------------<br>
     * @var Http\Router [Objeto Router com todas configurações de rota]
     */
    private $router;

    /**
     * ---------------------------------------------------------------------<br>
     * @var string [Base do name space onde fica para classes Controllers]
     */
    private $controllerNamespace = '\App\Controller\\';

    /**
     * ---------------------------------------------------------------------<br>
     * [Metodo Construtor: Guarda a instancia de route no atributo local no
     * momento em que é instanciado]
     * @param Http\Router $router
     */
    const AUTH = false;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * ---------------------------------------------------------------------<br>
     * [Responsável por iniciar toda a aplicação, baseado na configuração das
     * rotas no <i>Objeto Router </i> e na requisição tratado pelo <i>Objeto
     * Request </i>]
     */
    public function run()
    {
        if(is_callable($this->router->getController())){
            $callback = $this->router->getController();
            $callback();
            return true;
        }


        $controller = $this->controllerNamespace . $this->router->getController();
        $method = $this->router->getMethod();

        if (self::AUTH and $method !== 'logar' and $controller !== '\App\Controller\PrincipalController') {
            $this->validaSessao();
        }
        
        if (class_exists($controller)) {
            
            $controlador = new $controller($this->router->request);
            if (method_exists($controlador, $method)) {
                $controlador->$method();
            } else {
                throw new \Exception("O método $method passado na rota não existe");
            }
        } else {
            throw new \Exception("O A classe  $controller passado na rota não existe");
        }

    }

    public function validaSessao()
    {
        $login = new LoginController;

        if (!$login->logado()) {

            Response::view('login', false);
            die();
        }
    }
}
