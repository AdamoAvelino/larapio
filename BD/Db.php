<?php

class Db extends PDO{

	function __construct(){
		extract($this->getConfigDb());
		$dns = sprintf("%s:host=%s;port=%s;dbname=%s",$sgbd, $host,$porta,$db);
		$options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		];

		parent::__construct($dns, $user, $pass, $options);
	}

	private function getConfigDb(){
		$config = parse_ini_file('config.ini');
		return $config;
	}

	private function bindValues(&$stm, $data = []){

		foreach($data as $chave => $val){
			$tipo = (is_int($val)) ? PDO::PARAM_INT : PDO::PARAM_STR;
			$stm->bindValue(":$chave", $val, $tipo);
		}
	}

	public function select($sql, array $where, $all = true, $fetch = PDO::FETCH_OBJ){
		
		$stm = $this->prepare($sql);

		$this->bindValues($stm, $where);
		
		$stm->execute();

		if($all){
			return $stm->fetchAll($fetch);
		}
		return $stm->fetch($fetch);
	}

	public function insert($tabela, $dados){

		$colunas = implode('`, `', array_keys($dados));
		$valores = implode(',:', array_keys($dados));
echo $valores;
		$sql = sprintf("INSERT INTO %s (`%s`) VALUES(:%s)", $tabela, $colunas, $valores);
		$stm = $this->prepare($sql);

		$this->bindValues($stm, $dados);


//		$stm->execute();
	}

	public function update($tabela, array $dados, $where, $codigo){
		foreach($dados as $chave => $valor){
			$sql_arr[] = "$chave=:$chave";
		}

		$atualizar = implode(',', $sql_arr);

		$sql = sprintf("UPDATE %s set %s %s", $tabela, $atualizar, $where);
		$stm = $this->prepare($sql);
		$this->bindValues($stm, $dados);
		
		$stm->bindValue('id', $codigo);

		return $stm->execute();

	}

	public function delete($table, $where, $codigo){
		$sql = sprintf("DELETE FROM %s %s", $table, $where);
		echo $sql;
		$stm = $this->prepare($sql);
		$stm->bindValue('id', $codigo);
		$stm->execute();
	}
}
