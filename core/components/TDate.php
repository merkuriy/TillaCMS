<?php

/*
 *	Компонент TDate
 *	===============
 *	Компонент даты (2008-11-11 24:00:00)
 */
class components_TDate{
	
	
	
	//=====================================
	//Функция вывода на редактирование
	function edit($name,$parentId,$title){

		components_TDate::createTable();

		$data_child_element=sys::sql("SELECT `id`,`data` FROM `prefix_TDate` WHERE `name`='$name' AND `parent_id`='$parentId';",0);
		$data=mysql_fetch_array($data_child_element);

		if ($data['data']=='0000-00-00 00:00:00'){
			$SEND['error'] = 'В указанной дате была обнаружена ошибка и она была изменена!<br />';
			$data['data']=date("Y-m-d").' '.date("H:i:s");
		}	

		$SEND['title'] = $title;
		$SEND['name'] = $name;
		$SEND['data'] = $data['data'];
		$out = admin::draw('TDate/editDialog',$SEND);

		return $out;

	}


	//=====================================
	//Функция сохранения данных
	function save($POST,$FILES, $param=''){
		components_TDate::createTable();
		$result = sys::sql("SELECT `data` FROM `prefix_TDate` WHERE `name`='".$POST['dataName']."' AND `parent_id`='".$POST['parent_id']."';",0);
		if (mysql_num_rows($result)==0){
			$POST['parentId']=$POST['parent_id'];
			components_TDate::createStr($POST);
		}
		$result = sys::sql("UPDATE `prefix_TDate` SET `data` = '".$POST['data']."' WHERE `name`='".$POST['dataName']."' AND `parent_id`='".$POST['parent_id']."';",0);
		if ($param=='client'){
			return;
		} else {
		}
	}


	//=====================================
	//Функция создания записи
	function createStr($POST){
		components_TDate::createTable();
		$result = sys::sql("SELECT `id` FROM `prefix_TDate` WHERE `name`='".$POST['dataName']."' AND `parent_id`='".$POST['parentId']."'",0);
		if (mysql_num_rows($result)=='0'){
			$date=date("Y-m-d").' '.date("H:i:s");
			$result = sys::sql("INSERT INTO `prefix_TDate` (`id`,`name`,`parent_id`,`data`) VALUES ('','".$POST['dataName']."','".$POST['parentId']."','$date');",0);
		};
	}


	//=====================================
	//Функция удаления записи
	function deleteAttr($name,$id){
		components_TDate::createTable();
		$result = sys::sql("DELETE FROM `prefix_TDate` WHERE `parent_id` = '$id' AND `name` = '$name' LIMIT 1;",0);
	}


	//=====================================
	//Функция вывода данных
	function view($name,$parentId,$param=''){
		
		//components_TDate::createTable();
		
		$dateFormat['ru_RU']['+M']	= '"%c" ), "январь","февраль","март","апрель","май","июнь","июль","август","сентябрь","октябрь","ноябрь","декабрь"';
		$dateFormat['ru_RU']['-M']	= '"%c" ), "января","февраля","марта","апреля","мая","июня","июля","августа","сентября","октября","ноября","декабря"';
		$dateFormat['ru_RU']['+W']	= '"%w" )+1, "понедельник","вторник","среда","четверг","пятница","суббота"';
		
		//считываем из настроек используемый язык
		$language = sys::sql("SELECT `value` FROM `prefix_Settings` WHERE `name`='language'",0);
		if(mysql_num_rows($language)!=0){
			$language = mysql_result($language ,0);
		}else{
			$language = 'ru_RU';
		}
		
		//подготавливаем вставку
		if( isset( $dateFormat[$language][$param] ) ){
			$dateSelect = 'ELT( DATE_FORMAT( `data` , '.$dateFormat[$language][$param].' ) data';
		}else{
			$dateSelect = 'DATE_FORMAT( `data`, "%'.$param.'" ) data';
		}
		
		if ($dateSelect == 'DATE_FORMAT( `data`, "%" ) data') {
			$dateSelect = 'data';
		}

		//запрос
		$output = sys::sql('SELECT '.$dateSelect.' FROM `prefix_TDate` WHERE `name`="'.$name.'" AND `parent_id`='.$parentId.';',0);
		if( mysql_num_rows($output)!=0 ){
			$output = mysql_result($output ,0);
		}else{
			$output = '';
		}
	
		return $output;
	}


	//=====================================
	//Функция Создания таблицы для хранения данных
	function createTable(){
		$query=sys::sql("
			CREATE TABLE IF NOT EXISTS `prefix_TDate` (
				`id` int(11) NOT NULL auto_increment,
				`name` varchar(255) NOT NULL,
				`parent_id` int(11) NOT NULL,
				`data` datetime default NULL,
				PRIMARY KEY  (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8 COLLATE utf8_general_ci AUTO_INCREMENT=1;",0);
	}
	
	
	//=====================================
	//Функция Проверки условий
	function condition($name,$parentId,$cond){
		components_TDate::createTable();
		// определение знака
		if (substr($cond,0,2)=='<='){
			$znak='<=';
			$cond=substr($cond,2,strlen($cond));
		}
		if (substr($cond,0,2)=='>='){
			$znak='>=';
			$cond=substr($cond,2,strlen($cond));
		}
		if (substr($cond,0,1)=='<'){
			$znak='<';
			$cond=substr($cond,1,strlen($cond));
		}
		if (substr($cond,0,1)=='>'){
			$znak='>';
			$cond=substr($cond,1,strlen($cond));
		}
		if (substr($cond,0,1)=='='){
			$znak='=';    
			$cond=substr($cond,1,strlen($cond));
		}
		// преобразование зарезервированных слов
		if ($cond=='today'){
			$cond='CURDATE()';
		}
		if ($cond=='now'){
			$cond='NOW()';
		}
		// Выборка по условию 
		$result = sys::sql("SELECT `id` FROM `prefix_TDate` WHERE `data`$znak$cond AND `name`='$name' AND `parent_id`='$parentId'",0);
		if (mysql_num_rows($result)>0) {
			return true;
		} else {
			return false;
		};
	}

}
?>