<?php

include "Db.php";
$bd = new Db();

$bd->insert('produtos', ['contador' => 5, 'decricao' => 'teste insert', 'nome' => 'produto']);
var_dump($bd);
