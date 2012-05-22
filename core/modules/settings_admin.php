<?php

/*
 *	класс Settings с набором системных методов
 */
class modules_settings_admin{
	const TITLE = 'Настройки';
	const POSITION = '3';


	//==================================================================================================
	// Функция определения адресата запроса
	function onLoad($GET,$POST,$FILES){
		if (!isset($GET['action'])){modules_settings_admin::show();}
		if ($GET['action']=='findSettings'){modules_settings_admin::findSettings();}
		if ($GET['action']=='createSettings'){modules_settings_admin::createSettings($POST);}
		if ($GET['action']=='updateSettings'){modules_settings_admin::updateSettings($POST);}
		if ($GET['action']=='deleteSettings'){modules_settings_admin::deleteSettings($GET['id']);}

	}
	// Функция определения адресата запроса
	//==================================================================================================




	//==================================================================================================
	// Функция вывода настроек
	function show(){

		$SEND['settings'] = modules_settings_admin::findSettings();
		$SEND['path'] = 'Настройки';
		$SEND['content'] = admin::draw('settings/page',$SEND);
		$SEND['title'] = 'Настройки';
		$SEND['js'] = 'settings/js.js';
		echo admin::draw('page_index',$SEND);

	}
	// Функция вывода настроек
	//==================================================================================================




	//==================================================================================================
	// Функция поиска настроек
	function findSettings(){

		$result = sys::sql("SELECT `id`,`name`,`value` FROM `prefix_Settings` ORDER BY `id`",0);

		if (mysql_num_rows($result)>0){

			while($row = mysql_fetch_array($result)){
				$DATA['settingsElement'] .= admin::draw('settings/settingsElement',$row);
			}

			$out = admin::draw('settings/settingsForm',$DATA);
			
		}

		return $out;

	}
	// Функция поиска настроек
	//==================================================================================================




	//==================================================================================================
	// Функция создания настроек
	function createSettings($POST){

		$sql = sys::sql("INSERT
						INTO
							`prefix_Settings`
						VALUES
							(
								'',
								'".$POST['name']."',
								'".$POST['value']."'
							)
		;",0);

		echo modules_settings_admin::findSettings();

	}
	// Функция создания настроек
	//==================================================================================================




	//==================================================================================================
	// Функция обновления настроек	
	function updateSettings($POST){

		$query = sys::sql("SELECT `name` FROM `prefix_Settings` ORDER BY `id`",0);
		while($row = mysql_fetch_array($query)){
			$result = sys::sql("UPDATE `prefix_Settings` SET `value` = '".$POST[$row['name']]."' WHERE `name` ='".$row['name']."' LIMIT 1 ;",0);
		}

		echo modules_settings_admin::findSettings();

	}
	// Функция обновления настроек
	//==================================================================================================




	//==================================================================================================
	// Функция обновления настроек
	function deleteSettings($id){

		$sql = sys::sql("DELETE
						FROM
							`prefix_Settings`
						WHERE
							`id` = '$id'
						LIMIT 1
		;",0);

		echo modules_settings_admin::findSettings();

	}
	// Функция обновления настроек
	//==================================================================================================




	//==================================================================================================
	// Функция создания таблицы
	function createTable(){
		$sql = sys::sql("CREATE TABLE IF NOT EXISTS
							`prefix_Settings` (
								`id` int(11) NOT NULL auto_increment,
								`name` varchar(255) NOT NULL,
								`value` varchar(255) NOT NULL,
								PRIMARY KEY  (`id`)
							)
							ENGINE=MyISAM
							AUTO_INCREMENT=1
							CHARACTER SET utf8 COLLATE utf8_general_ci
		;",0);
	}
	// Функция создания таблицы
	//==================================================================================================

}
// Конец класса
?>