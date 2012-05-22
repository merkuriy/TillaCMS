<?php
//2010-01-12 11:09AM EST
class jsonrpc2{

	public $errors      = array(
		-32600  =>'Invalid Request',
		-32601  =>'Method not found',
		-32602  =>'Invalid params',
		-32603  =>'Internal error',
		-32700  =>'Parse error',
	);

	private $methods = array();

	public $key = 'jsonrpc';

	public function __construct(){
//		if(!is_array($method_map)) throw new Exception('Requires a method map that is an array.');
//		$this->methods = $method_map;
	}

	public function dispatch_batch($request){
		if(is_array($request)){
			shuffle($request);
			$return = array();
			foreach($request as $i=>$request_item){
				$a = $this->dispatch($request_item);
				if(is_object($a)) $return[] = $a;
			}
			return $return;
		}
		return $this->dispatch($request);
	}

	public function dispatch($request){
		if(!isset($request->jsonrpc)) $request->jsonrpc = '1.0';
		if($request->jsonrpc == '2.0' && !isset($request->params)) $request->params = array();
		$response = $this->invoke($request);
		if(!isset($request->id) || is_null($request->id)) return;
		$response->id = $request->id;
		if($request->jsonrpc == '2.0') $response->jsonrpc = '2.0';
		else{
			if(!isset($response->result)) $response->result = null;
			if(!isset($response->error)) $response->error = null;
		}
		return $response;
	}

	private function invoke($request){
		$call = api::getMethod($request->method);
		$error = -32601;
		try{
			if(!method_exists($call['class'],$call['method'])) throw new Exception(-32601);
			$m[1] = $call['class'];
			$m[0] = $call['method'];
			if(!is_array($m)) $m = array($m);
			$r = isset($m[1]) ? new ReflectionMethod($m[1], $m[0]) : new ReflectionFunction($m[0]);
			$p = $this->checkParams($r->getParameters(), $request->params);
			if(!isset($m[1]))       return (object) array('result' =>$r->invokeArgs($p));
			if(is_object($m[1]))    return (object) array('result' =>$r->invokeArgs($m[1], $p));
			if(is_string($m[1]))    return (object) array('result' =>$r->invokeArgs(new $m[1], $p));
		}
		catch(Exception $e){
			$error = $e->getMessage();
		}
		return (object) array('error'=> (object) array('code'=> $error, 'message'=>$this->errors[$error]));
	}

	private function checkParams($real, $sent){
		$new = array();
		$is_obj = is_object($sent);
		if(!is_array($sent)){
			$sent= array($sent);
		}
		$is_assoc = array_keys($sent) != range(0, count($sent) - 1);
		foreach($real as $i=>$param)
		{
			$name = $param->getName();
			if($is_obj &&  isset($sent->{$name})) $new[$i] = $sent->{$name};
			elseif($is_assoc && $sent[$name]) $new[$i] = $sent[$name];
			elseif( isset($sent[$i])) $new[$i] = $sent[$i];
			elseif(!$param->isOptional()) throw new Exception(-32602);
		}
		
		return $new;
	}
	
	public function parse(){
		$error = -32603;
		try{
			$r = $this->resolve_from_single_parameterized_call();
			if($r===FALSE) $r = $this->resolve_from_blob();
			if(is_object($r) || is_array($r)) return json_encode($this->dispatch_batch($r));
			throw new Exception(-32600);
		}
		catch(Exception $e){
			$error = $e->getMessage();
		}
		return json_encode((object) array('error'=> (object) array('code'=>$error, 'message'=>$this->errors[$error])));
	}

	private function resolve_from_single_parameterized_call(){
		if(isset($_REQUEST['method'])){
			$r = (object) array('method'=>null,'params'=>array(),'id'=>null,'jsonrpc'=>'1.0');
			foreach($r as $k=>$v){
				if(isset($_REQUEST[$k])){
					$i=json_decode($_REQUEST[$k],TRUE);
					$r->$k = ( $i ) ? $i : $_REQUEST[$k];
				}
			}
		}
		return isset($r) ? $r : FALSE;
	}

	private function resolve_from_blob(){
		$b = isset($_REQUEST[$this->key]) ? json_decode($_REQUEST[$this->key], TRUE) : null;
		if(!is_array($b)) $b = json_decode(rawurldecode($_SERVER['QUERY_STRING']), TRUE);
		if(!is_array($b)) $b = json_decode(file_get_contents('php://input'), TRUE);
		if(!is_array($b)) throw new Exception(-32700);
		if(!isset($b[0])) return$this-> map_into($b); //non-batch
		foreach($b as &$item) $item = $this->map_into($item);
		return $b;
	}

	private function map_into($i){
		$r = array('method'=>null,'params'=>array(),'id'=>null,'jsonrpc'=>'1.0');
		foreach($r as $k=>$v){
			if(isset($i[$k])) $r[$k] = $i[$k];
		}
		return (object) $r;
	}
}
?>