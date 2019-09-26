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
    protected $conn = 'master';  // 使用那个配置
    protected $pk = 'id';        // 当前链接表的主键
    protected $db_tablename;     // 当前访问的表名
    protected $db_conn;          // 当前实例的库

    private $where;            // 当前查询的 where 语句
    private $field;            // 当前查询字段
    private $order;            // 当前查询排序
    private $limit;

    public function __construct()
    {
        $this->conn()->setTable();
    }

    public function conn($model = '')
    {
        $model = $model ? ucfirst($model) : ucfirst($this->db);

        switch ($model) {
            case 'Mysql':
                $connect = new library\Mysql();
                break;
            case 'Pgsql':
                $connect = new library\Pgsql();
                break;
        }

        $this->db_conn = $connect;
        return $this;
    }

    public function setTable()
    {
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

    public function field($field = null)
    {
        $this->field = $field;
        return $this;
    }

    public function order($order)
    {
        $this->order = $order;
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
        return $this->db_conn->select($this->field, $this->where, $params, $this->order, $this->limit);
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
     * @param array $data 数据数组
     * @return bool|int 受影响行数
     */
    public function update(array $data)
    {
        return $this->db_conn->update($data, $this->where);
    }

}
