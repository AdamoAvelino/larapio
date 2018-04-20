<?php

namespace Http;

/**
 * [classe responsável por pegar a URL e separar em partes, definindo suas
 * respectiva utilidade]
 */
class Request {

    /**
     * ---------------------------------------------------------------------<br>
     * @var String [Nome da entidade e qual ação será executada]
     */
    private $url;

    /**
     * ---------------------------------------------------------------------<br>
     * @var array [Parametros de uma url query em caso de verbo GET verificada, 
     * com os nomes dos parametros definidos na configuração da rota]
     */
    private $query = [];

    /**
     * ---------------------------------------------------------------------<br>
     * @var array [Parametros de um formulario com method POST]
     */
    private $request = [];

    /**
     * ---------------------------------------------------------------------<br>
     * @var Array [Lista dos valores recuperados da url apartir da segunda 
     * posição, represetam valores dos parametros enviados pela requisição,apenas
     * quando o verbo <i>HTTP = GET</i> ] 
     */
    private $variaveis_url;

    /**
     * ---------------------------------------------------------------------<br>
     * Pega a URL digitada. Os dois primeiros parâmetros da URL monta o nome da 
     * entidade que deverá ser acessada e a ação que deverá ser executada, o 
     * restante são variáveis que alimentam os parametros vindas url query.
     */
    public function __construct() {
        if (isset($_GET["url"])) {
            $variaveis_url = explode('/', $_GET["url"]);

            if (count($variaveis_url) > 1) {
                $this->url = sprintf("%s.%s", array_shift($variaveis_url), array_shift($variaveis_url));
            }

            $this->variaveis_url = array_filter($variaveis_url);
        }
    }

    /**
     * ---------------------------------------------------------------------<br>
     * [Alimenta a propriedade query, que contem um conjunto de dados enviados 
     * via requisição pelo verbo GET do protocolo HTTP]
     * 
     * @param array $parametroRota [uma lista de string que servirá para 
     * determinar o nome das variavies vindas da requisição]
     */
    public function setGet(array $parametroRota) {
        for ($i = 0; $i < count($this->variaveis_url); $i++) {

            $this->query[$parametroRota[$i]] = $this->variaveis_url[$i];
        }
    }

    /**
     * -------------------------------------------------------------------- <br>
     * [Alimenta a propriedade request, que contem um conjunto de dados enviados 
     * via requisição pelo verbo POST do protocolo HTTP]
     * 
     * @param array $parametros [uma lista de string que servirá para 
     * determinar o nome das variavies vindas da requisição]
     */
    public function setRequest(array $parametros) {
        if (isset($_POST)) {

            foreach ($parametros as $key => $value) {

                if (!isset($_POST[$value])) {
                    echo new \Exception('Variável ' . $value . ' não identificada, verifique seu formulário');
                }

                $this->request[$value] = $_POST[$value];
            }
        }
    }

    /**
     * ---------------------------------------------------------------------<br>
     * [Retorna as duas primeiras partes da URL no formato param1.param2]
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * ---------------------------------------------------------------------<br>
     * [Retorna os parametros da url query, validada  com os parametros 
     * configurados na chamada da rota]
     * @return array
     */
    public function getQuery() {
        return $this->query;
    }

    /**
     * [Retorna os parametros os parametros submetidos pelo formulario que tenha 
     * sido configurado com o method POST, validada  com os parametros  
     * configurados na chamada da rota]
     * @return type
     */
    public function getRequest() {
        return $this->request;
    }

}
