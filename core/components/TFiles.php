<?php

/*
 *	Компонент TFiles (файлы)
 *	==============================
 *	Компонент файлов (позволяет сохранять файлы на сервер) 
 */
class components_TFiles{
	
	//=====================================
	//Функция вывода на редактирование
	function edit($name, $parentId, $title){
		
		$data_child_element=sys::sql("SELECT `id`,`data` FROM `prefix_TFiles` WHERE `name`='$name' AND `parent_id`='$parentId';",0);
		$SEND = mysql_fetch_array($data_child_element);
		
		$SEND['title'] = &$title;
		$SEND['name'] = &$name;
		
		if (file_exists($SEND['data']))
			return admin::draw('TFiles/editDialog',$SEND);
		
		return admin::draw('TFiles/editDialogEmpty',$SEND);

	}

	//=====================================
	//Функция сохранения данных
	function save($POST, $FILES, $name='', $param=''){
		
		if($POST['data']=='#delete'){
			
			components_TFiles::deleteAttr($POST['dataName'], $POST['parent_id']);
			
			echo 'Файл удалён<br/>';
			
			return false;
		}
		
		
		$result = sys::sql("SELECT `data` FROM `prefix_TFiles` WHERE `name`='".$POST['dataName']."' AND `parent_id`='".$POST['parent_id']."';",0);
		if (mysql_num_rows($result)==0){
			$POST['parentId']=$POST['parent_id'];
			components_TFiles::createStr($POST);
		}
		//сохранение файла
		if(isset($FILES[$name])){
			$file = $FILES[$name]["tmp_name"];
			$file_name = $FILES[$name]["name"];
			$file_size = $FILES[$name]["size"];
			$file_type = $FILES[$name]["type"];
			$error_flag = $FILES[$name]["error"];
			// Если ошибок не было
			if($error_flag == 0){
				$fType=explode('.',$file_name);
				$fileType=$fType[count($fType)-1];
				$result = sys::sql("SELECT `id` FROM `prefix_TFiles` WHERE `name`='".$POST['dataName']."' AND `parent_id`='".$POST['parent_id']."';",0);
				$id=mysql_result($result,0);
				$newFileName='../data/files/'.$id.'.'.$fileType;
				if (move_uploaded_file($_FILES[$name]['tmp_name'], $newFileName)) {
					// Внесение данных в БД
					$result = sys::sql("UPDATE `prefix_TFiles` SET `data` = '$newFileName' WHERE `name`='".$POST['dataName']."' AND `parent_id`='".$POST['parent_id']."' LIMIT 1 ;",0);
				}
			} else {
				echo 'Ошибка загрузки файла: '.$FILES[$name]["error"].'<br>';
			}
		}
		if ($param=='client'){return;} else {}	
	}


	//=====================================
	//Функция создания записи
	function createStr($POST){
		
		$result = sys::sql("SELECT `id` FROM `prefix_TFiles` WHERE `name`='".$POST['dataName']."' AND `parent_id`='".$POST['parentId']."'",0);
		if (mysql_num_rows($result)=='0'){
			$result = sys::sql("INSERT INTO `prefix_TFiles` (`id`,`name`,`parent_id`,`data`) VALUES ('','".$POST['dataName']."','".$POST['parentId']."','');",0);
		};
	}


	//=====================================
	//Функция удаления записи
	function deleteAttr($name,$id){
		$result = sys::sql("SELECT `data` FROM `prefix_TFiles` WHERE `parent_id` = '$id' AND `name` = '$name' LIMIT 1;",0);
		
		if (!mysql_num_rows($result)) return false;
		
		$fileName=mysql_result($result,0);
		if (file_exists($fileName)){
			unlink($fileName);
		}
		
		$result = sys::sql("DELETE FROM `prefix_TFiles` WHERE `parent_id` = '$id' AND `name` = '$name' LIMIT 1;",0);
	}


	//=====================================
	//Функция вывода данных
	function view($name,$parentId,$param=''){
		
		$data_child_element=sys::sql("SELECT `data` FROM `prefix_TFiles` WHERE `name`='$name' AND `parent_id`='$parentId';",0);
		if (mysql_num_rows($data_child_element)==0) {
			$out = '';
		} else {
			$out = mysql_result($data_child_element,0);
		}
		if ($param=='filesize' and $out!=''){
			$size=filesize($out)/1048576;
			$out=substr($size, 0, strpos($size, ".")+3);
		}
		if ($param=='filetype' and $out!=''){
			$out=substr($out, strlen($out)-3, 3);
		}
		$out=str_replace("..", "", $out);
		return $out;
	}


	//=====================================
	//Функция Проверки условий
	function condition($name,$parentId,$cond){
		return false;
	}
	
}



// Инициализация компонента

if (!file_exists('../data/files')) {
	mkdir("../data/files", 0777);
};

$query=sys::sql("
	CREATE TABLE IF NOT EXISTS `prefix_TFiles` (
		`id` int(11) NOT NULL auto_increment,
		`name` varchar(255) NOT NULL,
		`parent_id` int(11) NOT NULL,
		`data` varchar(255),
		PRIMARY KEY  (`id`)
	) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8 COLLATE utf8_general_ci AUTO_INCREMENT=1
;",0);



?>