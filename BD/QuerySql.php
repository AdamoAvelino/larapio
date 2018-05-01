<?php

class MagicSql
{

    /**
     *
     * @var type
     */
    protected $sql;
    /**
     *
     * @var type
     */
    private $nome;
    /**
     *
     * @var type
     */
    private $argumento;
    /**
     *
     * @var type
     */
    private $dados;
    /**
     *
     * @var type
     */
    private $criteria;
    /**
     *
     * @var type
     */
    private $operacaoCriteria;
    /**
     *
     * @var type
     */
    private $inNot;
    
    /**
     *
     */
    const OP_AND = "AND";
    /**
     *
     */
    const OP_OR = "OR";
    /**
     *
     */
    const OPERACOES = ['on', 'where', 'and', 'or'];
    /**
     *
     */
    const OPERADORES = ['in', 'not in'];

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
        $this->nome = $name;

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
        $nome = (strstr($this->nome, '_')) ? implode(' ', explode('_', $this->nome)) : $this->nome;

        $this->sql .= " " . strtoupper($nome);

        if (count($this->argumento) == 1 and is_string($this->argumento[0])) {
            $this->queryString();
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
                $this->inNot = (count($argumento) > 1 and in_array($argumento[1], self::OPERADORES));
                if ($this->inNot) {
                    $parametroString = array_pop($argumento);
                    $parametrosArray = explode(',', $parametroString);
                    $argumento = array_merge($argumento, $parametrosArray);
                    var_dump($argumento);
                }

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
     * @param type $operacoes
     * @return type
     */
    private function montarBind($operacoes)
    {
        $this->operacaoCriteria = $operacoes[1];
        $quantidadeAtribuicao = count($operacoes);

        for ($i = 2; $i < $quantidadeAtribuicao; $i++) {
            $valorAtribuicao = $operacoes[$i];

            $verificacaoAlias = explode('.', $valorAtribuicao);

            if ((count($verificacaoAlias) > 1 and is_int(current($verificacaoAlias))) or count($verificacaoAlias) == 1) {
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
        if (is_array($operacoes)) {
            $atributo = array_shift($operacoes);
            $operador = array_shift($operacoes);

            foreach ($operacoes as $atribuicao) {
                $atribuicaoBind[] = (!is_int($atribuicao)) ? $atribuicao : ":" . $atribuicao;

                if ($this->operacaoCriteria == 'BETWEEN' and ! in_array(self::OP_AND, $atribuicaoBind)) {
                    $atribuicaoBind[] = self::OP_AND;
                }
            }
            $separador = $this->inNot ? ', ' : ' ';
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
     * @param array $nomeSeparadoAssoc
     */
    private function queryClausula(array $nomeSeparadoAssoc)
    {
        if ($this->existeClausula($nomeSeparadoAssoc)) {
        }
    }

    /**
     * ---------------------------------------------------------------------<br>
     */
    private function clausulaQuery()
    {
    }

    /**
     * ---------------------------------------------------------------------<br>
     */
    private function colunasQuery()
    {
    }

    /**
     * ---------------------------------------------------------------------<br>
     * @return type
     */
    public function getSql()
    {
        return $this->sql;
    }
}

/**
 *
 * =====================Primeiro Teste=========================================
$select = new MagicSql;
echo $select->select("id,nome, email,idade")
        ->from('usuarios us')
        ->left_join('empresa emp')
        ->on(['emp.usuario', '=', 'us.contador'])
        ->and(['data', 'BETWEEN', '2018-04-25', '2018-04-30'], ['nomero', 'not in', '1,2,3,5,6'], ['nome', 'LIKE', '%avelino%'], MagicSql::OP_AND)
        ->where(["contador", "=", "50"])
        ->or(["codigo", "=", "3"], ["contador", "<", 7], MagicSql::OP_AND)
        ->and(["contador", "=", 55.4])
        ->getSql();
echo "<br>";
 */

//$update = new MagicSql;
//echo $update->update('orcamento')
//        ->set([valor => 2500.32, endereco => 3])
//        ->where(["id", "=", 154])
//        ->getSql();
//echo "<br>";
//echo $update->insert()
//        ->into('pedido', ['valor', 'numero', 'peso', 'descicao'])
//        ->values([2350, 22, 458, 2.5, 'produto legal'])
//        ->where(["id", "=", 154])
//        ->getSql();



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
