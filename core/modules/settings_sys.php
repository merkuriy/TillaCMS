<?php

/* 
 *	Модуль настройки (SYS)
 */
class modules_settings_sys {
	//========================================
	// Функция выбора настройки
	function get($name){
		$setting=sys::sql("SELECT `value` FROM `prefix_Settings` WHERE `name`='$name'",0);
		if (mysql_num_rows($setting)>0){
			return mysql_result($setting,0);
		}else{
			return false;
		}
	}

	function set($name,$value){
		$setting=sys::sql("UPDATE `prefix_Settings` SET `value`='$value' WHERE `name`='$name'",0);
	}

}