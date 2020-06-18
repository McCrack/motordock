<?php

/**
 *	{str} - 'String'
 *	{fld} - `Field`
 *	{int} - Intager
 *	{prp} - Prepared fragment
 *	{arr} - array `field`='value'
 *	{set} - `field`='value', ...
 *
 *	inquiry - Free query
 *	getRow - Single row result
 *	get - Multi row result
 *	getGroup - Flip result
 *	getTree - Result in the form of a tree
 *	toTree - Build tree
**/

class db extends mysqli
{
	public $errors;
	public $status;
	public $settings;
	public $success = false;
	public function __call($tName, $conditions)
	{
		foreach ($conditions[0] as $field=>$value) {
			if (is_string($value)) {
				$where[] = $this->parse("{fld} LIKE {str}", $field, $value);
			} elseif (is_int($value)) {
				$where[] = $this->parse("{fld} = {int}", $field, $value);
			} elseif(is_array($value)) {
				$where[] = $this->parse("{fld} IN ({arr})", $field, $value);
			}
		}
		return $this->getRow(
			"SELECT * FROM {fld} WHERE {prp} LIMIT 1",
			$tName,
			implode(" AND ", $where)
		);
	}
	public function inquiry()
	{
		$args = func_get_args();
		$query = $this->prepareQuery( $args );
		$response = false;
		if ($result=$this->rawQuery( $query )) {
				$this->success = true;
				$response['last_id'] = $this->insert_id;
				$response['affected_rows'] = $this->affected_rows;
				return $response;
		} else {
			$this->success = false;
			$response['last_id'] =
			$response['affected_rows'] = null;
			$this->errors[] = $this->error;
			return false;
		}
	}
	public function getRow()
	{
		$query = $this->prepareQuery(func_get_args());
		if ($result = $this->rawQuery($query)) {
			$response = $result->fetch_assoc();
			$result->free();
			return $response;
		} else {
			$this->errors[] = $this->error;
			return false;
		}
	}
	public function get()
	{
		$response = [];
		$query = $this->prepareQuery(func_get_args());
		if ($result = $this->rawQuery($query)) {
			if ($result->num_rows) {
				while ($row = $result->fetch_assoc()) {
					$response[] = $row;
				}
				$result->free();
			}
			$this->success = true;
			return $response;
		} else {
			$this->errors[] = $this->error;
			return [];
		}
	}
	public function getGroup()
	{
		$response = [];
		$query = $this->prepareQuery(func_get_args());
		if ($result = $this->rawQuery($query)) {
			if ($result->num_rows) {
				while ($row = $result->fetch_assoc()) foreach($row as $key=>$val) {
					$response[$key][]=$val;
				}
				$result->free();
			}
			return $response;
		} else {
			$this->errors[] = $this->error;
			return [];
		}
	}
	public function getTree()
	{
		$args  = func_get_args();
		$id = array_shift($args);
		$parent = array_shift($args);
		$query = $this->prepareQuery($args);
		$response = [];
		if ($result = $this->rawQuery($query)) {
			if ($result->num_rows) {
				while ($row = $result->fetch_assoc()) foreach($row as $key=>$val) {
					$response[$row[$parent]][$row[$id]][$key]=$val;
				}
				$result->free();
			}
			return $response;
		} else {
			$this->errors[] = $this->error;
			return false;
		}
	}
	public function toTree($list, $id, $parent)
	{
		$result = [];
		foreach ($list as $i=>$row) {
			foreach ($row as $key=>$val) {
				$result[$row[$parent]][$row[$id]][$key] = $val;
			}
		}
		return $result;
	}
	protected function rawQuery($query)
	{
		if ($result = $this->query($query, MYSQLI_STORE_RESULT)) {
			$this->success = true;
			$this->status = [
				"last_id"=>$this->insert_id,
				"affected_rows"=>(INT)$this->affected_rows
			];
		} else {
			$this->success = false;
			$this->status = [
				"last_id"=>0,
				"affected_rows"=>0
			];
		}
		return $result;
	}
	public function parse()
	{
		return $this->prepareQuery(func_get_args());
	}
	protected function prepareQuery($args)
	{
		$query = "";
		$raw   = array_shift($args);
		$array = preg_split("~({.{3}})~u",$raw,null,PREG_SPLIT_DELIM_CAPTURE);
		$anum  = count($args);
		$pnum  = floor(count($array) / 2);
		if ( $pnum != $anum ) {
			$this->errors[] = "Number of args ($anum) doesn't match number of placeholders ($pnum) in [$raw]";
		}
		foreach ($array as $i=>$part) {
			if ( ($i % 2) == 0 ) {
				$query .= $part;
				continue;
			}
			$value = array_shift($args);
			switch($part) {
				case "{fld}":
					$part = $this->escapeField($value);
					break;
				case "{str}":
					$part = $this->escapeString($value);
					break;
				case "{int}":
					$part = $this->escapeInt($value);
					break;
				case "{arr}":
					$part = $this->createIN($value);
					break;
				case "{set}":
					$part = $this->createSET($value);
					break;
				case "{prp}":
					$part = $value;
					break;
				default:
				break;
			}
			$query .= $part;
		}
		return $query;
	}

	protected function escapeInt($value)
	{
		if ($value === NULL) {
			return "NULL";
		}
		if (!is_numeric($value)) {
			$this->errors[] = "Integer (?i) placeholder expects numeric value, ".gettype($value)." given";
			return FALSE;
		}
		if (is_float($value)) {
			$value = number_format($value, 0, ".", ""); // may lose precision on big numbers
		}
		return $value;
	}
	protected function escapeString($value)
	{
		if($value === NULL){
			return "NULL";
		}
		return "'".$this->real_escape_string($value)."'";
	}
	protected function escapeField($value)
	{
		if (empty($value)) {
			$this->errors[] = "Empty value for identifier (?n) placeholder";
		} else {
			return "`".str_replace("`","``",$value)."`";
		}
	}
	protected function createIN($data)
	{
		if (!is_array($data) || empty($data)) {
			$this->errors[] = "Parameter is not an array or array is empty";
			return;
		}
		foreach ($data as &$value) {
			if (is_numeric($value)) {
				$value = $this->escapeInt($value);
			}else {
				$value = $this->escapeString($value);
			}
		}
		return implode(", ", $data);
	}
	protected function createSET($data)
	{
		if (!is_array($data) || empty($data)) {
			$this->errors[] = "Parameter is not an array or array is empty";
			return;
		}
		foreach ($data as $key=>&$value) {
			$value = $this->escapeField($key)."=".$this->escapeString($value);
		}
		return implode(", ", $data);
	}
}

?>
