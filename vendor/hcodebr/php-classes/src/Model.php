<?php 

//diretorio principal
namespace Hcode;

class Model {

	private $values = [];

	public function __call($name, $args)
	{
		$method = substr($name, 0, 3);

		$filedName = subStr($name, 3, strlen($name));

		switch ($method) {
			
			case 'get':
				return (isset($this->values[$filedName])) ? $this->values[$filedName] : NULL;
			break;
			
			case 'set':
				$this->values[$filedName] = $args[0];
			break;
			
			default:
				// code...
			break;
		}
	}


	public function setData($data = array())
	{
		foreach ($data as $key => $value) {
			
			$this->{"set".$key}($value);	
		}
	}


	public function getValues()
	{
		return $this->values;
	}



}

?>