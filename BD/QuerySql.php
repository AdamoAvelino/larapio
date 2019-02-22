<?php

namespace BD;

use BD\Db;

class QuerySql
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
    public $dados;

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
    private $bd;

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
        $this->bd = new Db();

    }

    /** Constroi a query de acordo com um metodo não declarado na classe.
     * Os parametros servem para determinar a construção da string SQL
     *
     * <b> Metodos WHERE, ON AND, OR </b>
     *
     * Possiveis arrays em arguments:
     * Em metodos WHERE, ON AND, OR deverão vir com um array de no minimo três posições
     * [coluna, operador, valor]:
     *
     * No caso do operador de comparação BETWEEN deverão vir 4 posições
     * [coluna, valor1, BETWEEN, valor2]
     *
     * No caso dos operadores IN e NOT IN também deverão vir com 3 posições, porém
     * a terceira posição que representa os valores das colunas a serem trabalhadas,
     * deverá ser uma string separadas por ',' :
     * [coluna , (NOT IN | IN), 'v1, v2, v3,']
     *
     * Em caso de operações que devam avaliar valores nulos ou não nulos, apenas
     * duas posições satisfaz o array:
     * [coluna, (IS NULL | IS NOT NULL)]
     *
     * As operações lógicas (critérias), também pode serem feitas de forma isolada,
     * assim compreendemos o caso: ((coluna1 > 3 or coluna2 = 'string') AND coluna3 IS NULL)
     *
     * Para construção desta clausula com criterias isoladas é necessário que haja
     * mais de um array como argumentos com a ultima posição declarando uma das
     * constant OP_OR ou OP_AND
     *
     * <b> Metodos VALUES e SET </b>
     *
     * O metodo VALUES e SET (operadore de insert e update respctivamente),
     * carregará um array associativo com os nomes das colunas => valor
     *
     *
     * ---------------------------------------------------------------------<br>
     * @param string $name O nome do metodo disparado, definirá as operações sql.
     * @param array $arguments serão os atributos da query que serão trabalhados
     * @return $this retorna objeto com seus atributos armazenados para que haja
     * uma concatenação dos valores de cada operação solicitada
     *
     * @uses O parametro arguments pode ser um array contendo string ou um outro array
     *
     *
     */
    public function __call($name, $arguments)
    {
        $this->nome = strtoupper($name);

        $this->argumento = $arguments;

        $this->runSql();
        return $this;

    }

    /** Inicia a Montagem da Query, apatir deste metodo são tomadas todas as
     * decisões para tratamento dos valores e montagem da query
     *
     * ---------------------------------------------------------------------<br>
     *
     * @return boolean
     */
    private function runSql()
    {
        if ($this->nome == 'VALUES') {
            $this->implodeString($this->nome);
            $this->argumento[0] = $this->sqlInsert($this->argumento[0]);
        }


        $nome = (strstr($this->nome, '_')) ? implode(' ', explode('_', $this->nome)) : $this->nome;

        $this->sql .= " " . $nome;

        if (count($this->argumento) == 1 and ( is_string($this->argumento[0]) or is_int($this->argumento[0]))) {
            $this->sql .= " " . $this->argumento[0];
        }

        if ($this->nome == 'SET') {
            $this->argumento = $this->sqlUpdate();
            return true;
        }

        $this->queryArray();

        return true;

    }

    /**
     *
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

                $this->bindString($this->montarBind($argumento));
            }

            $this->sql .= ' ( ' . $this->criteria[0] . ' ) ';
            $this->criteria = null;
        }

    }

    /**
     * ---------------------------------------------------------------------<br>
     * @param type $arg
     * @return type
     */
    private function sqlInsert($arg)
    {
        $colunasInto = implode(', ', array_keys($arg));
        $this->sql .= " (" . $colunasInto . ")";
        return array_values($arg);

    }

    /**
     * ---------------------------------------------------------------------<br>
     */
    private function sqlUpdate()
    {
        $sql = $this->bindString($this->montarBind(array_values($this->argumento[0])));
        $this->sql .= " " . $sql;

    }

    /**
     * ---------------------------------------------------------------------<br>
     *
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

        $this->operacaoCriteria = isset($operacoes[1]) ? strtoupper($operacoes[1]) : '';

        $quantidadeAtribuicao = count($operacoes);

        $i = !in_array($this->nome, ['VALUES', 'SET']) ? 2 : 0;
        $saoColunas = $i;

        for ($i; $i < $quantidadeAtribuicao; $i++) {
            $valorAtribuicao = $operacoes[$i];

            $verificacaoAlias = explode('.', $valorAtribuicao);

            if ((count($verificacaoAlias) > 1 and is_numeric(current($verificacaoAlias))) or count($verificacaoAlias) == 1 or ! $saoColunas) {
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
        if (is_array($operacoes) and $this->nome != 'SET') {

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
            return true;
        }

        if ($this->nome == 'SET') {
            $valoresBind = explode(',', ':' . implode(',:', $operacoes));
            $colunas = array_keys($this->argumento[0]);
            $sql = implode(' = %s, ', $colunas) . ' = %s ';
            return vsprintf($sql, $valoresBind);
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

        return "$dado";

    }

    /**
     * ---------------------------------------------------------------------<br>
     * @param type $operador
     */
    private function implodeString($operador)
    {
        $this->stringImplode = ($operador == 'VALUES' or in_array($operador, self::OPERADORES));

    }

    public function getSql($ignoreFech = true)
    {
        $sql = $this->sql;
        $this->sql = '';
        $dados = $this->dados;
        $this->dados = [];
//        var_dump($sql);
        return $this->bd->executar($sql, $ignoreFech, $dados);

    }

}
