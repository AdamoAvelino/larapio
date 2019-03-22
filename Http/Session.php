<?php

namespace Larapio\Http;

/**
 * Monta uma sessão com variaveis que for necessária e com configuração de
 * tempo de expiração.
 *
 */

class Session
{
    /**
     * Inicia a Sessão com um nome e a função session_start
     * @param string $name nome da sessão
     */
    public function __construct($iniciar = false, $name = "NEUTRALIZE")
    {
        if ($iniciar) {
            session_name($name);
            session_start();
        }
    }

    /**
     * ----------------------------------------------------------------
     * Inclui uma variavel na sessão
     * @param string $chave um nome para chave da sessão criada
     * @param string|Int $valor valor para a sessão criada
     */
    public function setSession($chave, $valor)
    {
        $_SESSION[$chave] = $valor;
    }
    /**
     * Verifica a existencia de uma chave registrada na sessão
     * @param  string  $field nome da chave que será verificada
     * @return boolean       Verdadeiro | Falso
     */
    public function has($field)
    {
        return isset($_SESSION[$field]);
    }

    /**
     * -------------------------------------------------------------------------
     * Retorna o valor de uma sessão pelo nome da chave
     * @param  string $field nome da chave da session
     * @return string|numerico|array  Retorna o valor da sessão pesquisada
     */
    public function getSession($field)
    {
        return $this->has($field) ?  $_SESSION[$field] : null;
    }

    /**
     * ------------------------------------------------------------------
     * Configura o tem de duração de uma sessão quando existe a sessão
     * @param [type] $strtime String valida para função strtotime.
     */
    public function setTimeSesssion($strtime)
    {
        $this->setSession('duracao', $strtime);
        $this->setSession('expira', strtotime("+$strtime"));
    }

    /**
     * --------------------------------------------------------------------------
     * Registra um tempo de expeiração para sessão, quando a sessão iniciada
     * @param  string $duration String valida para função strtotime.
     */
    public function registra($duration = '30 Minutes')
    {
        $this->setTimeSesssion($duration);
    }

    /**
     * --------------------------------------------------------------------------
     * Verifica se a sessão foi expirada
     * @return bool Verdadeira|Falso
     */
    public function expirada()
    {
        if (time() > $this->getSession('expira')) {
            return true;
        }
        return false;
    }

    /**
     * ---------------------------------------------------------------------------
     * Encerra a sessão vigênte
     * @return [type] [description]
     */
    public function destroy()
    {
        $_SESSION = [];
        session_destroy();
    }

    /**
     * ----------------------------------------------------------------------------
     * Atualiza o tem de expiração caso a sessão não esteja expirada
     * @return bool Verdadeiro|Falso
     */
    public function valida()
    {
        if ($this->expirada()) {
            $this->destroy();
            return false;
        }

        $this->renova();
        return true;
    }

    /**
     * --------------------------------------------------------------------------------
     * Renova o tempo de sessão
     */
    public function renova()
    {
        $this->setTimeSesssion($this->getSession('duracao'));
    }

    /**
     * -------------------------------------------------------------------------------
     * Undocumented function
     *
     * @param [type] $chave
     * @param [type] $valor
     * @return void
     */
    public function setFlash($chave, $valor)
    {
        $_SESSION['flash'][$chave] = $valor;

    }
    /**
     * -----------------------------------------------------------------------------------
     * Undocumented function
     *
     * @return void
     */
    public function getFlash()
    {
        $flash = $this->getSession('flash');
        $this->deletaSession('flash');
        return $flash;
    }
     
    /**
     * --------------------------------------------------------------------------------------
     * Undocumented function
     *
     * @param [type] $chave
     * @return void
     */
    private function deletaSession($chave)
    {
        unset($_SESSION[$chave]);

    }
}
