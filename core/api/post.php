<?php
/**
 * Класс реализации доступа к API по протоколу HTTP-POST(GET)
 */
class api_post
{
	function __construct() {
		global $CONF;
		// Загрузка CONFIG.php
		$CONF = sys::preLoad();
		
		// Получаем имя метода и класса
		$call	= api::getMethod(api::$params);
		
		// Формируем вызываемый метод
		$method	= $call['class'].'::'.$call['method'];

		$params = $_POST;
		if (count($_GET)>0){
			$params = $_GET;
		};

		// Если метод существует
		if (method_exists($call['class'],$call['method'])){
			eval('$result = '.$call['class'].'::'.$call['method'].'($params);');
		}else{
			// Если функции нет, выводим ошибку
			$result = -32601;
		}
		// Вывод результата
		if ($_POST['out']=='json'){
			echo json_encode($result);
		}else{
			echo $result;
		}
	}

	// Функция проверки параметров вызываемого метода
	private function checkParams($real, $sent){
		$new = array();
		$is_obj = is_object($sent);
		if(!is_array($sent)){
			$sent= array($sent);
		}
//		$is_assoc = array_keys($sent) != range(0, count($sent) - 1);
		$is_assoc = true;
		foreach($real as $i=>$param)
		{
			$name = $param->getName();
			if($is_obj &&  isset($sent->{$name})) $new[$i] = $sent->{$name};
			elseif($is_assoc && $sent[$name]) $new[$i] = $sent[$name];
			elseif( isset($sent[$i])) $new[$i] = $sent[$i];
			elseif(!$param->isOptional()) return -32602;
		}
		
		return $new;
	}
}


?>