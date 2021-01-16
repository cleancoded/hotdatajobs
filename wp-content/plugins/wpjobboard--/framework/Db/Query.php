<?php
/**
 * Description of Query
 *
 * @author greg
 * @package 
 */

class Daq_Db_Query
{

    /**
     * List of registred Models in this query
     * @var array
     */
    protected $_registered = array();

    protected $_from = "";

    protected $_join = array();

    protected $_where = null;

    protected $_select = null;

    protected $_group = null;

    protected $_having = null;

    protected $_order = null;

    protected $_limit = null;

    public function __construct()
    {
        $this->select();
    }
    
    /**
     * Creates new instance of Daq_Db_Query
     * 
     * @param type $select string
     * @return Daq_Db_Query 
     */
    public static function create($select = "*")
    {
        $query = new self;
        $query->select($select);
        
        return $query;
    }

    protected function _quote($value)
    {
        if(is_int($value) or is_float($value)) {
            return $value;
        } elseif(is_array($value)) {
            $arr = array();
            foreach($value as $v) {
                $arr[] = "'".esc_sql($v)."'";
            }
            return join(", ", $arr);
        } else {
            return "'".esc_sql($value)."'";
        }

        
    }

    public function quoteInto($pattern, $value = null)
    {
        $quoted = $this->_quote($value);
        return str_replace("?", $quoted, $pattern);
    }

    /**
     * Select
     *
     * @param string $select
     * @return Daq_Db_Query
     */
    public function select($select = "*")
    {
        $this->_select = $select;
        return $this;
    }

    /**
     * From
     *
     * @param string $model Name of model class
     * @return Daq_Db_Query Provides fluent interface
     */
    public function from($model)
    {
        $this->_from = $model;
        return $this;
    }

    /**
     * Or Where
     *
     * @param string $where
     * @param string $var
     * @return Daq_Db_Query Provides fluent interface
     */
    public function orWhere($where, $var = null)
    {
        $this->_where(str_replace("?", $this->_quote($var), $where), "OR ");
        return $this;
    }

    /**
     * Where
     *
     * @param string $where
     * @param string $var
     * @return Daq_Db_Query Provides fluent interface
     */
    public function where($where, $var = null)
    {
        $this->_where(str_replace("?", $this->_quote($var), $where), "AND ");
        return $this;
    }

    protected function _where($where, $type)
    {
        if(strlen($this->_where)==0) {
            $type = "";
        }
        $this->_where .= $type." ".$where." ";
    }

    /**
     * Inner Join
     *
     * @param string $model model_alias.reference
     * @param string $with "JOIN table ON x=y AND $with"
     * @return Daq_Db_Query Provides fluent interface
     */
    public function join($model, $with = null)
    {
        $this->_join("INNER", $model, $with);
        return $this;
    }

    /**
     * Left Join
     *
     * @param string $model model_alias.reference
     * @param string $with "JOIN table ON x=y AND $with"
     * @return Daq_Db_Query Provides fluent interface
     */
    public function joinLeft($model, $with = null)
    {
        $this->_join("LEFT", $model, $with);
        return $this;
    }

    protected function _join($type, $model, $with)
    {
        $this->_join[] = array("type"=>$type, "model"=>$model, "with"=>$with);
        return $this;
    }

    /**
     * Limit
     *
     * @param int $count
     * @param int $offset
     * @return Daq_Db_Query Provides fluent interface
     */
    public function limit($count, $offset = 0)
    {
        $this->_limit = " LIMIT ".$count." OFFSET ".$offset;
        return $this;
    }

    /**
     * Limit Page
     *
     * @param int $page Minimum 0
     * @param int $count
     * @return Daq_Db_Query Provides fluent interface
     */
    public function limitPage($page, $count)
    {
        $page = $page-1;
        if($page < 0) {
            $page = 0;
        }
        $this->_limit = " LIMIT ".$count." OFFSET ".(($page)*$count);
        return $this;
    }

    /**
     * Group By
     *
     * @param string $group
     * @return Daq_Db_Query Provides fluent interface
     */
    public function group($group)
    {
        $this->_group = " GROUP BY ".$group;
        return $this;
    }

    /**
     * Having
     *
     * @param string $have
     * @return Daq_Db_Query Provides fluent interface
     */
    public function having($have)
    {
        $this->_having = " HAVING ".$have;
        return $this;
    }

    /**
     * Order By
     *
     * @param string $order
     * @return Daq_Db_Query Provides fluent interface
     */
    public function order($order)
    {
        $this->_order = " ORDER BY ".$order;
        return $this;
    }

    /**
     * Returns query as a string;
     *
     * @return string
     */
    public function toString()
    {
        $modelList = array();

        list($model, $alias) = explode(" ", trim($this->_from));
        $modelList[$alias] = new $model;

        $query = " FROM `".$modelList[$alias]->tableName()."` AS `".$alias."` ";
        foreach($this->_join as $join) {

            list($temp, $alias) = explode(" ", trim($join['model']));
            list($link, $method) = explode(".", $temp);
            
            $ref = $modelList[$link]->getReference($method);

            $cache = $ref['foreign'];
            $modelList[$alias] = new $cache;

            $query .= $join["type"]." JOIN `".$modelList[$alias]->tableName();
            $query .= "` AS `".$alias."` ON `".$link."`.`".$ref['localId'];
            $query .= "`=`".$alias."`.`".$ref['foreignId']."` ";

            if($join['with'] !== null) {
                $query .= "AND ".$join['with']. " ";
            }
        }

        $select = explode(",", $this->_select);
        $select = array_map("trim", $select);
        $selectList = array();
        foreach($select as $field) {
            if($field == "*") {
                // put all fields
                foreach($modelList as $model => $value) {
                    foreach($modelList[$model]->getFieldNames() as $f) {
                        $selectList[] = $model.".".$f;
                    }
                }
            } elseif(stripos($field, ".*")) {
                list($model) = explode(".", $field);
                foreach($modelList[$model]->getFieldNames() as $f) {
                    $selectList[] = $model.".".$f;
                }
            } else {
                $selectList[] = $field;
            }
        }

        $select = array();
        foreach(array_unique($selectList) as $element) {
            if(stripos($element, " AS ")) {
                $select[] = $element;
            } else {
                $select[] = $element." AS ".str_replace(".", "__", $element);
            }
        }
        $select = join(", ", $select);

        foreach($modelList as $key => $model) {
            $find = $key.".";
            $repl = $key.".".$key."__";
            //$select = str_replace($find, $repl, $select);
        }

        if($this->_where !== null) {
            $query .= "WHERE ".$this->_where;
        }
        $query = "SELECT ".$select.$query.$this->_group;
        $query.= $this->_having.$this->_order.$this->_limit;

        $this->_registered = $modelList;
        return $query;
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function execute()
    {
        $rows = $this->fetchAll();
        $scheme = array(
            "_helper" => new stdClass,
        );
        $cache = $scheme;

        foreach($this->_registered as $k => $v) {
            $class = get_class($v);
            $scheme[$class] = array();
            $cache[$k] = $class;
        }

        $objList = array();
        foreach($rows as $row) {
            $temp = $scheme;
            $temp['_helper'] = new stdClass();
            foreach($row as $k => $v) {
                if(stripos($k, "__")) {
                    list($model, $field) = explode("__", $k);
                    $key = $cache[$model];
                    $temp[$key][$field] = $v;
                }  else {
                    $temp["_helper"]->$k = $v;
                }
            }
            $objList[] = $temp;
        }

        list($model, $alias) = explode(" ", trim($this->_from));

        $realList = array();
        foreach($objList as $obArr) {

            $object = new $model();
            $object->doExists(true);
            $object->fromArray($obArr[$model]);

            foreach($obArr as $obClass => $arr) {

                if($obClass == $model) {
                    continue;
                }
                if($obClass == "_helper") {
                    $object->helper = $arr;
                    continue;
                }

                $tObj = new $obClass;
                $tObj->doExists(true);
                $tObj->fromArray($arr);
                $object->addRef($tObj);
            }

            $realList[] = $object;
        }

        return $realList;
    }

    public function getDb()
    {
        return Daq_Db::getInstance()->getDb();
    }

    public function fetchColumn()
    {
        $db = $this->getDb();
        $query = $this->toString();

        $col = $db->get_col($query);
        
        if(isset($col[0])) {
            $result = $col[0];
        } else {
            $result = null;
        }

        if($db->last_error) {
            throw new Daq_Db_Exception($db->last_error, 0, $query);
        }

        return $result;
    }

    public function fetch($row = 0)
    {
        $db = $this->getDb();
        $query = $this->toString();
        
        $result =$db->get_row($query, $row);

        if($db->last_error) {
            throw new Daq_Db_Exception($db->last_error, 0, $query);
        }

        return $result;
    }

    public function fetchAll()
    {
        $db = $this->getDb();
        $query = $this->toString();

        $result = $db->get_results($query);
        if($db->last_error) {
            throw new Daq_Db_Exception($db->last_error, 0, $query);
        }

        return $result;
    }

    public function get($part)
    {
        $prop = "_".$part;
        if(property_exists($this, $prop)) {
            return $this->$prop;
        }
    }

}

?>