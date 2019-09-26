<?php
/**
 * User: wx
 * Date: 2018/12/7
 * Time: 18:24
 */

namespace lib\library;


class Pgsql
{

    public $db_host;         // 连接地址
    public $db_name;         // 数据库名
    public $db_port;         // 连接端口
    public $db_username;     // 连接名
    public $db_password;     // 连接密码
    public $db_tablename;    // 表名
    public $db_conn;         // 数据库连接
    public $resource;        // 当前返回的资源
    public $sql;             // 当前执行的 SQL 语句
    public $late_time = 3;   // 查询多少秒为慢查询
    public $find = false;    // 是否查询一条数据


    public function getconn()
    {
        // 连接数据库
        $this->db_conn = pg_connect("
            host={$this->db_host}
            port={$this->db_port}
            dbname={$this->db_name}
            user={$this->db_username}
            password={$this->db_password}
        ");

        if (!$this->db_conn) {

            $msg = 'PGSQL数据库连接失败 pg_last_error：' . pg_last_error();
            Log::save('error', $msg, __FILE__, __LINE__);
            die($msg);
        }

        return $this->db_conn;
    }

    public function __construct($conn = 'master')
    {
        //构造方法赋值
        $config = Config::get('database.pgsql');

        $config = $config[$conn] ?? [];
        if (empty($config)) {
            Log::save('error', "PGSQL[{$conn}]配置不存在，请在config/database" . EXT . '添加配置！');
            return false;
        }

        $this->db_host = $config['host'];
        $this->db_name = $config['dbname'];
        $this->db_port = $config['port'];
        $this->db_username = $config['username'];
        $this->db_password = $config['password'];

        $this->getconn();
    }

    /**
     * 设置表名
     *
     * @param string $table
     * @return $this
     */
    public function setTable($table)
    {
        $this->db_tablename = $table;
        return $this;
    }

    /**
     * 查询
     *
     * @param null|array|string $field 字段
     * @param null|array|string $where 条件
     * @param array $parameter 预加载数据
     * @param null|array|string $order 排序 'id desc' ['id'=>'desc'], ['id'=>'desc', 'status'=>'desc']
     * @param string $limit
     * @return array|bool|resource
     */
    public function select($field = null, $where = null, $parameter = [], $order = null, $limit = null)
    {
        $sql = "SELECT * FROM {$this->db_tablename}";
        if (!empty($field)) {
            $field = implode(',', $field);
            $sql = str_replace('*', $field, $sql);
        }
        if (!empty($where)) {

            if (is_numeric($where)) {
                $parameter['where'] = $where;
                $where = "id=:where";
            } elseif (is_array($where)) {

                $_w = [];
                $i = 0;
                foreach ($where as $name => $value) {
                    $kstr = $name;

                    if (is_array($value)) {
                        $_w[] = "{$kstr} in (:w{$i})";
                        $parameter['w' . $i] = $value;
                    } else {
                        $_w[] = "{$kstr} = :w{$i}";
                        $parameter['w' . $i] = $value;
                    }
                    $i++;
                }
                $where = implode(' and ', $_w);
            }

            $sql = $sql . ' WHERE ' . $where;
        }

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
     * @param array $data
     * @return bool|int
     */
    public function insert(array $data)
    {
        if (empty($data) && !is_array($data)) {
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

        $keys = implode(',', $fields);
        $values = implode(",", $values);

        $sql = "INSERT INTO {$this->db_tablename}( {$keys} )VALUES( {$values} )";
        $this->query($sql, $parameter);
        return $this->affectedRows();

    }

    /**
     * 更新数据
     *
     * @param array $data
     * @param int|array|string $where 过滤条件
     * @return bool|int 受影响条数
     */
    public function update(array $data, $where)
    {

        if (empty($data) && !is_array($data)) {
            return false;
        }

        $sets = [];
        $parameter = [];
        $i = 0;
        foreach ($data as $field => $value) {
            $kstr = $field;
            $vstr = ':v' . $i;
            array_push($sets, $kstr . '=' . $vstr);

            $parameter['v' . $i] = $value;
            $i++;
        }

        if (is_numeric($where)) {
            $parameter['where'] = $where;
            $where = "id=:where";
        } elseif (is_array($where)) {

            $_w = [];
            $i = 0;
            foreach ($where as $name => $value) {
                $kstr = $name;

                if (is_array($value)) {
                    $_w[] = "{$kstr} in (:w{$i})";
                    $parameter['w' . $i] = $value;
                } else {
                    $_w[] = "{$kstr} = :w{$i}";
                    $parameter['w' . $i] = $value;
                }
                $i++;
            }
            $where = implode(' and ', $_w);
        }

        $kav = implode(',', $sets);
        $sql = "UPDATE {$this->db_tablename} SET {$kav} WHERE {$where}";
        $this->query($sql, $parameter);
        return $this->affectedRows();
    }

    /**
     * 删除数据
     *
     * @param int|array|string $where 过滤条件
     * @return bool|int 受影响条数
     */
    public function delete($where)
    {

        if(is_numeric($where)){
            $parameter['where'] = $where;
            $where = "id=:where";
        }elseif(is_array($where)){

            $_w = [];
            $i = 0;
            foreach($where as $name => $value){
                $kstr = $name;

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

        $sql = "DELETE FROM {$this->db_tablename} WHERE {$where}";

        $this->query($sql, $parameter);
        return $this->affectedRows();
    }

    /**
     * 返回影响的条数
     *
     * @return bool|int
     */
    public function affectedRows()
    {
        if (!empty($this->resource)) {
            return pg_affected_rows($this->resource);
        } else {
            return false;
        }
    }

    /**
     * 获得SQL的类型
     *
     * @param $sql
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
     * 运行SQL
     *
     * @param $sql
     * @param array $parameter
     * @return array|bool|resource
     */
    public function query($sql, $parameter = [])
    {

        $sTime = microtime(true);

        // sql 过滤更替，获取 组成真正的 sql
        $sql = $this->bindParameter($sql, $parameter);
        $result = pg_query($this->db_conn, $sql);

        $eTime = microtime(true);
        $t = round($eTime - $sTime, 3);

        if (!$result) {
            Log::save('error', "PGSQL：{$sql}(error：" . pg_last_error($this->db_conn) . ")");
            return false;
        }

        $this->resource = $result;
        $this->sql = $sql;

        // 记录 sql
        if ($t > $this->late_time) {
            //慢查询，保存到 debug 日志文件
            Log::save('debug', "PGSQL: {$this->sql}(OK:{$t})");
        }
        Log::save('info', "PGSQL: {$this->sql}(OK:{$t})");

        if ($this->getSqlType($sql) == 'select') {

            $rows = [];
            while ($row = $this->fetch($result)) {
                // 是否查询一条数据
                if($this->find){
                    $rows = $row;
                    $this->find = false; // 还原设置，防止再次调用依然查询一条
                    break;
                }
                $rows[] = $row;
            }
            if (!empty($result)) {
                pg_free_result($result);
            }
            return $rows;
        }

        return $result;

    }

    /**
     * 从资源中获取数据
     *
     * @param null|resource $result 提交查询后获得的资源，如果不传表示使用最近一次查询返回的资源
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
        return pg_fetch_assoc($result);

    }

    /**
     * sql 过滤更替
     *
     * @param $sql
     * @param array $parameter
     * @return mixed|string
     */
    private function bindParameter($sql, $parameter = [])
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
                    $v = "'" . $this->escapeString($v) . "'";
                }
                unset($v);

                $replacement[] = implode(',', $value);
            } else {
                $replacement[] = "'" . $this->escapeString($value) . "'";
            }
        }

        $sql = str_replace($find, $replacement, $sql);
        return $sql;
    }

    /**
     * 转义 text/char 类型的字符串
     *
     * @param $value
     * @return string
     */
    private function escapeString($value)
    {
        return pg_escape_string($this->db_conn, $value);
    }

    public function __destruct()
    {
        pg_close($this->db_conn);
    }
}