<?php

namespace Http;

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
     * [Metodo que define qual controller e qual metodo será executado]
     * @param type $argumento [argumento vindos do metodo magico __call da
     * classe Route]
     */
    public function setControllerMetodo($argumento)
    {
        $ControllerMetodo = explode('.', $argumento);
        $this->controller = $ControllerMetodo[0];
        $this->metodo = $ControllerMetodo[1];
    }

    /**
     * ---------------------------------------------------------------------<br>
     * Metodo Responsável pela configuração dos parametros e o nome da rota
     *  configurados no metodo mágico do Objeto Rota
     *
     * @param type $argumento [argumento vindos do metodo magico __call da
     * classe Route]
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

        $this->nomeRota = isset($nomeRota) ? implode('.', $nomeRota) : 'default';
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
