<?php
/**
 * 数据库基类，可切换不同的数据库链接
 * User: wx
 * Date: 2018/12/6
 * Time: 11:08
 */

namespace lib;


class Model
{
    protected $db = 'Mysql';     // 需要链接的数据库
    protected $config = 'master';  // 使用那个配置
    protected $pk = 'id';        // 当前链接表的主键
    protected $db_tablename;     // 当前访问的表名
    protected $db_conn;          // 当前实例的库

    private $where;            // 当前查询的 where 语句
    private $field;            // 当前查询字段
    private $order;            // 当前查询排序
    private $group;            // 当前分组字段
    private $limit;

    public function __construct()
    {
        $this->conn()->setTable();
    }

    private function conn($model = '')
    {
        $model = $model ? ucfirst($model) : ucfirst($this->db);

        switch ($model) {
            case 'Mysql':
                $connect = new library\Mysql($this->config);
                break;
            case 'Pgsql':
                $connect = new library\Pgsql($this->config);
                break;
        }

        $this->db_conn = $connect;
        return $this;
    }

    public function setTable($db_tablename = null)
    {

        $this->db_tablename = $db_tablename ?? $this->db_tablename;

        if (!$this->db_conn || !$this->db_tablename) {
            $msg = '模型没有实例或没有设置操作的数据表';
            Debug::error($msg);
            throw new \Exception($msg);
        }

        $this->db_conn->setTable($this->db_tablename);
        return $this;
    }

    /**
     * 获取一条数据
     *
     * @param int|string|array $id
     * @param array|null $field
     * @return mixed
     */
    public function getOne($id = null, array $field = null)
    {
        $where = [];

        if ($id) {
            if (is_array($id)) {
                $key = array_keys($id)[0];
                $value = array_values($id)[0];
            } else {
                $key = $this->pk;
                $value = $id;
            }

            $where[$key] = $value;
        }

        return $this->where($where)
            ->field($field)
            ->limit(1)
            ->find()
            ->select();
    }

    public function where($where = null)
    {
        $this->where = $where;
        return $this;
    }

    public function field(array $field = null)
    {
        $this->field = $field;
        return $this;
    }

    public function order($order)
    {
        $this->order = $order;
        return $this;
    }

    public function group($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * 指定查询数量
     * @access public
     * @param mixed $offset 起始位置
     * @param mixed $length 查询数量
     * @return $this
     */
    public function limit($offset = 1, $length = null)
    {
        $this->limit = $this->db_conn->limit($offset, $length);
        return $this;
    }

    // 查询一条数据
    public function find()
    {
        $this->db_conn->find = true;
        return $this;
    }

    public function select($params = [])
    {
        $result = $this->db_conn->select($this->field, $this->where, $params, $this->order, $this->limit, $this->group);
        $this->field = null;$this->where = null;$this->order = null;$this->limit = null;$this->group= null;
        return $result;
    }

    /**
     * 插入数据
     *
     * @param array $data 数据数组
     * @return bool|int
     */
    public function insert(array $data)
    {
        return $this->db_conn->insert($data);
    }

    /**
     * 更新数据
     *
     * @param array $data
     * @return bool|int 受影响行数
     * @throws \Exception
     */
    public function update(array $data)
    {
        if($this->where === null){
            // 没设置更新条件 不更新
            $msg = '请设置更新条件!';
            Debug::debug($msg);
            if (Debug::check()) throw new \Exception($msg);
            return false;
        }

        $result = $this->db_conn->update($data, $this->where);
        $this->where = null;
        return $result;
    }

    public function delete($where, $limit = 0)
    {
        if($this->where){
            $where = $this->where;
        }
        if($this->limit){
            $limit = $this->limit;
        }
        $result = $this->db_conn->delete($where, $limit);
        $this->where = null;$this->limit = null;
        return $result;
    }

    public function insertId()
    {
        return $this->db_conn->insertId();
    }
}
