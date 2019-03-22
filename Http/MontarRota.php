<?php

namespace Larapio\Http;

class MontarRota
{
    /**
     *
     * @var String Nome da Rota que servirá como chave do array que será
     * armazenado na propriedade <i>listaRotas do Objeto Router</i>
     */
    private $nomeRota;
    /**
     * ---------------------------------------------------------------------<br>
     * @var String [Verbo HTTP configurado pelo metodo mágico <i>__call do
     * Objeto Router</i>]
     */
    private $verbo;

    /**
     * ---------------------------------------------------------------------<br>
     * @var Array [Nome dos parametros GET ou POST configurados no <i>Objeto
     * Router</i>]
     */
    private $parametros = [];

    /**
     * ---------------------------------------------------------------------<br>
     * @var String [Nome do controller configurado pelo <i>Objeto Router</i>]
     */
    private $controller;

    /**
     * ---------------------------------------------------------------------<br>
     * @var String [Nome do controller metodo pelo <i>Objeto Router</i>]
     */
    private $metodo;

    /**
     * ---------------------------------------------------------------------<br>
     * @param String $verbo Nome do verbo que deverá ser utilizado na requisição
     */
    public function __construct($verbo)
    {
        $this->verbo = $verbo;
    }

    /**
     * ----------------------------------------------------------------------<br>
     *  [Metodo que define qual controller e qual metodo será executado]
     * Recebe o segundo parametro enviado pelo metodo router do Objeto Router utiliza
     * utiliza o explode para separar strings que serão o Controllers e o Médodo de Ação  
     * @param String $argumento Duas paravras delimitadas por ".(ponto)".
     * 
     */
    public function setControllerMetodo($argumento)
    {
        if (is_callable($argumento)) {
            $this->controller = $argumento;
            return true;
        }
        $ControllerMetodo = explode('.', $argumento);
        $this->controller = $ControllerMetodo[0];
        $this->metodo = $ControllerMetodo[1];
    }

    /**
     * ---------------------------------------------------------------------<br>
     * Metodo Responsável pela configuração dos parametros e o nome da rota.
     * Parametros são strings envolvidas por "{}(chaves)" para serem utilizadas em
     * metodos de ação de um determinado controller. As strings que não são envolvidas
     * por "{}(chaves)", serão configuradas como nome de rotas em um array que determinará
     * toda a ação a ser executada.
     * @param string $argumento string com palavras delimitadas por "/(barras), que são definidas
     * como nome de rota ou parametros que servirão de informação para execução das ações.
     * @example Primeiro parametro do metodo router da classe Roter invocado no arquivo rotas.php:
     * produto/listar/{codigo}/{fornecedor}
     */
    public function setParametros($argumento)
    {
        $parametros = array_filter(explode('/', $argumento));

        foreach ($parametros as $param) {
            if (preg_match('#{#', $param)) {
                $this->parametros[] = preg_replace('#{|}#', '', $param);
                continue;
            }
            $nomeRota[] = $param;
        }

        if(isset($nomeRota) or $argumento){

            $this->nomeRota = isset($nomeRota) ? implode('.', $nomeRota) : $argumento;
            return true;
        }

        $error = new \App\Error\Error();
        $error->show('Nome da rota não foi definido');
        
    }

    /**
     * -------------------------------------------------------------------------
     * [Recuperação o valor da propriedade privada rota]
     * @return String [O nome da rota]
     *
     */
    public function getNomeRotas()
    {
        return $this->nomeRota;
    }

    /**
     * --------------------------------------------------------------------------
     * [Recuperação o valor da propriedade privada parametros]
     * @return array
     */
    public function getParametros()
    {
        return $this->parametros;
    }

    /**
     * ---------------------------------------------------------------------<br>
     * [Recuperação o valor da propriedade privada controller]
     * @return String
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * ---------------------------------------------------------------------<br>
     * [Recuperação o valor da propriedade privada metodo]
     * @return String
     */
    public function getMetodo()
    {
        return $this->metodo;
    }

    /**
     * ---------------------------------------------------------------------<br>
     * [Recuperação o valor da propriedade privada verbo]
     * @return String
     */
    public function getVerbo()
    {
        return $this->verbo;
    }
}
