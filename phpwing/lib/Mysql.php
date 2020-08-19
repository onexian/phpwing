<?php

/**
 * mysql 链接类
 * User: wx
 * Date: 2018/12/7
 * Time: 18:23
 */
namespace wing\lib;


class Mysql
{

    private $db_host;         // 连接地址
    private $db_name;         // 数据库名
    private $db_port;         // 连接端口
    private $db_charset;      // 数据库字符集
    private $db_username;     // 连接名
    private $db_password;     // 连接密码
    public $db_tablename;    // 表名
    public $pk = 'id';       // 表主键
    public $db_conn;         // 数据库连接
    public $connection = 'master';
    public $resource;        // 当前返回的资源
    public $sql;             // 当前执行的 SQL 语句
    public $late_time = 3;   // 查询多少秒为慢查询

    public function getconn()
    {
        // 连接数据库
        $this->db_conn = mysqli_connect(
            $this->db_host,
            $this->db_username,
            $this->db_password,
            $this->db_name,
            $this->db_port
        );

        if (!$this->db_conn) {

            $msg = 'MYSQL数据库连接失败 mysqli_connect_errno：' . mysqli_connect_errno() . '；mysqli_connect_error：' . mysqli_connect_error();
            Log::save('error', $msg, __FILE__, __LINE__);
            die($msg);
        }

        // 设置字符集
        if (!$this->db_charset) {
            $charset = "utf8";
        } else {
            $charset = $this->db_charset;
        }

        $char = mysqli_set_charset($this->db_conn, $charset);

        if (!$char) {

            $msg = 'charset设置错误，请输入正确的字符集名称 mysqli_errno：' . mysqli_errno($this->db_conn) . '；mysqli_error：' . mysqli_error($this->db_conn);
            Log::save('error', $msg, __FILE__, __LINE__);
            die($msg);
        }

        $db = mysqli_select_db($this->db_conn, $this->db_name);
        if (!$db) {

            $msg = '未找到数据库，请输入正确的数据库名称 mysqli_errno：' . mysqli_errno($this->db_conn) . '；mysqli_error：' . mysqli_error($this->db_conn);
            Log::save('error', $msg, __FILE__, __LINE__);
            die($msg);
        }

        return $this->db_conn;
    }

    public function __construct($conn = null)
    {
        //构造方法赋值
        $config = Config::get('database.mysql');
        if($conn) $this->connection = $conn;

        $config = $config[$this->connection] ?? [];
        if (empty($config)) {
            Log::save('error', "MYSQL[{$this->connection}]配置不存在，请在config/database" . EXT . '添加配置！', __FILE__, __LINE__);
            return false;
        }

        $this->db_host = $config['host'];
        $this->db_name = $config['dbname'];
        $this->db_port = $config['port'];
        $this->db_username = $config['username'];
        $this->db_password = $config['password'];
        $this->db_charset = $config['charset'] ?? "utf8";

        $this->getconn();
    }

    // 设置表名
    public function setTable($table)
    {
        $this->db_tablename = "`{$table}`";
        return $this;
    }

    /**
     * 获取一条数据
     * @param            $where
     * @param array|null $field
     * @return array
     */
    public function getOne($where, array $field = null)
    {

        list($where, $parameter) = $this->formatWhere($where);

        $sql = "SELECT * FROM {$this->db_tablename} WHERE {$where} LIMIT 1";

        if (!empty($field)) {
            $field = '`' . implode('`,`', $field) . '`';
            $sql = str_replace('*', $field, $sql);
        }
        $ret = $this->query($sql, $parameter);
        return $ret[0]??[];
    }

    /**
     * 数据查询
     *
     * @param null|array $field 字段
     * @param null|array|string $where 条件
     * @param array $parameter
     * @param null|array|string $order 排序 'id desc' ['id'=>'desc'], ['id'=>'desc', 'status'=>'desc']
     * @param string $limit
     * @return array|bool|\mysqli_result
     */
    public function select(array $field = null, $where = null, $parameter = [], $order = null, $limit = null)
    {
        $sql = "SELECT * FROM {$this->db_tablename}";
        if (!empty($field)) {
            $field = '`' . implode('`,`', $field) . '`';
            $sql = str_replace('*', $field, $sql);
        }

        list($where, $parameter) = $this->formatWhere($where);

        if ($order) {
            if (is_array($order)) {

                $orderBy = [];
                foreach ($order as $key => $val) {
                    if(is_numeric($key)){
                        continue;
                    }else{
                        $orderBy[] = "{$key} {$val}";
                    }
                }
                $orderByStr = implode(',', $orderBy);
            } else {
                $orderByStr = strval($order);
            }
            if($orderByStr){
                $sql .= " ORDER BY " . $orderByStr;
            }
        }

        if(is_string($limit) && $limit){
            $sql .= ' LIMIT ' . $limit;
        }

        return $this->query($sql, $parameter);
    }

    /**
     * 插入数据
     *
     * @param $data 数据数组
     * @param bool $replace
     * @return bool|int
     */
    public function insert($data, $replace = false)
    {
        if(empty($data)){
            return false;
        }

        $fields = [];
        $values = [];
        $parameter = [];
        $i = 0;
        foreach ($data as $field => $value) {
            $fields[] = $field;
            $parameter['v' . $i] = $value;
            $values[] = ':v' . $i;
            $i++;
        }

        if (empty($replace)) {
            $sqlPrefix = 'INSERT';
        } else {
            $sqlPrefix = 'REPLACE';
        }

        $keys = implode('`,`', $fields);
        $values = implode(",", $values);

        $sql = "{$sqlPrefix} INTO {$this->db_tablename}( `{$keys}` )VALUES( {$values} )";
        $this->query($sql, $parameter);
        return $this->affectedRows();

    }

    /**
     * 更新数据
     *
     * @param array $data 数据数组
     * @param $where 过滤条件
     * @return bool|int 受影响行数
     */
    public function update(array $data, $where)
    {

        if(empty($data)){
            return false;
        }

        $sets = [];
        $parameter = [];
        $i = 0;
        foreach ($data as $field => $value) {
            $kstr = '`' . $field . '`';
            $vstr = ':v' .$i;
            array_push($sets, $kstr . '=' . $vstr);

            $parameter['v' . $i] = $value;
            $i++;
        }

        list($where, $paramet) = $this->formatWhere($where);
        $parameter = array_merge($parameter, $paramet);

        $kav = implode(',', $sets);
        $sql = "UPDATE {$this->db_tablename} SET {$kav} WHERE {$where}";

        $this->query($sql, $parameter);
        return $this->affectedRows();
    }

    /**
     * 删除数据
     *
     * @param $where 过滤条件
     * @param int $limit 限制操作的条数
     * @return bool|int 受影响行数
     */
    public function delete($where, $limit = 0)
    {

        list($where, $parameter) = $this->formatWhere($where);

        $sql = "DELETE FROM {$this->db_tablename} WHERE {$where}";

        $limit = (int) $limit;
        if($limit){
            $sql .= ' LIMIT ' . $limit;
        }

        $this->query($sql, $parameter);
        return $this->affectedRows();
    }

    /**
     * 返回最近插入的数据的id
     *
     * @return bool|int
     */
    public function insertId(){
        if(!empty($this->db_conn)){
            return mysqli_insert_id($this->db_conn);
        }else{
            return false;
        }
    }

    /**
     * 返回影响的条数
     *
     * @return bool|int
     */
    public function affectedRows(){
        if(!empty($this->db_conn)){
            return mysqli_affected_rows($this->db_conn);
        }else{
            return false;
        }
    }

    /**
     * @param $sql
     * @param array $parameter
     * @return array|bool|\mysqli_result
     */
    public function query($sql, $parameter = [])
    {

        $sTime = microtime(true);

        // sql 过滤更替，获取 组成真正的 sql
        $sql = $this->bindParameter($sql, $parameter);

        $result = mysqli_query($this->db_conn, $sql);

        $eTime = microtime(true);
        $t = round($eTime - $sTime, 3);

        if (!$result) {
            Log::save('error', "SQL：{$sql}(error：" . mysqli_error($this->db_conn) . ")");
            return false;
        }

        $this->resource = $result;
        $this->sql = $sql;

        // 记录 sql
        if ($t > $this->late_time) {
            //慢查询，保存到 debug 日志文件
            Log::save('debug', "SQL: {$this->sql}(OK:{$t})");
        }
        Log::save('info', "SQL: {$this->sql}(OK:{$t})");

        if ($this->getSqlType($sql) == 'select') {
            $rows = [];
            while ($row = $this->fetch($result)) {
                $rows[] = $row;
            }
            if (!empty($result)) {
                mysqli_free_result($result);
            }
            return $rows;
        }

        return $result;

    }

    /**
     * 从资源中获取一行数据
     *
     * @param resource $result 提交查询后获得的资源，如果不传表示使用最近一次查询返回的资源
     * @return array|bool
     */
    public function fetch($result = null)
    {
        if (is_null($result)) {
            $result = $this->resource;
        }
        if (empty($result)) {
            return false;
        }
        return mysqli_fetch_array($result, MYSQLI_ASSOC);

    }

    private function formatWhere($where)
    {
        if(is_numeric($where)){
            $parameter['where'] = $where;
            $where = "`{$this->pk}`=:where";
        }elseif(is_array($where)){

            $_w = [];
            $i = 0;
            foreach($where as $name => $value){
                $kstr = '`' . $name . '`';

                if(is_array($value)){
                    $_w[] = "{$kstr} in (:w{$i})";
                    $parameter['w' . $i] = $value;
                }else{
                    $_w[] = "{$kstr} = :w{$i}";
                    $parameter['w' . $i] = $value;
                }
                $i++;
            }
            $where = implode(' and ', $_w);
        }
        return [$where, $parameter];
    }

    /**
     * 获得SQL的类型
     *
     * @param string $sql 字符串
     * @return array|string
     */
    private function getSqlType($sql)
    {
        $sql = str_replace("\n", ' ', $sql);
        $sqlType = explode(' ', $sql);
        $sqlType = strtolower(trim($sqlType[0]));
        return $sqlType;
    }

    /**
     * sql 过滤更替
     *
     * @param $sql
     * @param $parameter
     * @return mixed|string
     */
    private function bindParameter($sql, $parameter)
    {
        $sql = trim($sql);

        // 注意替换结果尾部加一个空格
        $sql = preg_replace("/:([a-zA-Z0-9_\-\x7f-\xff][a-zA-Z0-9_\-\x7f-\xff]*)\s*([,\)]?)/", "\x01\x02\x03\\1\x01\x02\x03\\2 ", $sql);
        $find = [];
        $replacement = [];
        foreach ($parameter as $key => $value) {
            $find[] = "\x01\x02\x03$key\x01\x02\x03";
            if (is_array($value)) {
                foreach ($value as &$v) {
                    $v = "'" . $this->escapeString($v, $this->db_conn) . "'";
                }
                unset($v);

                $replacement[] = implode(',', $value);
            } else {
                $repl = "'" . $this->escapeString($value, $this->db_conn) . "'";
                if(strstr($value,'`')){
                    $repl = trim($repl, "'");
                }
                $replacement[] = $repl;
            }
        }

        $sql = str_replace($find, $replacement, $sql);
        return $sql;
    }

    private function escapeString($value, $link)
    {
        return mysqli_real_escape_string($link, $value);
    }

    public function __destruct()
    {
        mysqli_close($this->db_conn);
    }
}