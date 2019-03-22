<?php

namespace Larapio\Http;

use Larapio\Http\MontarRota;
use Larapio\Http\Request;

/**
 * Classe responsável por montar e verificar se as requisições confere com algum rota
 * Então disponibiliza o nome do controlador, método e os parâmetros
 */
class Router
{

    /**
     * -------------------------------------------------------------------------
     * @var array vetor de objetos MontaRota com todas rotas configuradas
     */
    private $listaRotas;

    /**
     * -------------------------------------------------------------------------
     * @var Http\Request Propriedade que carrega o objeto Request
     */
    public $request;

    /* ---------------------------------------------------------------------
     * @var array Contém o obejeto MontaRota da requisição atual
     *
     */
    private $rota = false;

    /**
     * -------------------------------------------------------------------------
     * @var Array carrega um string concatenada especificando o metodo e
     * o controller a ser executado que servirá como chave do array
     * da propriedade rotas, para recuperar a rota correta vinda do request
     */
    private $chaveRotas;

    /**
     * -------------------------------------------------------------------------
     * @const VERBO constante que serve para verificar o metodo magico invocado
     * corresponde as verbo HTTP utilizados nesse framework
     */
    const VERBO = ['get', 'post'];

    /**
     * ---------------------------------------------------------------------<br>
     * Carrega os recursos necessários para a classe
     * @param Http\Request $request  Alimenta a propriedade request com
     * uma injeção de dependencia do objeto request.
     * @return void
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        $this->rota = $this->matchRouter();
    }

    /**
     * ---------------------------------------------------------------------<br>
     * [Metodo Monta todas as possiveis rotas através do objeto MontaRota]
     * @param String $nome nome do metodo disparado, que servirá para decidir
     * qual verbo do HTTP será usado na requisição
     * <b>Opções</b>:
     * <ul>
     * <li>GET</li>
     * <li>POST</li>
     * </ul>
     *
     * @param Array $argumentos todos os argumentos que estão na chamda
     * do metodo magico
     */
    public function __call($nome, $argumentos)
    {
        if (!in_array($nome, self::VERBO)) {
            $erro = new \App\Error\Error();
            $erro->show(new \Exception('Houve Algum erro na configuração das rotas'));
            exit();
        }


        $montarRota = new MontarRota(strtoupper($nome));

        $montarRota->setParametros($argumentos[0]);

        $montarRota->setControllerMetodo($argumentos[1]);

        $this->listaRotas[$montarRota->getNomeRotas()] = $montarRota;
    }

    /**
     * ------------------------------------------------------------------------------------------
     * [Verifica se existe alguma rota configurada para a requisição, caso exista
     * retorna a rota solicitada com seus parametros para montagem do objeto
     * MontaRota na propriedade rota]
     * @return array
     */
    private function matchRouter()
    {

        $this->chaveRotas = $this->request->getUrl() ?: '/';

        if ($this->chaveRotas and isset($this->listaRotas[$this->chaveRotas])) {

            $rota = $this->hasParametros($this->listaRotas[$this->chaveRotas]);

            if ($rota) {
                return $rota;
            }
        }
        $error = new \App\Error\Error();
        $error->show('Antenção, Erro na configuração de rotas');
    }

    /**
     * -----------------------------------------------------------------------------------------
     * Metodo responsável por iniciar a propriedade <i>rota</i> com um dos objetos
     * MotaRota listados na propriedade <i>listaRota</i>
     *
     * @param MontarRota $rota - Objeto rota definido de acordo com request
     * @return boolean|MontarRota - retorna a rota de acordo com o verbo
     * configurado e o verbo solicitado na requisição e se também está correto
     * a quantidade de parametros
     */
    private function hasParametros(MontarRota $rota)
    {

        if ($rota->getVerbo() == 'GET') {
            $this->request->setQuery($rota->getParametros());
        }

        if ($rota->getVerbo() == 'POST') {
            $this->request->setRequest();
        }

        if ($this->matchParans($rota, $rota->getVerbo())) {

            return $rota;
        }
    }

    /**
     * ----------------------------------------------------------------------<br>
     * [Método que valida a quantidade de parametros vindas por uma das variaveis
     * globais GET ou POST com a quantidade dos parametros configuradas na rota]
     * @param  Http\MontarRota  $rota [Objeto monta rota que carrega todas as
     * configurações da rota em questão]
     * @return String [Verbo configurado na rota]
     */
    private function matchParans(MontarRota $rota, $verbo)
    {
        if ($verbo == $this->request->method) {
            if (count($this->request->getQuery()) == count($rota->getParametros())) {
                return $rota;
            }
        }

        if($verbo == $this->request->method){
            if(count($this->request->variaveis_url) == 0 and count($rota->getParametros()) == 0) {
                return $rota;
            }
        }

        $erro = new \App\Error\Error();
        $erro->show('Configuração da rota incorreta: Verifique o VERBO ou os {PARAMETROS}');
    }

    /**
     * ------------------------------------------------------------------------------------------------
     * Retorna o nome do controlador cadastrado no arquivo de rotas
     * @return string
     */
    public function getController()
    {
        return $this->rota->getController();
    }

    /**
     * --------------------------------------------------------------------------------------------------
     * Retorna o nome do método cadastrado no arquivo de rotas
     * @return string
     */
    public function getMethod()
    {
        return $this->rota->getMetodo();
    }

    /**
     * -------------------------------------------------------------------------------------------------
     * Retorna um array com os parâmetros passados na URL
     * @return array
     */
    public function getParams()
    {
        return $this->request->getParametros();
    }
}
