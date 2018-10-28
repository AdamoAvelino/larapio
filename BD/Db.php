<?php

namespace BD;

use \PDO;

class Db extends PDO
{
    const OPTIONS = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

    private $pdo;

    public function __construct()
    {
        extract($this->getConfigDb());

        if ($sgbd == 'mysql') {
            $dns = sprintf("%s:host=%s;port=%s;dbname=%s", $sgbd, $server, $porta, $banco);


        } elseif ($sgbd == 'sqlite') {
            $dns = sprintf("%s:%s", $sgbd, $server);
        }


        parent::__construct($dns, $usuario, $senha, self::OPTIONS);
    }

    /**
     * ---------------------------------------------------------------------<br>
     * @return array
     */
    private function getConfigDb()
    {
        $config = parse_ini_file('config.ini');
        return $config;
    }

    /**
     * ---------------------------------------------------------------------<br>
     * @param type $stm
     * @param type $data
     */
    private function bindValues(&$stm, $data)
    {
        foreach ($data as $chave => $val) {
            $tipo = (is_int($val)) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stm->bindValue(":$chave", $val, $tipo);
        }
    }

    /**
     * ---------------------------------------------------------------------<br>
     * @param type $instrucao
     * @param type $bindValues
     * @param type $fetch
     * @return type
     */
    public function executar($instrucao, $bindValues = [], $fetch = PDO::FETCH_OBJ)
    {
        $stm = $this->prepare($instrucao);
        if ($bindValues) {

            $this->bindValues($stm, $bindValues);
        }

        $stm->execute();
        if (substr(ltrim($instrucao), 0, 6) == 'SELECT') {

            return $stm->fetchAll($fetch);
        }

        return $this->lastInsertId();
    }
}
