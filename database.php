<?php

class Database {
	
    private $host;
    private $db;
    private $username;
    private $password;
    
    private $select;
    private $from;
    private $where;
    private $orderBy;
    private $limit;
    private $join;
    private $result;
    
    public function __construct($db, $host, $username, $password) {
        $this->host     = $host;
        $this->db       = $db;
        $this->username = $username;
        $this->password = $password;
    }
    private function connect() {
        $link = mysqli_connect($this->host, $this->username, $this->password, $this->db) or die('Error ' . mysqli_error($link));
        return $link;
    }

    public function queryRun($query) {
        $link = $this->connect();
        $result = $link->query($query) or die('Error in the consult..' . mysqli_error($link));
        $link->close();
        $this->result = $result;
        return $this;
    }
    
    private function resetVariables() {
        $this->select	= NULL;
        $this->from     = NULL;
        $this->where	= NULL;
        $this->orderBy	= NULL;
        $this->limit	= NULL;
        $this->join     = NULL;
    }

    public function select($arg) {
        $this->select = $arg;
        return $this;
    }

    private function getSelect() {
        if(!empty($this->select)) {
            return "SELECT " . $this->select;
        } else {
            return "SELECT *";
        }
    }

    public function from($arg) {
        $this->from = $arg;
        return $this;
    }

    private function getFrom() {
        if(!empty($this->from)) {
            return " FROM `" . $this->from . "`";
        }
    }

    public function where($arg1, $arg2 = FALSE) {
        if(is_array($arg1) && $arg2 == FALSE) {
            foreach($arg1 as $key => $val) {
                $this->where[$key] = $val;
            }
        } else if(is_string($arg1) && is_string($arg2)) {
            $this->where[$arg1] = $arg2;
        }
        return $this;
    }

    private function getWhere() {
        if(!empty($this->where) && is_array($this->where)) {
            $sql = " WHERE";

            foreach($this->where as $key => $val) {
                $sql .= " `$key` = '$val' AND";
            }

            return rtrim($sql, ' AND');
        }
    }

    public function order_by($order_by, $order) {
        $this->orderBy = array('order_by' => $order_by, 'order' => $order);
        return $this;
    }

    private function getOrderBy() {
        if(!empty($this->orderBy) && is_array($this->orderBy)) {
            return $sql = " ORDER BY " . $this->orderBy['order_by'] . " " . strtoupper($this->orderBy['order']);
        }
    }

    public function limit($arg) {
        $this->limit = $arg;
        return $this;
    }

    private function getLimit() {
        if(!empty($this->limit)) {
            return $sql = " LIMIT " . $this->limit;
        }
    }

    public function join($joinTable, $joinOn, $joinType = FALSE) {
        $this->join[] = array('joinTable' => $joinTable, 'joinOn' => $joinOn, 'joinType' => $joinType);
        return $this;
    }

    private function getJoin() {
        if(!empty($this->join)) {
            $sql = "";
            foreach($this->join as $item) {
                if($item['joinType'] != FALSE) {
                        $sql .= " " . strtoupper($item['joinType']);
                }

                $sql .= " JOIN " . $item['joinTable'] . " ON " . $item['joinOn'];
            }
            return $sql;
            /*
            $sql = "";
            if($this->join['joinType'] != FALSE) {
                $sql = " " . strtoupper($this->join['joinType']);
            }

            return $sql .= " JOIN " . $this->join['joinTable'] . " ON " . $this->join['joinOn'];
            */
        }
    }

    private function buildQuery($table = FALSE, $where = FALSE) {
        if($table != FALSE) {
            $this->from($table);
        }

        if($where != FALSE) {
            $this->where($where);
        }

        $sql = $this->getSelect();

        $sql .= $this->getFrom();

        $sql .= $this->getJoin();

        $sql .= $this->getWhere();

        $sql .= $this->getOrderBy();

        $sql .= $this->getLimit();

        $this->resetVariables();
        return $sql;
    }

    public function get($table = FALSE) {
        $sql = $this->buildQuery($table);
        $result = $this->queryRun($sql);
        return $result;
    }

    public function get_where($table = FALSE, $where = FALSE) {
        if($table == FALSE || $where == FALSE) {
            trigger_error('Some error message');
            die();
        }

        $sql = $this->buildQuery($table, $where);
        $result = $this->queryRun($sql);
        return $result;
    }

    public function row_array() {
        return $this->result->fetch_assoc();
    }

    public function result_array() {
        return $this->result->fetch_all(MYSQLI_ASSOC);
        /*$data = [];
        while ($row = $this->result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;*/
    }

    public function row() {
        return $this->result->fetch_object();
    }

    public function num_rows() {
        return $this->result->num_rows;
    }

    public function query($query) {
        $result = $this->queryRun($query);
        return $result;
    }
	
}

?>