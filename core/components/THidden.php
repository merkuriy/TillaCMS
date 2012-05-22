<?php

/*
 *	Компонент THidden
 *	=================
 *	Компонент хранения системных значений
 */
class components_THidden {
	
	public static $ReservedWords = array(
		'count' => '0-9',
		'abstractChild' => '',
		'abstractParent' => ''
	);
	
	
	//=====================================
	//Функция вывода данных
	function view($name,$parentId,$param=''){
		
		$data_child_element=sys::sql("SELECT `data` FROM `prefix_THidden` WHERE `name`='$name' AND `parent_id`='$parentId';",0);
		
		if (mysql_num_rows($data_child_element))
			return mysql_result($data_child_element,0);
		
		return '';
	}
	
	
	
	//=====================================
	//Функция вывода на редактирование
	function edit($name,$parentId,$title){
		components_THidden::createTable();
		$data_child_element=sys::sql("SELECT `id`,`data` FROM `prefix_THidden` WHERE `name`='$name' AND `parent_id`='$parentId';",0);
		$data=mysql_fetch_array($data_child_element);	
		$SEND['title'] = $title;
		$SEND['name'] = $name;
		$SEND['data'] = $data['data'];
		$out = admin::draw('THidden/editDialog',$SEND);
		return $out;
	}


	//=====================================
	//Функция сохранения данных
	function save($POST,$FILES, $param=''){
		components_THidden::createTable();
		$result = sys::sql("SELECT `data` FROM `prefix_THidden` WHERE `name`='".$POST['dataName']."' AND `parent_id`='".$POST['parent_id']."';",0);
		if (mysql_num_rows($result)==0){
			$POST['parentId']=$POST['parent_id'];
			components_THidden::createStr($POST);
		}
		$name=$POST[dataName];
		$spell=components_THidden::spell($POST['data'],$name); 
		if ($spell){
			$result = sys::sql("UPDATE `prefix_THidden` SET `data` = '".$POST['data']."' WHERE `name`='".$POST['dataName']."' AND `parent_id`='".$POST['parent_id']."' LIMIT 1 ;",0);
			if ($param=='client'){
				return;
			} else {
			}
		} else {
			echo 'Несовместимый тип данных!';
		};
	}


	//=====================================
	//Функция создания записи
	function createStr($POST){
		components_THidden::createTable();
		$parent_class=mysql_result(sys::sql("SELECT `base_class` FROM `prefix_Sections` WHERE `id`='".$POST['parentId']."';",0),0);
		$component_id=mysql_result(sys::sql("SELECT `id` FROM `prefix_ClassSections` WHERE `name`='".$POST['dataName']."' AND `parent_id`='$parent_class';",0),0);
		$defVal=sys::sql("SELECT `value` FROM `prefix_THidden_Settings` WHERE `parent_id`='$component_id'",0);
		if (mysql_num_rows($defVal)>0){
			$defValue=mysql_result($defVal,0);
		}
		$result = sys::sql("SELECT `id` FROM `prefix_THidden` WHERE `name`='".$POST['dataName']."' AND `parent_id`='".$POST['parentId']."'",0);
		if (mysql_num_rows($result)=='0'){
			$result = sys::sql("INSERT INTO `prefix_THidden` ( `id` , `name` , `parent_id` , `data` ) VALUES ('','".$POST['dataName']."','".$POST['parentId']."','$defValue');",0);
		};
	}


	//=====================================
	//Функция удаления записи
	function deleteAttr($name,$id){
		components_THidden::createTable();
		$result = sys::sql("DELETE FROM `prefix_THidden` WHERE `parent_id` = '$id' AND `name` = '$name' LIMIT 1;",0);
	}
	

	//=====================================
	//Функция Создания таблицы для хранения данных
	function createTable(){
		$query=sys::sql("
			CREATE TABLE IF NOT EXISTS `prefix_THidden` (
				`id` int(11) NOT NULL auto_increment,
				`name` varchar(255) NOT NULL,
				`parent_id` int(11) NOT NULL,
				`data` varchar(255),
				PRIMARY KEY  (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8 COLLATE utf8_general_ci AUTO_INCREMENT=1 ;",0);
		$query=sys::sql("
			CREATE TABLE IF NOT EXISTS `prefix_THidden_Settings` (
				`id` int(11) NOT NULL auto_increment,
				`parent_id` int(11) NOT NULL,
				`value` varchar(255),
				PRIMARY KEY  (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8 COLLATE utf8_general_ci AUTO_INCREMENT=1 ;",0);
	}
	
	
	//=====================================
	// Функция проверки правильности ввода
	function spell($value,$name){
		$word=explode("_", $name);
		$ReservedWords=self::$ReservedWords;
		if (isset($ReservedWords[$word[0]])){
			$spell=false;
			// числовое значение
			if ($ReservedWords[$word[0]]=='0-9'){
				for ($x=0;$x<strlen($value);$x++){
					if ((ord(substr($value, $x, 1))>47) and (ord(substr($value, $x, 1))<58)){
						$spell=true;
					} else {
						$spell=false;
					}
				}
			}
			if ($ReservedWords[$word[0]]==''){
				$spell=true;
			}
		} else {
			return true;
		};
		if ($spell){
			return true;
		} else {
			return false;
		};
		return true;
	}


	//=====================================
	//Функция Проверки условий
	function condition($name,$parentId,$cond){
		return false;
	}
  
  
	//=====================================
	// Функция настроек
	function editSettings($id){
		
		components_THidden::createTable();
		
		$value=sys::sql("SELECT `value` FROM `prefix_THidden_Settings` WHERE `parent_id`='$id';",0);

		if (mysql_num_rows($value)>0){
			$SEND['value']=mysql_result($value,0);
		} else {
			$SEND['value']='';
		};

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
		$SEND['js'] = 'THidden/editRuleDialog.js';

		echo admin::draw('THidden/editRuleDialog',$SEND);

	} 


	//=====================================
	// Функция сохранения настроек
	function saveSettings($id,$POST){
		$value=sys::sql("SELECT `value` FROM `prefix_THidden_Settings` WHERE `parent_id`='$id';",0);
		if (mysql_num_rows($value)==0){
			$result=sys::sql("INSERT INTO `prefix_THidden_Settings` VALUES ('','$id','".$POST['value']."')",0);
		}else{
			$result=sys::sql("UPDATE `prefix_THidden_Settings` SET `value`='".$POST['value']."' WHERE `parent_id`='$id';",0);
		}
		echo 'Сохранение прошло успешно!';
	}  
}
?>