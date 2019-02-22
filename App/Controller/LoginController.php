<?php

namespace App\Controller;

use Http\Request;
use Http\Session;
use App\Model\Usuario;
use Http\Response;

class LoginController
{
    /**
     * --------------------------------------------------------------------
     * Undocumented variable
     *
     * @var [type]
     */
    private $sessao;
    /**
     * -------------------------------------------------------------------
     * Undocumented variable
     *
     * @var [type]
     */
    private $request;

    /**
     * ---------------------------------------------------------------------
     * Undocumented function
     *
     * @param Request $request
     */
    public function __construct(Request $request = null)
    {
        $this->sessao = new Session(true);
        
        if($request){
            $this->request = $request;
        }
        
    }
    /**
     * ----------------------------------------------------------------------
     * Undocumented function
     *
     * @return void
     */
    public function logar()
    {        
        extract($this->request->getRequest());
        $retorno = $this->verificarUsuario(['login' => $login, 'senha' => $senha]);

        if($retorno)
        {
            $this->sessao->registra();
            $this->sessao->setSession('login', $login);
            $this->sessao->setSession('senha', $senha);
            header('Location: /');
            die();
        }
        $this->sessao->setFlash('erro', 'Os dados de login estão incorretos');
        header('Location: /login');
            
    }
    /**
     * --------------------------------------------------------------------------
     * Undocumented function
     *
     * @param [type] $dados
     * @return void
     */
    public function verificarUsuario($dados)
    {
        $usuario = new Usuario;
        return $usuario->validacao($dados);
    }
    /**
     * -----------------------------------------------------------------------------------------
     * Undocumented function
     *
     * @return void
     */
    public function logado()
    {   
        // $this->sessao->destroy();
        if($this->sessao->has('login') and $this->sessao->has('senha')){
            return $this->sessao->valida();
        }
        return false; 
    }

    public function sair()
    {
        $this->sessao->destroy();
        header('Location: /login');
    }

}
