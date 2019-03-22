<?php

namespace Larapio\BD;

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
        $config = parse_ini_file('config.ini', true);
        extract($config);
        return $banco_de_dados;

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
     * @param type $ignoreFech Quando precisar de um resultado que deva vir em array, enviar falso no parametro
     * @return type
     */
    public function executar($instrucao, $ignoreFech, $bindValues = [])
    {
        $stm = $this->prepare($instrucao);
        if ($bindValues) {

            $this->bindValues($stm, $bindValues);
        }

        $stm->execute();
        if (substr(ltrim($instrucao), 0, 6) == 'SELECT') {

            $dataSetObject = $stm->fetchAll(PDO::FETCH_OBJ);
            
            if (count($dataSetObject) === 1 and $ignoreFech) {
                return $dataSetObject[0];
            }
            return $dataSetObject;
        }

        return $this->lastInsertId();

    }

}
