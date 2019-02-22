<?php

namespace Http;

/**
 * [classe responsável por pegar a URL e separar em partes, definindo suas
 * respectiva utilidade]
 */
class Request
{

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

    /**
     * ---------------------------------------------------------------------
     * Undocumented variable
     *
     * @var [type]
     */
    private $arquivo;

    /**
     * ---------------------------------------------------------------------
     * Undocumented variable
     *
     * @var [type]
     */
    private $servidor;

    /**
     * ---------------------------------------------------------------------
     * Undocumented function
     */
    public function __construct()
    {
        $this->servidor = $_SERVER;
        if (isset($_GET["url"])) {
            $variaveis_url = explode('/', $_GET["url"]);

            count($_FILES) ? $this->arquivo = $_FILES : $this->arquivo = false;

            $this->url = sprintf("%s.%s", array_shift($variaveis_url), array_shift($variaveis_url));
            $this->trataUrl();

            $this->variaveis_url = array_filter($variaveis_url, function($valor) {
                return ($valor === '0' or $valor);
            });
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
    public function setGet(array $parametroRota)
    {
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
    public function setRequest()
    {
        if (isset($_POST)) {
            foreach ($_POST as $key => $value) {

                $this->request[$key] = $value;
            }
        }

    }

    /**
     * ---------------------------------------------------------------------<br>
     * [Retorna as duas primeiras partes da URL no formato param1.param2]
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * ---------------------------------------------------------------------<br>
     * 
     */
    private function trataUrl()
    {
        $sem_metodo = array_filter(explode('.', $this->url));
        if (count($sem_metodo) == 1) {
            $this->url = array_shift($sem_metodo);
        }

    }

    /**
     * ---------------------------------------------------------------------<br>
     * [Retorna os parametros da url query, validada  com os parametros
     * configurados na chamada da rota]
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * ---------------------------------------------------------------------<br> 
     * [Retorna os parametros os parametros submetidos pelo formulario que tenha
     * sido configurado com o method POST, validada  com os parametros
     * configurados na chamada da rota]
     * @return type
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * ---------------------------------------------------------------------------
     * Undocumented function
     *
     * @return void
     */
    public function guardaArquivo($nomelInput)
    {
        $diretorio = $this->servidor['DOCUMENT_ROOT'] . '/recursos/imagem';

        if (is_array($this->arquivo[$nomelInput]['name'])) {
            foreach ($this->arquivo[$nomelInput]['name'] as $index => $arquivo) {

                $uriFile = $diretorio . '/' . basename($this->arquivo[$nomelInput]['name'][$index]);
                $arquivoTemp = $this->arquivo[$nomelInput]['tmp_name'][$index];

                if (move_uploaded_file($arquivoTemp, $uriFile)) {
                    $urls[] = basename($this->arquivo[$nomelInput]['name'][$index]);
                } else {
                    $urls[] = basename($this->arquivo[$nomelInput]['name'][$index]) . " Falhou";
                }
            }
            return $urls;
        }

        $uriFile = $diretorio . '/' . basename($this->arquivo[$nomelInput]['name']);
        $arquivoTemp = $this->arquivo[$nomelInput]['tmp_name'];

        if (move_uploaded_file($arquivoTemp, $uriFile)) {
            return basename($this->arquivo[$nomelInput]['name']);
        }

        return false;

    }

    /**
     * ---------------------------------------------------------------------<br> 
     * @param type $nomelInput
     * @return type
     */
    public function existeArquivo($nomelInput)
    {
        return $this->arquivo[$nomelInput]['name'] ? $this->arquivo[$nomelInput] : false;

    }

    /**
     * ---------------------------------------------------------------------<br> 
     * @param type $nomeInput
     * @return type
     */
    public function getArquivo($nomeInput)
    {
        return $this->arquivo[$nomeInput];

    }

}
