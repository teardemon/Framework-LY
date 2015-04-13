<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-08-06
 */

namespace Core\library;


class mysql extends clsPDO
{

    const OPT_EMPTY = '';

    const OPT_IGNORE = 'IGNORE';

    const OPT_DELAYED = "DELAYED";

    const OPT_LOW_PRIORITY = 'LOW_PRIORITY';

    const MSG_LENGTH_LIMIT = 256;


    private static $instances = [];

    private static $errorLog = '/tmp/mysql-error.log';

    private $instanceName;

    private $clauses = [];

    private $params = [];

    private $batch = null;

    private $rowCount = null;

    /**
     * @var \PDOStatement
     */
    private $lastPDOStatement;

    private $last;


    /**
     * @param $name
     * @return mysql
     */
    public static function instance($name)
    {
        if (isset (self::$instances[$name]) && self::$instances[$name] instanceof \PDO) {
            $inst = self::$instances[$name];
        } else {
            $config = \config::load('mysql', $name);
            $dsn = $config['dsn'] ? : 'mysql:host=localhost';
            $username = $config['username'];
            $password = $config['password'];
            $iOptions = isset($config['options']) ? $config['options'] : [];
            $options = [];
            foreach ($iOptions as $key => $val) {
                $key = @constant("\\PDO::$key") ? : $key;
                $val = @constant("\\PDO::$val") ? : $val;
                $options[$key] = $val;
            }
            self::setErrorLogPath();
            $inst = new self($dsn, $username, $password, $options);
            $inst->instanceName = $name;
            self::$instances[$name] = $inst;
        }
        return $inst;
    }

    /**
     * @param $statement
     * @param array $params
     * @param array $options
     * @return clsPDOStatement|$this
     */
    public function run($statement = null, array $params = [], array $options = [])
    {
        if ($statement === null && $this->batch !==null) {
            $this->insertBatch();
            $this->batch = null;
            return $this;
        } else {
            $statement = $statement ? : implode(' ', $this->clauses);
            $params = $params ? : $this->params;
            $statement = preg_replace('#(\s+)#', ' ', $statement);
            $this->last = [
                'statement' => $statement,
                'params' => $params,
            ];
            $PDOStatement = $this->prepare($statement, $options);
            $PDOStatement->execute($params);
            $this->lastPDOStatement = $PDOStatement->PDOStatement();
            $this->clauses = $this->params = [];
            return $PDOStatement;
        }
    }

    public function printDebugInfo()
    {
        $this->lastPDOStatement->debugDumpParams();
    }


    /**
     * @param string $clause
     * @param array $params
     * @return $this
     */
    public function addClause($clause, array $params = [])
    {
        if (is_array($clause)) {
            $this->clauses = array_merge($this->clauses, $clause);
        } else {
            $this->clauses[] = $clause;
        }
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function addClauses(array $clauses)
    {
        foreach ($clauses as $key => $val) {
            if (is_int($key)) {
                $this->addClause($val);
            } elseif (is_array($val)) {
                $this->addClause($key, $val);
            } else {
                mysql::setError("Illegal clauses: " . json_encode($clauses));
            }
        }
        return $this;
    }

    /**
     * last executed
     * @return mixed
     */
    public function last()
    {
        return $this->last;
    }

    /**
     * @param $table
     * @param string|array $fields
     * @param array $clauses
     * @return $this
     */
    public function select($table, $fields = '*', array $clauses = [])
    {
        $field = $this->makeFields($fields);
        $clause = "SELECT $field FROM $table";
        $this->addClause($clause);
        $this->addClauses($clauses);
        return $this;
    }

    /**
     * @param string $table
     * @param array|null $data
     * @param string $option
     * @return $this
     */
    public function insert($table, array $data = null, $option = self::OPT_EMPTY)
    {
        return $this->append('INSERT', $table, $data, $option);
    }

    /**
     * @param $table
     * @param array $data
     * @param string $option
     * @return $this
     */
    public function replace($table, array $data, $option = self::OPT_EMPTY)
    {
        return $this->append('REPLACE', $table, $data, $option);
    }

    /**
     * @param $table
     * @param array|string $predicate
     * @return $this
     */
    public function update($table,  $predicate)
    {
        if (is_array($predicate)) {
            $fields = array_keys($predicate);
            $values = array_values($predicate);
            $predicate = $this->assemble($fields, "`%s`=?");
        } else {
            $values = [];
        }
        $clause = "UPDATE $table SET $predicate";
        return $this->addClause($clause, $values);
    }

    /**
     * @param array|string|null $fields
     * @param array $values
     * @return $this
     */
    public function values($fields, array $values)
    {
        $this->batch = count($this->params);
        $clause = [
            'VALUES' => $fields
        ];
        $params = [
            'VALUES' => $values
        ];
        return $this->addClause($clause, $params);
    }

    /**
     * @param array|string $update
     * @param array $params
     * @return $this
     */
    public function onDuplicateKey($update, array $params = [])
    {
        $clause = "ON DUPLICATE KEY UPDATE ";
        if (is_array($update)) {
            $buffer = [];
            foreach ($update as $key => $item) {
                if (is_int($key)) {
                    $buffer[] = "`$item`=VALUES(`$item`)";
                } else {
                    $buffer[] = "`$key`='$item'";
                }
            }
            $clause .= implode(',', $buffer);
        } else {
            $clause .= $update;
        }
        return $this->addClause($clause, $params);
    }

    /**
     * @param $table
     * @param $conditions
     * @return $this
     */
    public function delete($table, $conditions)
    {
        $clause = "DELETE FROM $table";
        $this->addClause($clause);
        if ($conditions) {
            $this->where($conditions);
        }
        return $this;
    }

    public function show($express)
    {
        $clause = "SHOW $express";
        $this->addClause($clause);
        return $this;
    }

    /**
     * @param string $table new table name
     * @param string $template create table like the template
     */
    public function create($table, $template)
    {
        $template = '`'.$template.'`';
        $clause = "CREATE TABLE IF NOT EXISTS `$table` LIKE $template";
        $this->run($clause);
    }

    /**
     * @param string|array $table
     */
    public function drop($table)
    {
        if (is_array($table)) {
            $sql = "DROP TABLES $table";
        } else {
            $sql = "DROP TABLE $table";
        }
        $this->exec($sql);
    }

    /**
     * @param array|string $conditions Will be combined with 'AND'
     * @param array|string|null $_ Will be combined with 'OR'
     * @return $this
     */
    public function where($conditions, $_ = null)
    {
        $conds = func_get_args();
        $buffer = [];
        $params = [];
        foreach ($conds as $cond) {
            list ($_cond, $_params) = $this->makeConditions($cond);
            $buffer[] = $_cond;
            $params = array_merge($_params);
        }
        $condition = implode(' OR ', $buffer);
        $clause = "WHERE $condition";
        return $this->addClause($clause, $params);
    }

    /**
     * @param array $set
     * @return $this
     */
    public function in(array $set)
    {
        list($holders, $params) = $this->makeValues($set);
        return $this->addClause("in ($holders)", $params);
    }

    /**
     * @param string $order
     * @return $this
     */
    public function orderBy($order)
    {
        return $this->addClause("ORDER BY $order");
    }

    /**
     * @param $fields
     * @return $this
     */
    public function groupBy($fields)
    {
        return $this->addClause("GROUP BY $fields");
    }

    /**
     * @param int $num
     * @param int $offset
     * @return $this
     */
    public function limit($num = 1, $offset = 0)
    {
        $num = intval($num);
        $offset = intval($offset);
        return $this->addClause("LIMIT $offset,$num");
    }

    /**
     * @param null|int|string $field column name or offset
     * @return mixed
     */
    public function fetch($field = null)
    {
        return $this->exe('fetch', func_get_args());
    }

    /**
     * @param null|int|string $keyField
     * @param null|int|string $valField
     * @return mixed
     */
    public function fetchAll($keyField = null, $valField = null)
    {
        return $this->exe('fetchAll', func_get_args());
    }

    public function insertId($name = null)
    {
        $this->run();
        return $this->PDO->lastInsertId($name);
    }

    public function rowCount()
    {
        $res = $this->run();
        $rowCount = ($res instanceof clsPDOStatement) ? $res->rowCount() : $this->rowCount;
        return $rowCount;
    }

    public static function setError($info)
    {
        $traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $referer = '';
        foreach ($traces as $trace) {
            if ($trace['class'] != __CLASS__) {
                $referer = @sprintf('%s-%s, line %d of %s', $trace['class'], $trace['function'], $trace['line'], $trace['file']);
                break;
            }
        }
        if (!is_array($info)) {
            $info = [$info];
        }
        $message = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $referer);
        foreach ($info as $key => $val) {
            $msg = json_encode($val);
            $flags = str_split($msg, self::MSG_LENGTH_LIMIT);
            $last = array_pop($flags);
            $first = array_shift($flags);
            $mid = $flags ? ' ... ' : '';
            $message .= "$key: $first$last$mid\n";
        }
        \output::debugFile(self::$errorLog, $message);
    }

    public static function setErrorLogPath($path = null)
    {
        if ($path) {
            self::$errorLog = $path;
        } else {
            $path = \config::load('system', 'file', 'mysql-error', false);
            if ($path === false) {
                $path = \config::load('system', 'path', 'log', false);
                $path = $path ? $path . '/mysql-error.log' : false;
            }
            if ($path) {
                self::$errorLog = $path;
            }
        }
    }

    private function append($method, $table, array $data = null, $option)
    {
        $table = $this->makeFields($table);
        if ($data === null) { //set data later
            $clause = "$method $option INTO $table";
            $this->addClause($clause);
        } else {
            $fields = array_keys($data);
            $values = array_values($data);
            $field = $this->makeFields($fields);
            list($value, $params) = $this->makeValues($values);
            $clause = "$method $option INTO $table ($field) VALUES ($value)";
            $this->addClause($clause, $params);
        }
        return $this;
    }

    private function insertBatch()
    {
        static $maxLength;
        if (empty($maxLength)) { //insert batch
            $maxLength = $this->query("SHOW VARIABLES LIKE 'max_allowed_packet'")->fetch(0);
        }
        $clauses = $this->clauses;
        $params = $this->params;
        $fields = $clauses['VALUES'];
        $values = $params['VALUES'];
        $field = $fields ? '(' . $this->makeFields($fields) . ')' : '';
        $cntFields = count($fields);
        $coreLength = strlen(json_encode($clauses));
        $holders = array_fill(0, $cntFields, '?');
        $holder = '(' . implode(',', $holders) . ')';
        $_values = [];
        $_length = $coreLength;
        $rowCount = 0;
        foreach ($values as $value) {
            $_length += strlen(json_encode($value)) + $cntFields * 3 + 2;
            $_values[] = $value;
            if ($_length > $maxLength) {
                $next = array_pop($_values);
                $_holders = array_fill(0, count($_values), $holder);
                $clauses['VALUES'] = "$field VALUES " . implode(',', $_holders);
                $statement = implode(' ', $clauses);
                $params['VALUES'] = call_user_func_array('array_merge', $_values);
                array_splice($params, $this->batch, 1, $params['VALUES']);
                $rowCount += $this->run($statement, $params)->rowCount();
                $_values = [$next];
                $_length = $coreLength;
            }
        }
        if ($_values) {
            $_holders = array_fill(0, count($_values), $holder);
            $clauses['VALUES'] = "$field VALUES " . implode(',', $_holders);
            $statement = implode(' ', $clauses);
            $params['VALUES'] = call_user_func_array('array_merge', $_values);
            array_splice($params, $this->batch, 1, $params['VALUES']);
            $rowCount += $this->run($statement, $params)->rowCount();
        }
        $this->rowCount = $rowCount;
        return $this;
    }

    private function makeFields($fields)
    {
        if (!is_array($fields)) {
            return $fields;
        }
        $buffer = [];
        foreach ($fields as $field) {
            $buffer[] = sprintf('`%s`', $field);
        }
        return implode(',', $buffer);
    }

    /**
     * @param array $data
     * @return array [placeholders, data]
     */
    private function makeValues(array $data)
    {
        $cnt = count($data);
        $holders = array_fill(0, $cnt, '?');
        $ret = [
            implode(',', $holders),
            $data,
        ];
        return $ret;
    }

    private function makeConditions($conditions)
    {
        if (!is_array($conditions)) {
            return [$conditions, []];
        }
        $buffer = [];
        $params = [];
        foreach ($conditions as $key => $val) {
            if (is_int($key)) {
                $buffer[] = $val;
            } elseif (is_array($val)) {
                $buffer[] = $key;
                $params = array_merge($params, $val);
            } else {
                $buffer[] = "`$key`=?";
                $params[] = $val;
            }
        }
        $condition = $this->assemble($buffer, '(%s)', ' AND ');
        return [$condition, $params];
    }

    private function assemble(array $input, $format, $hinge = ',')
    {
        $buffer = [];
        foreach ($input as $key => $val) {
            if (is_int($key)) {
                $buffer[] = sprintf($format, $val);
            } else {
                $buffer[] = sprintf($format, $key, $val);
            }
        }
        return implode($hinge, $buffer);
    }

    private function exe($method, $argus = [])
    {
        $statement = implode(' ', $this->clauses);
        $clsPDOStatement = $this->run($statement, $this->params);
        $this->clauses = $this->params = [];
        return call_user_func_array([$clsPDOStatement, $method], $argus);
    }

}

class clsPDO
{
    /**
     * @var \PDO
     */
    protected $PDO;

    public function __construct($dsn, $username, $password, $options)
    {
        try {
            $this->PDO = new \PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            $this->PDO = false;
            $info = [
                'Message' => $e->getMessage(),
            ];
            mysql::setError($info);
        }
    }

    public function close()
    {
        $this->PDO = null;
    }

    public function prepare($statement, $options = [])
    {
        return $this->callPDOMethod('prepare', func_get_args());
    }

    public function query($statement)
    {
        return $this->callPDOMethod('query', func_get_args());
    }

    public function exec($statement)
    {
        return $this->callPDOMethod('exec', func_get_args());
    }

    public function rowCount()
    {
        return $this->callPDOMethod('rowCount');
    }

    public function lastInsertId($name = null)
    {
        return $this->callPDOMethod('lastInsertId', func_get_args());
    }

    /**
     * @param $method
     * @param $argus
     * @return clsPDOStatement|bool
     */
    private function callPDOMethod($method, $argus = [])
    {
        if ($this->PDO instanceof \PDO) {
            try {
                $res = call_user_func_array([$this->PDO, $method], $argus);
            } catch (\PDOException $e) {
                $res = false;
            }
        } else {
            $res = false;
        }
        if ($res === false) {
            $info = [
                'ErrorInfo' => $this->PDO instanceof \PDO ? $this->PDO->errorInfo() : 'Illegal PDO instance.',
                'Method' => $method,
                'Argus' => $argus,
            ];
            if (isset ($e) && $e instanceof \PDOException) {
                $info['Message'] = $e->getMessage();
            }
            mysql::setError($info);
        }
        $res = new clsPDOStatement($res);
        return $res;
    }
}

class clsPDOStatement
{
    /**
     * @var \PDOStatement
     */
    private $PDOStatement;

    public function __construct($PDOStatement)
    {
        $this->PDOStatement = $PDOStatement;
    }

    /**
     * @return \PDOStatement
     */
    public function PDOStatement()
    {
        return $this->PDOStatement;
    }

    public function execute(array $params = null)
    {
        return $this->callPDOStatementMethod('execute', func_get_args());
    }

    /**
     * @param null|string|int $field column name or offset
     * @return bool|mixed|null
     */
    public function fetch($field = null)
    {
        $res = $this->callPDOStatementMethod('fetch');
        if ($field) {
            $field = is_int($field) ? ($field -1) : $field; // offset start from 1
            $res = arrayFetch($res, $field);
        }
        return $res;
    }

    public function fetchAll($keyField = null, $valField = null)
    {
        $res = $this->callPDOStatementMethod('fetchAll');
        $keyField = is_int($keyField) ? ($keyField - 1) : $keyField;
        $valField = is_int($valField) ? ($valField - 1) : $valField;
        if ($keyField === null) {
            if ($valField === null) {
                $ret = $res;
            } else {
                $ret = [];
                foreach ($res as $row) {
                    $ret[] = arrayFetch($row, $valField);
                }
            }
        } else {
            $ret = [];
            foreach ($res as $row) {
                $key = arrayFetch($row, $keyField);
                $val = $valField === null ? $row : arrayFetch($row, $valField);
                $ret[$key] = $val;
            }
        }
        return $ret;
    }

    public function rowCount()
    {
        return $this->callPDOStatementMethod('rowCount');
    }

    private function callPDOStatementMethod($method, $argus = [])
    {
        if (!$this->PDOStatement instanceof \PDOStatement) {
            return false;
        }
        try {
            $res = call_user_func_array([$this->PDOStatement, $method], $argus);
        } catch (\PDOException $e) {
            $res = false;
        }
        if ($res === false && $this->PDOStatement->errorCode() != '00000') {
            $info = [
                'ErrorInfo' => $this->PDOStatement->errorInfo(),
                'SQL' => $this->PDOStatement->queryString,
                'Method' => $method,
                'Argus' => $argus,
            ];
            if (isset ($e) && $e instanceof \PDOException) {
                $info['Message'] = $e->getMessage();
            }
            mysql::setError($info);
        }
        return $res;
    }

}
