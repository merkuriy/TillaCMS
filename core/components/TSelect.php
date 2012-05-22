<?php

/*
 *	Компонент TSelect
 *	=================
 *	Компонент хранения системных значений
 */
class components_TSelect{

	//=====================================
	//Функция вывода на редактирование
	function edit($name,$parentId,$title){
		components_TSelect::createTable();
		$data_child_element=sys::sql("SELECT `id`,`data` FROM `prefix_TSelect` WHERE `name`='$name' AND `parent_id`='$parentId';",0);
		$data=mysql_fetch_array($data_child_element);

		$sql = sys::sql("SELECT
							selct.`name` name,
							selct.`title` title
						FROM
							`prefix_TSelect_Settings` selct,
							`prefix_ClassSections` class
						WHERE
							selct.`parent_id`=class.`id` AND
							class.`name` = '$name'
		;",0);

		while ($row = mysql_fetch_array($sql)){
			$SEN['title'] = $row['title'];
			$SEN['name'] = $row['name'];
			
			if( $row['name']==$data['data'] ){
				$SEND['option'] .= admin::draw('TSelect/option.active',$SEN);
			}else{
				$SEND['option'] .= admin::draw('TSelect/option',$SEN);
			}
		}

		$SEND['title'] = $title;
		$SEND['name'] = $name;
		$SEND['data'] = $data['data'];
		$out = admin::draw('TSelect/editDialog',$SEND);
		return $out;
	}


	//=====================================
	//Функция сохранения данных
	function save($POST, $FILES, $param=''){
		
		components_TSelect::createTable();
		$result = sys::sql("SELECT `data` FROM `prefix_TSelect` WHERE `name`='".$POST['dataName']."' AND `parent_id`='".$POST['parent_id']."';",0);
		if (mysql_num_rows($result)==0){
			$POST['parentId']=$POST['parent_id'];
			components_TSelect::createStr($POST);
		}
		$name=$POST[dataName];
		
		$result = sys::sql("UPDATE `prefix_TSelect` SET `data` = '".$POST['data']."' WHERE `name`='".$POST['dataName']."' AND `parent_id`='".$POST['parent_id']."' LIMIT 1 ;",0);
		if ($param=='client'){
			return;
		} else {
		}
	}


	//=====================================
	//Функция создания записи
	function createStr($POST){
		components_TSelect::createTable();
		$parent_class=mysql_result(sys::sql("SELECT `base_class` FROM `prefix_Sections` WHERE `id`='".$POST['parentId']."';",0),0);
		$component_id=mysql_result(sys::sql("SELECT `id` FROM `prefix_ClassSections` WHERE `name`='".$POST['dataName']."' AND `parent_id`='$parent_class';",0),0);
		$defVal=sys::sql("SELECT `name`,`title` FROM `prefix_TSelect_Settings` WHERE `parent_id`='$component_id'",0);
		if (mysql_num_rows($defVal)>0){
			$defValue=mysql_result($defVal,0);
		}
		$result = sys::sql("SELECT `id` FROM `prefix_TSelect` WHERE `name`='".$POST['dataName']."' AND `parent_id`='".$POST['parentId']."'",0);
		if (mysql_num_rows($result)=='0'){
			$result = sys::sql("INSERT INTO `prefix_TSelect` ( `id` , `name` , `parent_id` , `data` ) VALUES ('','".$POST['dataName']."','".$POST['parentId']."','$defValue');",0);
		};
	}


	//=====================================
	//Функция удаления записи
	function deleteAttr($name,$id){
		components_TSelect::createTable();
		$result = sys::sql("DELETE FROM `prefix_TSelect` WHERE `parent_id` = '$id' AND `name` = '$name' LIMIT 1;",0);
	}


	//=====================================
	//Функция вывода данных
	function view($name,$parentId,$param=''){
		//components_TSelect::createTable();
		$data_child_element=sys::sql("SELECT `data` FROM `prefix_TSelect` WHERE `name`='$name' AND `parent_id`='$parentId';",0);
		if (mysql_num_rows($data_child_element)==0) {
			return '';
		} else {
			return mysql_result($data_child_element,0);
		}
	}


	//=====================================
	//Функция Создания таблицы для хранения данных
	function createTable(){
		$query=sys::sql("
			CREATE TABLE IF NOT EXISTS `prefix_TSelect` (
				`id` int(11) NOT NULL auto_increment,
				`name` varchar(255) NOT NULL,
				`parent_id` int(11) NOT NULL,
				`data` varchar(255),
				PRIMARY KEY  (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8 COLLATE utf8_general_ci AUTO_INCREMENT=1 ;",0);
		$query=sys::sql("
			CREATE TABLE IF NOT EXISTS `prefix_TSelect_Settings` (
				`id` int(11) NOT NULL auto_increment,
				`parent_id` int(11) NOT NULL,
				`title` varchar(255),
				`name` varchar(255),
				PRIMARY KEY  (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8 COLLATE utf8_general_ci AUTO_INCREMENT=1 ;",0);
	}
	
	
	//=====================================
	// Функция проверки правильности ввода
	function spell($value,$name){
	}


	//=====================================
	//Функция Проверки условий
	function condition($name,$parentId,$cond){
		return false;
	}
  
  
	//=====================================
	// Функция настроек
	function editSettings($id){

		$value=sys::sql("SELECT `id`,`name`, `title` FROM `prefix_TSelect_Settings` WHERE `parent_id`='$id';",0);

		if (mysql_num_rows($value)>0){
			while ($row = mysql_fetch_array($value)){
				$SEN['name']=$row['name'];
				$SEN['title']=$row['title'];
				$SEN['id']=$row['id'];
				$SEND['value'] .= admin::draw('TSelect/values',$SEN);
			}
		} else {
			$SEND['value'] = 'Нет значений';
		};

		$SEND['value'] .= '<br />';

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
		$SEND['js'] = 'TSelect/editRuleDialog.js';

		echo admin::draw('TSelect/editRuleDialog',$SEND);

	} 


	//=====================================
	// Функция сохранения настроек
	function saveSettings($id,$POST){
		$value=sys::sql("SELECT `title` FROM `prefix_TSelect_Settings` WHERE `parent_id`='$id' AND `name`='".$POST['name']."';",0);
		if (mysql_num_rows($value)==0){
			$result=sys::sql("INSERT INTO `prefix_TSelect_Settings` VALUES ('','$id','".$POST['title']."','".$POST['name']."')",0);
		}else{
			$result=sys::sql("UPDATE `prefix_TSelect_Settings` SET `title`='".$POST['tile']."' WHERE `parent_id`='$id' AND `name`='".$POST['name']."';",0);
		}
		echo 'Сохранение прошло успешно!';
	}  

	function delSettings($id){
		$result = sys::sql("DELETE FROM `prefix_TSelect_Settings` WHERE `id` = '$id' LIMIT 1;",0);
	}

}
?>