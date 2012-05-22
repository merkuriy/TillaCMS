<?php
/**
 * Файл реализации API
 */

// Проставляем кодировку UTF-8
header('Content-type: text/html; charset="utf-8"',true);

// Подключаем автозагрузчик классов
include_once('__autoload_class.php');

class api
{
	static public
		$protocol,
		$params;
	
	static public function getMethod ($methodRequest)
	{
		$temp			= explode('?',$methodRequest);
		$calledMethod	= explode('.',$temp[0]);
		$classTemp		= explode('_',$calledMethod[0]);
		$method			= $calledMethod[1];
		if (count($classTemp)==1){
			$class='modules_'.$classTemp[0].'_sys';
		}else{
			$class='modules_'.$calledMethod[0];
		}
		
		return array(
			'class'	=> $class,
			'method'=> $method
		);
	}
}

$protocol = substr($_SERVER['REQUEST_URI'], 5);
api::$params = strpos($protocol, '/', 1);

/*
 * Проверяем существование параметров протокола
 * и в случае отсутсвия параметров api::$params будет равен bool(false)
 */
if (api::$params!==false ) {
	api::$protocol	= substr($protocol, 0, api::$params);
	api::$params	= substr($protocol, api::$params+1);
}else
	api::$protocol	= $protocol;

// Если протокол существует
if (file_exists('api/'.api::$protocol.'.php')) {
	// Запускаем сессию, для получения корректных данных
	session_start();
	// создаем новый объект этого протокола
	$protocol = 'api_'.api::$protocol;
	// Вызываем протокол
	$result = new $protocol;
// В противном случае
} else {
	// Выдаем ошибку 404
	header('HTTP/1.1 404 Not Found');
}

?>