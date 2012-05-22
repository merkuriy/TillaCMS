<?php

/*
 * Автоматическая загрузка классов при первом их вызове
 */   

function __autoload($className) {
	
	$classa = explode('_',$className);
	if( count($classa)==1 ){
		require_once '../core/'.$className.'.php';
	}elseif(count($classa)==2 ){
		require_once '../core/'.$classa[0].'/'.$classa[1].'.php';
	}elseif(count($classa)==3 ){
		require_once '../core/'.$classa[0].'/'.$classa[1].'_'.$classa[2].'.php';
	}
}


?>