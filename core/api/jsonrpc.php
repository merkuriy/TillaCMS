<?php
/**
 * Класс реализации доступа к API по протоколу JSON-RPC2
 */
class api_jsonrpc{
	function __construct(){
		global $CONF;
		// Загрузка CONFIG.php
		$CONF = sys::preLoad();
		require_once('../core/includes/jsonrpc2.class.php');
		$server = new jsonrpc2();

		echo $server->parse();
	}
}


?>