<?php

/*
 *	Компонент TText
 *	===============
 *	Компонент хранения текстового значения (MEMO) 
 */
class components_TText{
	
	//=====================================
	//Функция вывода данных
	function view($name, $parentId, $param=''){
		$data_child_element=sys::sql("SELECT `data` FROM `prefix_TText` WHERE `name`='$name' AND `parent_id`='$parentId';",0);
		
		if (mysql_num_rows($data_child_element))
			return mysql_result($data_child_element,0);
		
		return '';
	}
	
	
	//=====================================
	//Функция вывода на редактирование
	function edit($name, $parentId, $title){
		components_TText::createTable();
		
		$data = sys::sql("
			SELECT
				text.`data` data,
				settings.`type` type
			FROM
				`prefix_TText` text,
				`prefix_Sections` sections,
				`prefix_ClassSections` class
				LEFT JOIN
					`prefix_TTextSettings` settings
				ON
					settings.`id` = class.`id`
			WHERE
				text.`name` = '$name' AND
				text.`parent_id` = $parentId AND
				
				sections.`id` = text.`parent_id` AND
				
				class.`parent_id` = sections.`base_class` AND
				class.`name` = '$name'
		;", 1);

		$SEND = array(
			'parentId' => $parentId,
			'title' => $title,
			'name' => $name,
			'data' => $str = json_encode( $data[0]['data'] )
		);
		

		if ($data[0]['type']==2)
			return admin::draw('TText/editDialogCode', $SEND);
		else if ($data[0]['type']==1)
			return admin::draw('TText/editDialog', $SEND);
		
		return admin::draw('TText/editDialogWYSIWYG', $SEND);
		
	}


	//=====================================
	//Функция сохранения данных
	function save($POST,$FILES='',$param=''){
		$result = sys::sql("SELECT `data` FROM `prefix_TText` WHERE `name`='".$POST['dataName']."' AND `parent_id`='".$POST['parent_id']."';",0);
		if (mysql_num_rows($result)==0){
			$POST['parentId']=$POST['parent_id'];
			components_TText::createStr($POST);
		}
		$result = sys::sql("UPDATE `prefix_TText` SET `data` = '".$POST['data']."' WHERE `name`='".$POST['dataName']."' AND `parent_id`='".$POST['parent_id']."' LIMIT 1 ;",0);
		if ($param=='client'){
			return;
		} else {
		}
	}


	//=====================================
	//Функция создания записи
	function createStr($POST){
		components_TText::createTable();	
		$result = sys::sql("SELECT `id` FROM `prefix_TText` WHERE `name`='".$POST['dataName']."' AND `parent_id`='".$POST['parentId']."'",0);
		if (mysql_num_rows($result)=='0'){
			$result = sys::sql("INSERT INTO `prefix_TText` ( `id` , `name` , `parent_id` , `data` ) VALUES ( '', '".$POST['dataName']."', '".$POST['parentId']."', '');",0);
		};
	}


	//=====================================
	//Функция удаления записи
	function deleteAttr($name,$id){

		$result = sys::sql("DELETE FROM `prefix_TText` WHERE `parent_id` = '$id' AND `name` = '$name' LIMIT 1;",0);
	}
	


	


	//=====================================
	//Функция Проверки условий
	function condition($name,$parentId,$cond){
		return false;
	}
	
	
	
	//=====================================
	// Функция настроек
	function editSettings($id){
		components_TText::createTable();
		
		$SEND = sys::sql("SELECT `type` FROM `prefix_TTextSettings` WHERE `id`='$id';",1);
		
		
		
		if (count($SEND)){
			$SEND['type1'] = $SEN['type2'] = '';
			if( $SEND[0]['type']==2 )
				$SEND['type2'] = 'selected="selected"';
			else if( $SEND[0]['type'] )
				$SEND['type1'] = 'selected="selected"';
		}
			
		else
			$SEND['type1'] = '';
		
		$SEND['parent'] = mysql_result(
			sys::sql("SELECT
						`parent_id`
					FROM
						`prefix_ClassSections`
					WHERE
						`id` = '$id'
			;",0)
		,0);
		
		$SEND['id'] = $id;
		$SEND['js'] = 'TText/editRuleDialog.js';
		
		echo admin::draw('TText/editRuleDialog',$SEND);
		
	} 


	//=====================================
	// Функция сохранения настроек
	function saveSettings($id, $POST){
		components_TText::createTable();
		
		sys::sql("
			INSERT INTO `prefix_TTextSettings` (`id`,`type`)
			VALUES ($id,'".$POST['type']."')
			ON DUPLICATE KEY UPDATE `type`='".$POST['type']."';
		;",0);
		
		echo 'Сохранение прошло успешно!';
	}  
	
	
	
	//=====================================
	//Функция Создания таблицы для хранения данных
	function createTable(){
		sys::sql("
			CREATE TABLE IF NOT EXISTS `prefix_TText` (
				`id` int(11) NOT NULL auto_increment,
				`name` varchar(255) NOT NULL,
				`parent_id` int(11) NOT NULL,
				`data` text,
				PRIMARY KEY  (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8 COLLATE utf8_general_ci
		;",0);
		
		sys::sql("
			CREATE TABLE IF NOT EXISTS `prefix_TTextSettings` (
				`id` int(11) NOT NULL auto_increment,
				`type` TINYINT NOT NULL,
				PRIMARY KEY  (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8 COLLATE utf8_general_ci
		;",0);
	}
	
}
?>