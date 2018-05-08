<?php

class Db extends PDO
{

    public function __construct()
    {
        extract($this->getConfigDb());
        $dns = sprintf("%s:host=%s;port=%s;dbname=%s", $sgbd, $host, $porta, $db);
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        parent::__construct($dns, $user, $pass, $options);
    }

    /**
     * ---------------------------------------------------------------------<br>
     * @return type
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
    private function bindValues(&$stm, $data = [])
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
    public function executar($instrucao, $bindValues, $fetch = PDO::FETCH_OBJ)
    {
        $stm = $this->prepare($instrucao);

        $this->bindValues($stm, $bindValues);

        $stm->execute();

        if (substr($instrucao, 0, 5) == 'SELECT') {
            return $stm->fetch($fetch);
        }
    }

}
