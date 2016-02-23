<?php
/*$customer = ORM::factory($customerTable);
	$customer->timestamp(array(
		'addTime','lastEditTime'
	));
	
	//V
	$values = array(
		'name'=>1,
		'area'=>2,
		'from'=>3
);
$customer->left_join('log','l')->on('l.customerId','id')->on('df','sdf');
$customer->right_join('log','l')->on('l.customerId','id')->on('df','sdf');
$customer->where('sd','in','3,4,5')->where('dd.sdfsd','in',array(456,'dsf'));
$resutl = $customer->values($values)
->order_by('ut.id')
->group_by('23','f','tt.sdf')
->having('df','=',456)->having('df.df','in',array(456,'dsf'))->having('df.df','in','456,"dsf"')
->limit(2)->select(array('23','f','sdf'))->set_pack('pack1');
$customer->select()->set_pack('pack2')->union('pack1');
//echo $customer->sql();
$customer->set_pack('pack3');
$customer->where('d','in',$customer->get_pack('pack3'))->select();
echo $customer->sql();
var_dump($customer->get_pack());
exit;*/

class MYSQL{
	
	private $error;
	
	private $model;
	
	private $alias = '`t1`';
    
    private $pre = '';
	
	private $id;
	
	private $timestamps = array();
	
	private $values = array();
	
	private $wheres = array();
	
	private $where_open = false;
	
	private $join = array();
	
	private $order_by = array();
	
	private $group_by = array();
	
	private $having = array();
	
	private $limit,$offset;
	
	private $sql;
	
	private $sql_packs = array();
	
	private static $last_sql;
	
	private $action;
	
	private $as_array = false;
	
	private $as_array_rule;
	
	private $result = false;
	
	private $insert_id = false;
	
	public static function table_prefix($pre = null){
        if ($pre){
            $this->pre = $pre;
        }
        return $this->pre;
	}
        
    public static function last_sql(){
        return self::$last_sql;
    }
        
	private static function instance($model = null,$extra = null){
		
		$_instance = new self;
                
                if (0 < preg_match('/^\d+$/',$extra)){
			$_instance->id = $extra;
		}elseif(is_string($extra)){
			$_instance->alias = '`'.$extra.'`';
		}
		
		if (!is_string($model)){
			return $_instance->error('模型字符串不正确');
		}else{
			$_instance->model = $model;
		}
		
		return $_instance;
	}
	
	public function error($message = null){
		if (is_string($message) && !empty($message)){
			$this->error = $message;
			if (!headers_sent()){
				header('content-type:text/html;charset=utf-8');
			}
			echo '<p>ORM error::'.$this->error."</p>\n";
			return $this;
		}
		return $this->error;
	}
	
	public static function factory($model = null,$extra = null){
		$model = self::table_prefix().$model;
		return self::instance($model,$extra);
	}
	
	public function timestamp($timestamps = array(),$type = true){
		if (!is_array($timestamps) || empty($timestamps)){
			return $this->error('时间字段数组为空');
		}
		if ($type){
			$stamp = 'unix_timestamp()';
		}else{
			$stamp = 'now()';
		}
		foreach ($timestamps as $timestamp){
			$this->timestamps['`'.$timestamp.'`'] = $stamp;
		}
		return $this;
	}
	
	public function values($values = array(),$addslashes = true){
		if (!is_array($values) || empty($values)){
			return $this->error('字段数组为空');
		}
		foreach ($values as $field=>$value){
                        if ($addslashes)
                            $value = addslashes($value);
                        if (($value == 'null') || preg_match('/\+[ ]*\d+$/i',$value))
                            $this->values['`'.$field.'`'] = $value;
                        else
                            $this->values['`'.$field.'`'] = "'".$value."'";
		}
		return $this;
	}
	
	public function where($field,$compare,$value,$type = 'and'){
		//var_dump(func_get_args());
		if (!is_string($field) || !is_string($compare)){
			return $this;
		}
		if (empty($field) || empty($compare)){
			return $this;
		}
		
		if (false === strpos($field,'.')){
			list($alias,$field) = array($this->alias,$field);
		}else{
			list($alias,$field) = explode('.',$field);
			$alias = '`'.$alias.'`';
		}
		$field = '`'.$field.'`';
		//$field = $alias.'.'.'`'.$field.'`';
		
		if (('in' === $compare) || 'not in' === $compare){
			if (!is_array($value)){
				$value = (string)$value;
				if (!preg_match('/\(.+\)/',$value)){
					$value = str_replace(array('"',"'"),'',$value);
					$values = explode(',',$value);
					$value = implode("','",$values);
				}
			}else{
				$value = implode("','",$value);
			}
			$value = "('".$value."')";
		}else{
                        if ($value === null)
                            $value = "null";
                        else
                            $value = "'".(string)$value."'";
		}
		
		if (empty($this->wheres) || (true === $this->where_open)){
			//$this->wheres[] = array($field,$compare,$value);
			$this->wheres[] = array(
				'alias'=>$alias,'field'=>$field,
				'compare'=>$compare,'value'=>$value
			);
			$this->where_open = false;
		}else{
			$this->wheres[] = array(
				'type'=>$type,
				'alias'=>$alias,'field'=>$field,
				'compare'=>$compare,'value'=>$value
			);
			//$this->wheres[] = array($type,$field,$compare,$value);
		}
		//var_dump($this->wheres);
		return $this;
	}
	
	public function or_where($field,$compare,$value){
		return $this->where($field,$compare,$value,'or');
	}
	
	public function where_open($type = 'and'){
                if (empty($this->wheres))
			$type = '';
		$this->wheres[] = $type.' (';
		$this->where_open = true;
		return $this;
	}
	
	public function or_where_open(){
		return $this->where_open('or');
	}
	
	public function where_close(){
		$this->wheres[] = ')';
		return $this;
	}
	
	public function limit($max){
		if (!is_int($max) && !preg_match('/^\d+$/', $max)){
			return $this->error('limit非数值');
		}
		$this->limit = (int)$max;
		return $this;
	}
	
	public function offset($start){
		if (!is_int($start) && !preg_match('/^\d+$/', $start)){
			return $this->error('offset非数值');
		}
		$this->offset = (int)$start;
		return $this;
	}
	
	/* ->select('23','f','sdf')
	 * 或者
	 * ->select(array('23','num'),array('f','try))
	*/
	public function select($selectFieldsArr = array()){
		if (0 < func_num_args()){
			$selectFields = func_get_args();
			foreach ($selectFields as &$selectField){
                                if ('*' == $selectField){
					$selectField = $this->alias.'.*';
					continue;
				}
				if (is_array($selectField)){
                                        if (isset($selectField[1])){
                                            $field_alias = $selectField[1];
                                        }
                                        $selectField = $selectField[0];
				}
				
				if (0 < preg_match('/^[\w\.]+$/',$selectField)){
					$selectField = (string)$selectField;
					if (false === strpos($selectField,'.')){
						list($alias,$field) = array($this->alias,$selectField);
					}else{
						list($alias,$field) = explode('.',$selectField);
						$alias = '`'.$alias.'`';
					}
					$selectField = $alias.'.'.'`'.$field.'`';
				}
                                if (isset($field_alias)){
                                    $selectField .= ' as `'.$field_alias.'`';
                                }
			}
			$selectSQL = implode(',',$selectFields);
		}else{
			$selectSQL = '*';
		}
		
		$this->output('select',array('selectSQL'=>$selectSQL));
		$this->action = 'select';
		return $this;
		//return $this->exec('select');
	}
	
	public function update(){
		if (empty($this->values)){
			return $this->error('值数组为空');
		}
		
        $updates = array();
		foreach ($this->values as $field=>$value){
			$updates[] = $field.'='.$value;
		}
		foreach ($this->timestamps as $field=>$value){
			$updates[] = $field.'='.$value;
		}
		$updateSQL = implode(',',$updates);
		
		$this->output('update',array('updateSQL'=>$updateSQL));
		$this->action = 'update';
		return $this;
		//return $this->exec('update');
	}
	
	public function insert($option = null,$on_duplicate_key_update = array()){
		if (empty($this->values)){
			return $this->error('值数组为空');
		}
		$fields = array_keys($this->values);
		$values = array_values($this->values);
		if (!empty($this->timestamps)){
			$fields = array_merge($fields,array_keys($this->timestamps));
			$values = array_merge($values,array_values($this->timestamps));
		}
//                var_dump($fields,in_array('`id`',$fields));
		if (!in_array('`id`',$fields) && ('id' == $fields[0])){
			array_unshift($fields,'`id`');
			array_unshift($values,'null');
		}
		
		$fieldsSQL = '('.implode(',',$fields).')';
		$valuesSQL = 'values('.implode(',',$values).')';
		$insertSQL = $fieldsSQL.' '.$valuesSQL;
		
        if ($option == 'ignore')
            $this->output('insert',array('insertSQL'=>$insertSQL),'ignore');
        elseif(($option == 'duplicate') && $on_duplicate_key_update){
            $updates = array();
            foreach ($on_duplicate_key_update as $field=>$value){
                    $updates[] = $field.'='.$value;
            }
            $on_duplicate_key_update_SQL = implode(',',$updates);
            $this->output('insert',array('insertSQL'=>$insertSQL),'duplicate',$on_duplicate_key_update_SQL);
        }else
            $this->output('insert',array('insertSQL'=>$insertSQL));
		$this->action = 'insert';
		return $this;
		//return $this->exec('insert');
	}
	
	public function delete(){
		$this->output('delete');
		$this->action = 'delete';
		return $this;
		//return $this->exec('delete');
	}
	
	public function join($model = null,$alias = null,$direction = null){      
		if (!is_string($model)){
			return $this->error('模型字符串不正确');
		}
		
		if (!$alias){
			$alias = $model;
		}

        if ((false === strpos($model,'(')) && (false === strpos($field,'.')))
            $model = '`'.self::table_prefix().$model.'`';

		$join = array_filter(array(
			$direction,'join',
			$model,'as','`'.$alias.'`'
		));
		$this->join[] = array(
			'join'=>implode(' ',$join)
		);
                
		return $this;
	}
	
	public function left_join($model = null,$alias = null){
		return $this->join($model,$alias,'left');
	}
	
	public function right_join($model = null,$alias = null){
		return $this->join($model,$alias,'right');
	}
	
	public function on($field1,$field2){
		if (!is_string($field1) || !is_string($field2)){
			return $this->error('非字符串');
		}
		foreach (func_get_args() as $field){
			if (false === strpos($field,'.')){
				list($alias,$field) = array($this->alias,$field);
			}else{
				list($alias,$field) = explode('.',$field);
				$alias = '`'.$alias.'`';
			}
			$ons[] = $alias.'.'.'`'.$field.'`';
		}
		$last = count($this->join)-1;
		$this->join[$last]['on'][] = $ons[0].'='.$ons[1];
		//var_dump($this->join);
		return $this;
	}
	
	/* 
	 * ->order_by('id')
	 * 或者
	 * ->order_by('ut.id','desc')
	 * */
	public function order_by($fields,$direction = 'asc'){
		if (is_string($fields)){
			$fields = array($fields);
		}
		foreach ($fields as $field){
			$field = (string)$field;
			if (false !== strpos($field,'(')){
                $this->order_by[$this->alias.'.'.$field] = $field;
                continue;
            }elseif (false === strpos($field,'.')){
				list($alias,$field) = array($this->alias,$field);
			}else{
				list($alias,$field) = explode('.',$field);
				$alias = '`'.$alias.'`';
			}
			//var_dump(explode('.',$field));
			
                        if (isset($this->order_by[$alias.'.'.$field]))
                                unset($this->order_by[$alias.'.'.$field]);
			$this->order_by[$alias.'.'.$field] = $alias.'.'.'`'.$field.'` '.$direction;
		}
		//var_dump($this->order_by);
		return $this;
	}
	
	/* ->group_by('23','f','sdf')
	 * 或者
	 * ->group_by(array('23','f','sdf'))
	*/
	public function group_by($fieldsArr){
		if (0 < func_num_args()){
			if (is_array($fieldsArr)){
				$fields = $fieldsArr;
			}else{
				$fields = func_get_args();
			}
			
			foreach ($fields as $field){
				$field = (string)$field;
				if (false === strpos($field,'.')){
					list($alias,$field) = array($this->alias,$field);
				}else{
					list($alias,$field) = explode('.',$field);
					$alias = '`'.$alias.'`';
				}
				//var_dump(explode('.',$field));
				
				$this->group_by[] = $alias.'.'.'`'.$field.'`';
			}
		}
		
		//var_dump($this->group_by);
		return $this;
	}
	
	/* 
	 * ->having('df','=',456)
	 * 或者
	 * ->or_having('df.df','in',456)
	 * */
	public function having($field,$compare,$value,$type = 'and'){
		if (!is_string($field) || !is_string($compare)){
			return $this;
		}
		if (empty($field) || empty($compare)){
			return $this;
		}
		
		if (false === strpos($field,'.')){
			list($alias,$field) = array($this->alias,$field);
		}else{
			list($alias,$field) = explode('.',$field);
			$alias = '`'.$alias.'`';
		}
		$field = $alias.'.'.'`'.$field.'`';
		
		if ('in' === $compare){
			if (is_array($value)){
				$value = "('".implode("','",$value)."')";
			}else{
				$value = (string)$value;
				if (!preg_match('/\(.+\)/',$value)){
					$value = str_replace(array('"',"'"),'',$value);
					$values = explode(',',$value);
					$value = "('".implode("','",$values)."')";
				}
			}
		}else{
			$value = "'".(string)$value."'";
		}
		
		if (empty($this->having)){
			$this->having[] = array($field,$compare,$value);
		}else{
			$this->having[] = array($type,$field,$compare,$value);
		}
		
		return $this;
	}
	
	public function or_having($field,$compare,$value){
		return $this->having($field,$compare,$value,'or');
	}
	
	private function output($action,$param = array(),$extra1 = null,$extra2 = null){
		$whereSQL = $joinSQL = $groupSQL = $orderSQL = $limitSQL = '';
		if (isset($this->id)){
			$this->where('id','=',$this->id);
		}
		
		if (!empty($this->wheres)){
			//var_dump($this->wheres);
			foreach ($this->wheres as $where) {
				if (is_array($where)){
					if ('delete' !== $action){
						$where['field'] = $where['alias'].'.'.$where['field'];
					}
					unset($where['alias']);
					$wheres[] = implode(' ',$where);
				}else{
					$wheres[] = $where;
				}
				//$wheres[] = is_array($where)?implode(' ',$where):$where;
			}
			//var_dump($wheres);
			$whereSQL = ' where '.implode(' ',$wheres);
		}
		
		//var_dump($this->join);
		if (!empty($this->join) && ($action == 'select')){
			foreach ($this->join as $join) {
				if (isset($join['on']) && is_array($join['on'])){
					$on = implode(' and ',$join['on']);
				}
				$joins[] = $join['join'].' on '.$on;
			}
			$joinSQL = ' '.implode(' ',$joins);
			//var_dump($joinSQL);
		}
		
		if (!empty($this->group_by) && ($action == 'select')){
			if (is_array($this->having) && !empty($this->having)){
				foreach ($this->having as $having) {
					$havings[] = is_array($having)?implode(' ',$having):$having;
				}
				$havingSQL = ' having'.implode(' ',$havings);
				$groupSQL = ' group by '.implode(',',$this->group_by).$havingSQL;
			}else{
				$groupSQL = ' group by '.implode(',',$this->group_by);
			}
		}
		if (!empty($this->order_by) && ($action == 'select')){
			$orderSQL = ' order by '.implode(',',$this->order_by);
		}
		
		if (isset($this->limit) && false !== $this->limit){
			if (isset($this->offset) && false !== $this->offset){
				$limitSQL = ' '.'limit '.$this->offset.','.$this->limit;
			}else{
				$limitSQL = ' '.'limit '.$this->limit;
			}
		}
		
		//var_dump($whereSQL);
		switch ($action){
			case 'delete':
				$DML = 'delete from `'.$this->model.'`'.$whereSQL.$limitSQL;
				break;
			case 'select':
				//$selectSQL = "`sdf`,`ddfew`";
				$selectSQL = $param['selectSQL'];
				$DML = 'select '.$selectSQL.' from `'.$this->model.'` as '.$this->alias.$joinSQL.$whereSQL.$groupSQL.$orderSQL.$limitSQL;
				break;
			case 'update':
				//$updateSQL = "`ty`='132',`sdf`='df'";
				$updateSQL = $param['updateSQL'];
				$DML = 'update `'.$this->model.'` as '.$this->alias.' set '.$updateSQL.$whereSQL.$limitSQL;
				break;
			case 'insert':
				//$insertSQL = "(`sdf`,`dd`) values('df','234')";
				$insertSQL = $param['insertSQL'];
                                if ($extra1 == 'ignore')
                                    $DML = 'insert ignore into `'.$this->model.'` '.$insertSQL;
                                elseif(($extra1 == 'duplicate') && $extra2){
                                    $DML = 'insert into `'.$this->model.'` '.$insertSQL.' ON DUPLICATE KEY UPDATE '.$extra2;
                                }else
                                    $DML = 'insert into `'.$this->model.'` '.$insertSQL;
				break;
			default:
				$DML = '';
		}
		//var_dump($DML);exit;
		$this->sql = $DML;
	}
	
	//->pack(':pack1:')
	public function set_pack($packTag = null){
		if (null === $packTag){
			return $this->error('未提供子句标记');
		}
		
		$sql = $this->sql();
		if ($sql){
			$this->sql_packs[$packTag] = '('.$sql.')';
			$this->clear();
		}
		
		return $this;
	}
	
	public function get_pack($packTag = null){
		if (null !== $packTag){
			if (isset($this->sql_packs[$packTag])){
				return $this->sql_packs[$packTag];
			}else{
				return $this->error('找不到该子句');
			}
			
		}
		return $this->sql_packs;
	}
	
	//->pack(':pack2:')->union(':pack1:',':pack2:');
	public function union($packTag1,$packTag2 = null,$type = 'union'){
		if (is_string($packTag1) && isset($this->sql_packs[$packTag1])){
			$this->clear();
			if (is_string($packTag2) && isset($this->sql_packs[$packTag2])){
				$this->sql = $this->sql_packs[$packTag1].' '.$type.' '.$this->sql_packs[$packTag2];
			}else{
				$last_pack = end($this->sql_packs);
				$this->sql = $last_pack.' '.$type.' '.$this->sql_packs[$packTag1];
			}
		}else{
			return false;
		}
		return $this;
	}
	
	public function union_all($packTag1,$packTag2 = null){
		return $this->union($packTag1,$packTag2,'union all');
	}
	
	public function sql(){
		if (!isset($this->sql) || !is_string($this->sql) || (0 == strlen($this->sql))){
			$this->error('SQL语句为空');
			return false;
		}
                self::$last_sql = $this->sql;
		return $this->sql;
	}
	
	public function clear(){
		unset($this->action,$this->as_array_rule);
		unset($this->limit,$this->offset);
		$this->timestamps = $this->values = $this->wheres = array();
		$this->join = $this->order_by = $this->group_by = $this->having = array();
		$this->where_open = $this->as_array = false;
		return $this;
	}
}