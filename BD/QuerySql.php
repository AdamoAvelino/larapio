<?php

class MagicSql
{

    protected $sql;

    public function __construct()
    {
        
    }

    public function __call($name, $arguments)
    {
        $this->sql .= " " . strtoupper($name);

        if (count($arguments == 1)) {
            $this->queryString($arguments);
            return $this;
        }

        $this->queryArray($name, $arguments);

        return $this;
    }

    private function queryString($arguments)
    {
        if ($arguments) {
            $this->sql .= " " . $arguments[0];
        }
    }

    private function queryArray($nome, $arguments)
    {
        if (in_array($nome, ['or', 'and'])) {
            
        }
    }

    public function getSql()
    {
        return $this->sql;
    }

}

$select = new MagicSql;
echo $select->select("id,nome, email,idade")
        ->from('usuarios us')
        ->join('empresa emp')
        ->on('emp.usuario = us.contador')
        ->where("nome like '%Carlos%'")
        ->or("codigo=3", "AND", "contador=7)")
        ->getSql();
echo "<br>";

$update = new MagicSql;
echo $update->update('orcamento')
        ->set("valor=2500.32,endereco=3")
        ->where("id=154")
        ->getSql();
