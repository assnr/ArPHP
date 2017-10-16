<?php
namespace ar\comp\Db;
use ar\core\Ar as Ar;
use ar\comp\Component as Component;
/**
 * ArPHP A Strong Performence PHP FrameWork ! You Should Have.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Core.Component.Db
 * @author   yc <ycassnr@gmail.com>
 * @license  http://www.arphp.org/licence MIT Licence
 * @version  GIT: 1: coding-standard-tutorial.xml,v 1.0 2014-5-01 18:16:25 cweiske Exp $
 * @link     http://www.arphp.org
 */

/**
 * mysql
 *
 * default hash comment :
 *
 * <code>
 *  # This is a hash comment, which is prohibited.
 *  $hello = 'hello';
 * </code>
 *
 * @category ArPHP
 * @package  Core.Components.Db
 * @author   yc <ycassnr@gmail.com>
 * @license  http://www.arphp.org/licence MIT Licence
 * @version  Release: @package_version@
 * @link     http://www.arphp.org
 */
class Mssql extends Db
{
    // driver
    // public $driverName = __CLASS__;
    // last sql
    public $lastSql = '';
    // last insert id
    public $lastInsertId = '';
    // guess
    public $allowGuessConditionOperator = true;
    // query options
    protected $options = array(
        'columns' => '*',
        'table' => '',
        'join' => '',
        'where' => '',
        'group' => '',
        'having' => '',
        'order' => '',
        'limit' => '',
        'union' => '',
        'comment' => '',
    );

    /**
     * flush options.
     *
     * @return boolean
     */
    protected function flushOptions()
    {
        $this->options = array(
            'columns' => '*',
            'table' => '',
            'join' => '',
            'where' => '',
            'group' => '',
            'having' => '',
            'order' => '',
            'limit' => '',
            'union' => '',
            'comment' => '',
        );
        return true;

    }

    /**
     * query
     *
     * @param string $sql sql string.
     *
     * @return mixed
     */
    public function query($sql = '')
    {
        static $i = array();
        $returnResult = false;
        if (empty($sql)) :
            $sql = $this->buildSelectSql();
        else :
            $returnResult = true;
        endif;

        $sqlCmd = strtoupper(substr($sql, 0, 6));

        if(in_array($sqlCmd, array('UPDATE', 'DELETE')) && stripos($sql, 'where') === false) :
            throw new \ar\core\DbException('no WHERE condition in SQL(UPDATE, DELETE) to be executed! please make sure it\'s safe', 42005);
        endif;

        $this->lastSql = $sql;
        $this->flushOptions();

        try {
            $this->pdoStatement = $this->getDbConnection()->query($sql);
            $i[] = $this->pdoStatement;
        } catch (\PDOException $e) {
            throw new \ar\core\DbException($e->getMessage() . ' lastsql :' . $sql);
        }
        if ($returnResult) :
            return $this->pdoStatement->fetchAll(\PDO::FETCH_ASSOC);
        endif;

        return $this->pdoStatement;

    }

    /**
     * get columns.
     *
     * @return mixed
     */
    public function getColumns()
    {
        $table = $this->options['table'];

        $sql = 'show columns from ' . $table;

        $ret = $this->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        $columns = array();

        foreach ($ret as $value) :
            $columns[] = $value['Field'];
        endforeach;

        return $columns;

    }

    /**
     * count.
     *
     * @return mixed
     */
    public function count()
    {
        $result = $this->select(array('COUNT(\'*\') as t'))->queryRow();
        if (empty($result)) :
            $total = 0;
        else :
            $total = (int)$result['t'];
        endif;
        return $total;

    }

    /**
     * query row.
     *
     * @return mixed
     */
    public function queryRow()
    {
        $this->limit(1);
        return $this->query()->fetch(\PDO::FETCH_ASSOC);

    }

    /**
     * query row column.
     *
     * @param string $field column
     *
     * @return mixed
     */
    public function queryColumn($field = '')
    {
        if ($result = $this->select($field)->queryRow()) :
            $result = $result[$field];
        endif;
        return $result;

    }

    /**
     * query all.
     *
     * @param string $columnKey key.
     *
     * @return mixed
     */
    public function queryAll($columnKey = '')
    {
        $result = $this->query()->fetchAll(\PDO::FETCH_ASSOC);
        if ($result && $columnKey) :
            $dataBox = array();
            foreach ($result as $row) :
                if (isset($row[$columnKey])) :
                    $dataBox[$row[$columnKey]] = $row;
                endif;
            endforeach;
            $result = $dataBox;
        endif;
        return $result;

    }

    /**
     * source.
     *
     * @param string $source source.
     *
     * @return Object
     */
    public function setSource($source)
    {
        $this->options['source'] = $source;
        return $this;

    }

    /**
     * insert.
     *
     * @param array   $data      data.
     * @param boolean $checkData checkData for filter.
     *
     * @return mixed
     */
    public function insert(array $data = array(), $checkData = false)
    {
        $options = $this->options;

        if (\ar\core\Model::model($this->options['source'])->insertCheck($data)) :

            $data = \ar\core\Model::model($this->options['source'])->formatData($data);

            if (!empty($data)) :
                if ($checkData) :
                    $data = \ar\core\comp('format.format')->filterKey($this->getColumns(), $data);
                endif;

                $this->options = $options;

                $this->data($data);
            else :
                return false;
            endif;

            $sql = $this->bulidInsertSql();
            $this->exec($sql);

            return $this->lastInsertId = $this->getDbConnection()->lastInsertId();

        endif;

        return false;

    }

    /**
     * update.
     *
     * @param array   $data      data.
     * @param boolean $checkData checkData.
     *
     * @return mixed
     */
    public function update(array $data = array(), $checkData = false)
    {
        $options = $this->options;

        if ($checkData) :
            $data = \ar\core\comp('format.format')->filterKey($this->getColumns(), $data);
            unset($data['id']);
        endif;

        $this->options = $options;

        if (!empty($data)) :
            $this->columns($data);
        endif;

        $sql = $this->bulidUpdateSql();

        return $this->exec($sql);


    }

    /**
     * delete.
     *
     * @return mixed
     */
    public function delete()
    {
        $sql = $this->buildDeleteSql();
        if (!preg_match('/ WHERE /i', $sql)) :
            throw new \ar\core\DbException('bad sql condition , where must to be infix');
        endif;
        return $this->exec($sql);

    }

    /**
     * exec.
     *
     * @param string $sql sql.
     *
     * @return mixed
     */
    protected function exec($sql)
    {
        try {
            $this->lastSql = $sql;
            $this->flushOptions();
            return $this->getDbConnection()->exec($sql);
        } catch (\PDOException $e) {
            throw new \ar\core\DbException($e->getMessage() . ' lastsql :' . $sql);
        }

    }

    /**
     * quote.
     *
     * @param mixed $data data.
     *
     * @return mixed
     */
    protected function quote($data)
    {
        if (is_array($data) || is_object($data)) :
            $return = array();
            foreach ($data as $k => $v) :
                $return[$k] = $this->quote($v);
            endforeach;
            return $return;
        else :
            $data = $this->getDbConnection()->quote($data);
            if (false === $data) :
                $data = "''";
            endif;
            return $data;
        endif;

    }

    /**
     * select fields.
     *
     * @param mixed $fields fields.
     *
     * @return mixed
     */
    public function select($fields = '')
    {
        if (is_string($fields) && strpos($fields, ',')) :
            $fields = explode(',', $fields);
        endif;

        if (is_array($fields)) :
            $array   =  array();
            foreach ($fields as $key => $field) :
                if (!is_numeric($key)) :
                    $array[] = $this->quoteObj($key) . ' AS ' . $this->quoteObj($field);
                else :
                    $array[] = $this->quoteObj($field);
                endif;
            endforeach;
            $fieldsStr = implode(',', $array);
        elseif (is_string($fields) && !empty($fields)) :
            $fieldsStr = $this->quoteObj($fields);
        else :
            $fieldsStr = '*';
        endif;

        $this->options['columns'] = $fieldsStr;
        return $this;

    }

    /**
     * table.
     *
     * @param string $table table.
     *
     * @return mixed
     */
    public function table($table)
    {
        $this->options['table'] = $this->quoteObj($this->getCurrentConfig('prefix') . $table);
        return $this;

    }

    /**
     * join.
     *
     * @param string $table table.
     * @param mixed  $cond  condition.
     *
     * @return mixed
     */
    public function join($table, $cond)
    {
        return $this->joinInternal('JOIN', $table, $cond);

    }

    /**
     * leftjon.
     *
     * @param string $table table.
     * @param mixed  $cond  condition.
     *
     * @return mixed
     */
    public function leftJoin($table, $cond)
    {
        return $this->joinInternal('LEFT JOIN', $table, $cond);

    }


    /**
     * rightjoin.
     *
     * @param string $table table.
     * @param mixed  $cond  condition.
     *
     * @return mixed
     */
    public function rightJoin($table, $cond)
    {
        return $this->joinInternal('RIGHT JOIN', $table, $cond);

    }


    /**
     * join.
     *
     * @param string $join  join table.
     * @param string $table table.
     * @param mixed  $cond  condition.
     *
     * @return mixed
     */
    protected function joinInternal($join, $table, $cond)
    {
        $table = $this->quoteObj($table);
        $this->options['join'] .= " $join $table ";
        if (is_string($cond) && (strpos($cond, '=') === false && strpos($cond, '<') === false && strpos($cond, '>') === false)) :
            $column = $this->quoteObj($cond);
            $this->options['join'] .= " USING ($column) ";
        else :
            $cond = $this->buildCondition($cond);
            $this->options['join'] .= " ON $cond ";
        endif;
        return $this;

    }

    /**
     * quote.
     *
     * @param string $objName objName.
     *
     * @return mixed
     */
    public function quoteObj($objName)
    {
        return $objName;
        if (is_array($objName)) :
            $return = array();
            foreach ( $objName as $k => $v ) :
                $return[] = $this->quoteObj($v);
            endforeach;
            return $return;
        else :
            $v = trim($objName);
            $v = str_replace('`', '', $v);
            $v = preg_replace('# +AS +| +#i', ' ', $v);
            $v = explode(' ', $v);

            foreach ($v as $k_1 => $v_1) :
                $v_1 = trim($v_1);
                if ($v_1 == '') :
                    unset($v[$k_1]);
                    continue;
                endif;
                if (strpos($v_1, '.')) :
                    $v_1 = explode('.', $v_1);
                    foreach ($v_1 as $k_2 => $v_2) :
                        if ($v_2 != '*') :
                            $v_1[$k_2] = '`' . trim($v_2) . '`';
                        else :
                            $v_1[$k_2] = trim($v_2);
                        endif;
                    endforeach;
                    $v[$k_1] = implode('.', $v_1);
                elseif (preg_match('#\(.+\)#', $v_1)) :
                    $v[$k_1] = $v_1;
                else :
                    $v[$k_1] = '`'.$v_1.'`';
                endif;
            endforeach;

            $v = implode(' AS ', $v);
            return $v;
        endif;

    }

    /**
     * group.
     *
     * @param string $group group.
     *
     * @return mixed
     */
    public function group($group)
    {
        $this->options['group'] = empty($group) ? '' : ' GROUP BY ' . $group;
        return $this;

    }

    /**
     * having.
     *
     * @param string $having having.
     *
     * @return mixed
     */
    public function having($having)
    {
        $this->options['having'] = empty($having) ? '' : ' HAVING ' . $having;
        return $this;

    }

    /**
     * where.
     *
     * @param mixed $conditions cond.
     *
     * @return mixed
     */
    public function where($conditions = '')
    {
        $conStr = $this->buildCondition($conditions);
        $this->options['where'] = empty($conStr) ? '' : ' WHERE ' . $conStr;
        return $this;

    }


    /**
     * order.
     *
     * @param mixed $order order by.
     *
     * @return mixed
     */
    public function order($order)
    {
        $this->options['order'] = empty($order) ? '' : ' ORDER BY ' . $order;
        return $this;

    }

    /**
     * (top) limit just compare with mysql.
     *
     * @param mixed $limit limit.
     *
     * @return mixed
     */
    public function limit($limit)
    {
        $this->options['limit'] = empty($limit) ? '' : ' TOP ' . $limit;
        return $this;

    }

    /**
     * union.
     *
     * @param mixed $union union.
     *
     * @return mixed
     */
    public function union($union)
    {

    }

    /**
     * columns.
     *
     * @param mixed $data data.
     *
     * @return mixed
     */
    public function columns($data)
    {
        $setStr = '';
        if (is_string($data)) :
            $setStr = $data;
        elseif (is_array($data)) :
            foreach ($data as $key => $val) :
                $set[] = $this->quoteObj($key) . '=' . $this->quote($val);
            endforeach;
            $setStr = implode(',', $set);
        endif;
        $this->options['set'] = ' SET ' . $setStr;

        return $this;

    }

    /**
     * where.
     *
     * @param array $data data.
     *
     * @return mixed
     */
    public function data(array $data)
    {
        $values  =  $fields    = array();
        foreach ($data as $key => $val) :
            if(is_scalar($val) || is_null($val)) :
                $fields[] = $this->quoteObj($key);
                $values[] = $this->quote($val);
            endif;
        endforeach;
        $this->options['data'] = '(' . implode($fields, ',') . ') VALUES (' . implode($values, ',') . ')';
        return $this;
    }

    /**
     * build
     *
     * @param mixed  $condition cond.
     * @param string $logic     logic.
     *
     * @return mixed
     */
    public function buildCondition($condition = array(), $logic = 'AND')
    {
        if (!is_array($condition)) :
            if (is_string($condition)) :
                $count = preg_match('#\>|\<|\=| #', $condition, $logic);
                if (!$count) :
                    throw new \ar\core\DbException('bad sql condition: must be a valid sql condition');
                endif;
                $condition = explode($logic[0], $condition);
                $condition[0] = $this->quoteObj($condition[0]);
                $condition = implode($logic[0], $condition);
                return $condition;
            endif;
            throw new \ar\core\DbException('bad sql condition: ' . gettype($condition));
        endif;

        $logic = strtoupper($logic);
        $content = null;
        foreach ($condition as $k => $v) :
            $v_str = null;
            $v_connect = '';

            if (is_int($k)) :
                if ($content) :
                    $content .= $logic . ' (' . $this->buildCondition($v) . ') ';
                else :
                    $content = '(' . $this->buildCondition($v) . ') ';
                endif;
                continue;
            endif;

            $k = trim($k);

            $maybe_logic = strtoupper($k);

            if (in_array($maybe_logic, array('AND', 'OR'))) :
                if ($content) :
                    $content .= $logic . ' (' . $this->buildCondition($v, $maybe_logic) . ') ';
                else :
                    $content = '(' . $this->buildCondition($v, $maybe_logic) . ') ';
                endif;
                continue;
            endif;

            $k_upper = strtoupper($k);

            $maybe_connectors = array('>=', '<=', '<>', '!=', '>', '<', '=',
                ' NOT BETWEEN', ' BETWEEN', 'NOT LIKE', ' LIKE', ' IS NOT', ' NOT IN', ' IS', ' IN');

            foreach ($maybe_connectors as $maybe_connector) :
                $l = strlen($maybe_connector);
                if (substr($k_upper, -$l) == $maybe_connector) :
                    $k = trim(substr($k, 0, -$l));
                    $v_connect = $maybe_connector;
                    break;
                endif;
            endforeach;

            if (is_null($v)) :
                $v_str = ' NULL';
                if ($v_connect == '') :
                    $v_connect = 'IS';
                endif;
            elseif (is_array($v)) :
                if ($v_connect == ' BETWEEN') :
                    $v_str = $this->quote($v[0]) . ' AND ' . $this->quote($v[1]);
                elseif (is_array($v) && !empty($v)) :
                    $v_str = null;
                    foreach ($v AS $one) :
                        if (is_array($one)) :
                            $sub_items = '';
                            foreach ($one as $sub_value) :
                                $sub_items .= ',' . $this->quote($sub_value);
                            endforeach;
                            $v_str .= ',(' . substr($sub_items, 1) . ')' ;
                        else :
                            $v_str .= ',' . $this->quote($one);
                        endif;
                    endforeach;
                    $v_str = '(' . substr($v_str, 1) . ')';

                    if (empty($v_connect)) :
                        if ($this->allowGuessConditionOperator === null || $this->allowGuessConditionOperator === true) :
                            $v_connect = 'IN';
                        else :
                            throw new \ar\core\DbException("guessing condition operator is not allowed: use '$k IN'=>array(...)");
                        endif;
                    endif;
                elseif (empty($v)) :
                    $v_str = $k;
                    $v_connect = '<>';
                endif;
            else :
                $v_str = $this->quote($v);
            endif;

            if (empty($v_connect)) :
                $v_connect = '=';
            endif;

            $quoted_k = $this->quoteObj($k);
            if ($content) :
                $content .= " $logic ( $quoted_k $v_connect $v_str ) ";
            else :
                $content = " ($quoted_k $v_connect $v_str) ";
            endif;
        endforeach;

        return $content;

    }

    /**
     * build sql
     *
     * @return string
     */
    protected function buildSelectSql()
    {
        $sql = str_replace(
            array('%TABLE%','%COLUMNS%','%JOIN%','%WHERE%','%GROUP%','%HAVING%','%ORDER%','%LIMIT%','%UNION%','%COMMENT%'),
            array(
                    $this->options['table'],
                    $this->options['columns'],
                    $this->options['join'],
                    $this->options['where'],
                    $this->options['group'],
                    $this->options['having'],
                    $this->options['order'],
                    $this->options['limit'],
                    $this->options['union'],
                    $this->options['comment']
                ),
            'SELECT %LIMIT% %COLUMNS% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%%UNION%%COMMENT%'
        );

        return $sql;

    }

    /**
     * bulid update sql.
     *
     * @return string
     */
    protected function bulidUpdateSql()
    {
        $sql   = str_replace(
            array('%TABLE%','%SET%','%WHERE%','%COMMENT%'),
            array(
                    $this->options['table'],
                    $this->options['set'],
                    $this->options['where'],
                    $this->options['comment']
                ),
            'UPDATE %TABLE%%SET%%WHERE%%COMMENT%'
        );

        return $sql;

    }

    /**
     * bulid insert sql.
     *
     * @return string
     */
    protected function bulidInsertSql()
    {
        $sql = str_replace(
            array('%TABLE%','%DATA%','%COMMENT%'),
            array(
                    $this->options['table'],
                    $this->options['data'],
                    $this->options['comment']
                ),
            'INSERT INTO %TABLE%%DATA%%COMMENT%'
        );

        return $sql;

    }

    /**
     * bulid delete sql.
     *
     * @param mixed $options options
     *
     * @return string
     */
    public function buildDeleteSql($options = array())
    {
        $sql = str_replace(
            array('%TABLE%', '%WHERE%', '%COMMENT%'),
            array(
                    $this->options['table'],
                    $this->options['where'],
                    $this->options['comment']
                ),
            'DELETE FROM %TABLE%%WHERE%%COMMENT%'
        );

        return $sql;

    }

    /**
     * to string.
     *
     * @return mixed
     */
    public function __toString()
    {
        return var_export(get_class_methods(__CLASS__), 1);

    }

}
