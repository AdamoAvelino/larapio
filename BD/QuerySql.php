<?php

class MagicSql
{

    /**
     * É Alimentado de forma concatenada para retorna a string sql fromatada
     * @var string
     */
    protected $sql;

    /**
     * Guarda o nome do metodo magico quando invocado uma instrução SQL
     * Pode ser (INSERT, UPDATE, SELECT, DELETE, WHERE, JOIN LEFT_JOIN, ON) 
     * @var string
     */
    private $nome;

    /**
     * Parametros disparados do metodo magico, para preencimento atributos e 
     * valores do sql
     * @var array 
     */
    private $argumento;

    /**
     * Armazena os valores dos dados para fazer o bind do stm do PDO
     * @var array
     */
    private $dados;

    /**
     * Montar critéria de clausulas isoladas de WHERE e ON (em JONIS)
     * Exemplo: WHERE( contador = 26 AND (nome LIKE '%João%' OR codigo > 25))
     * @var array
     */
    private $criteria;

    /**
     * Decide se a construção de valores do bind ficam entre parênteses em caso
     * de operações IN NOT IN ou VALUES da Instrução INSERT
     * @var Bool 
     */
    private $stringImplode;

    /**
     * Isola o operador lógigo para decisão da montagem BETWEEN
     * @var string
     */
    private $operacaoCriteria;

    /**
     * Faz a decisão de operadores IN e NOT IN, que são parametros em string e 
     * devem ser implodidos para um array e fazer o bind para stm do PDO
     * @var Bool
     */
    private $inNot;

    /**
     * Operador Lógico AND que deve ser usado no ulotimo parametro do metodo 
     * mágico em isolamentos das clausulas 
     */
    const OP_AND = "AND";

    /**
     * Operador Lógico OR que deve ser usado no ulotimo parametro do metodo 
     * mágico em isolamentos das clausulas 
     */
    const OP_OR = "OR";

    /**
     * Operações que definirão 
     */
    const OPERACOES = ['ON', 'WHERE', 'AND', 'OR', 'VALUES'];

    /**
     *
     */
    const OPERADORES = ['IN', 'NOT IN'];

    public function __construct()
    {
        
    }

    /**
     * ---------------------------------------------------------------------<br>
     * @param type $name
     * @param type $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        $this->nome = strtoupper($name);

        $this->argumento = $arguments;

        $this->runSql();
        return $this;
    }

    /**
     * ---------------------------------------------------------------------<br>
     *
     */
    private function runSql()
    {
        if ($this->nome == 'VALUES') {
            $this->implodeString($this->nome);
            $this->argumento[0] = $this->sqlInsert($this->argumento[0]);
//            var_dump($this->argumento);
        }


        $nome = (strstr($this->nome, '_')) ? implode(' ', explode('_', $this->nome)) : $this->nome;

        $this->sql .= " " . $nome;

        if (count($this->argumento) == 1 and is_string($this->argumento[0])) {
            $this->queryString();
        }

        if ($this->nome == 'SET') {
            $this->argumento = $this->sqlUpdate();
            return true;
        }

        $this->queryArray();
    }

    /*
     * ---------------------------------------------------------------------<br>
     *
     */

    private function queryString()
    {
        $this->sql .= " " . $this->argumento[0];
    }

    /**
     * ---------------------------------------------------------------------<br>
     * @return boolean
     */
    private function queryArray()
    {
        if (in_array($this->nome, self::OPERACOES)) {
            foreach ($this->argumento as $argumento) {

                $argumento = $this->verificarOperacao($argumento);

                if (is_string($argumento)) {
                    $criteriaFormacao = implode(" $argumento ", $this->criteria);
                    $this->sql .= ' ( ' . $criteriaFormacao . ' ) ';
                    $this->criteria = null;
                    return true;
                }
//                var_dump($this->nome);


                $this->bindString($this->montarBind($argumento));
            }

            $this->sql .= ' ( ' . $this->criteria[0] . ' ) ';
            $this->criteria = null;
        }
    }

    private function sqlInsert($arg)
    {
        $colunasInto = implode(', ', array_keys($arg));
        $this->sql .= " (" . $colunasInto . ")";
//        var_dump(array_values($arg));
        return array_values($arg);
    }

    private function sqlUpdate()
    {
        $valoresBind = $this->montarBind(array_values($this->argumento[0]));
        $valoresBind = explode(',', ':' . implode(',:', $valoresBind));
        $colunas = array_keys($this->argumento[0]);
        $sql = implode(' = %s, ', $colunas) . ' = %s ';
        $sql = vsprintf($sql, $valoresBind);
        $this->sql .= " " . $sql;
    }

    /**
     * ---------------------------------------------------------------------<br>
     * @param type $argumento
     * @return type
     */
    private function verificarOperacao($argumento)
    {

        $this->inNot = (count($argumento) > 1 and in_array(strtoupper($argumento[1]), self::OPERADORES));

        if ($this->inNot) {
            $this->implodeString(strtoupper($argumento[1]));
            $parametroString = array_pop($argumento);
            $parametrosArray = explode(',', $parametroString);
            $argumento = array_merge($argumento, $parametrosArray);
        }

        return $argumento;
    }

    /**
     * ---------------------------------------------------------------------<br>
     * @param type $operacoes
     * @return type
     */
    private function montarBind($operacoes)
    {
        $this->operacaoCriteria = strtoupper($operacoes[1]);

        $quantidadeAtribuicao = count($operacoes);

        $i = !in_array($this->nome, ['VALUES', 'SET']) ? 2 : 0;

        for ($i; $i < $quantidadeAtribuicao; $i++) {
            $valorAtribuicao = $operacoes[$i];

            $verificacaoAlias = explode('.', $valorAtribuicao);

            if ((count($verificacaoAlias) > 1 and is_numeric(current($verificacaoAlias))) or count($verificacaoAlias) == 1) {
                $this->dados[] = $this->formatarClausula($valorAtribuicao);

                $operacoes[$i] = (count($this->dados) - 1);
                continue;
            }
            $operacoes[$i] = $valorAtribuicao;
        }
        return $operacoes;
    }

    /**
     *
     * ---------------------------------------------------------------------<br>
     * @param type $operacoes
     * @return boolean
     */
    private function bindString($operacoes)
    {
        $atribuicaoBind = [];
        if (is_array($operacoes)) {

            $atributo = ($this->nome != 'VALUES') ? array_shift($operacoes) : '';
            $operador = ($this->nome != 'VALUES') ? strtoupper(array_shift($operacoes)) : '';

            foreach ($operacoes as $atribuicao) {
                $atribuicaoBind[] = (!is_int($atribuicao)) ? $atribuicao : ":" . $atribuicao;

                if ($this->operacaoCriteria == 'BETWEEN' and ! in_array(self::OP_AND, $atribuicaoBind)) {
                    $atribuicaoBind[] = self::OP_AND;
                }
            }

            $separador = $this->stringImplode ? ', ' : ' ';
            $atribuicaoCriteria = implode($separador, $atribuicaoBind);
            $atribuicaoCriteria = $this->inNot ? ' ( ' . $atribuicaoCriteria . ' ) ' : $atribuicaoCriteria;
            $this->criteria[] = $atributo . " " . $operador . " " . $atribuicaoCriteria;
            return false;
        }

        return true;
    }

    /**
     * ---------------------------------------------------------------------<br>
     * @param type $dado
     * @return type
     */
    private function formatarClausula($dado)
    {
        if (is_numeric($dado)) {
            return $dado;
        }

        return "'$dado'";
    }

    /**
     * ---------------------------------------------------------------------<br>
     * @param type $operador
     */
    private function implodeString($operador)
    {
        $this->stringImplode = ($operador == 'VALUES' or in_array($operador, self::OPERADORES));
    }

    public function getSql()
    {
        var_dump($this->dados);
        return $this->sql;
    }

}

$select = new MagicSql;
echo $select->select("id,nome, email,idade")
        ->from('usuarios us')
        ->left_join('empresa emp')
        ->on(['emp.usuario', '=', 'us.contador'])
        ->and(['data', 'BETWEEN', '2018-04-25', '2018-04-30'], ['nomero', 'not in', '1,2,3,5,6'], ['nome', 'LIKE', '%avelino%'], MagicSql::OP_OR)
        ->where(["contador", "=", "50"])
        ->or(["codigo", "=", "3"], ["contador", "<", 7], MagicSql::OP_AND)
        ->and(["contador", "=", 55.4])
        ->and(["coluna", "IS NULL"])
        ->getSql();
echo "<br>";
echo "<br>";

$insert = new MagicSql;
echo $insert->insert()
        ->into('pedido')
        ->values(['valor' => 2350, 'numero' => 22, 'peso' => 2.5, 'descicao' => 'produto legal'])
        ->where(["id", "=", 154])
        ->getSql();
echo "<br>";
echo "<br>";

$update = new MagicSql;
echo $update->update('orcamento')
        ->set(['valor' => 2500.32, 'endereco' => 3])
        ->where(["id", "=", 154])
        ->getSql();
echo "<br>";
echo "<br>";

$delete = new MagicSql;
echo $delete->delete()
        ->from('pedidos')
        ->where(["id", "=", 154])
        ->getSql();
echo "<br>";

/**
 *
 * =====================Primeiro Teste=========================================
 */





/**
 * Tratamentos de colunas com atribuiçoes de dois pontos
 * ***********************
 * values de insert
 * atribuições de update
 * ----------------------------------------------
 * Tratamento de stirng
 * ********************
 * Colunas de SELECT
 * -----------------------------------------------
 * Tratamentos de colunas Especiais com atribuição dos dois pontos
 * ****************************************************************
 * Operadores AND OR WHERE
 *
 */

/**
 * Tratamentos de nomes composto
 * ******************************
 * Separação da composição do nome quando necessário, tanto para identificar
 * um lef/right JOIN com para verificar como para determinar um operação lógica
 * com isolamento de parenteses
 */

/**
 * Sempre montar um array para tratamentos de valores de colunas que levan alias.
 */
