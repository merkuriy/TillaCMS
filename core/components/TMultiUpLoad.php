<?php

/*
 *	Компонент TMultiUpLoad
 *	======================
 *	Компонент мультизагрузки файлов
 */
class components_TMultiUpLoad{
	function edit($name,$parentId,$title){
		$SEND['id']=$parentId;
		$SEND['title'] = $title;
		$SEND['name'] = $name;
		$out = admin::draw('TMultiUpLoad/editDialog',$SEND);
		return $out;
	}
	function save($POST,$FILES, $param=''){}
	function createStr($POST){}
	function deleteAttr($name,$id){}
	function view($name,$parentId,$param=''){}
	function createTable(){}
	function condition($name,$parentId,$cond){return false;}
	
	//===================================
	// Диалог мульти загрузки фотографий
	function load($id){
	}


	//===============================================
	// функция добавления файлов через мультизагрузку
	function saveFile($id,$FILES,$name=''){
		if (isset($_POST['chunks'])){
			$fileName = $_POST['name'];
			$chunk = $_POST['chunk'];
			$out = fopen($fileName, $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen($_FILES['file']['tmp_name'], "rb");
	
				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
	
				fclose($out);
				@unlink($_FILES['file']['tmp_name']);
			}
			
			if($chunk+1==$_POST['chunks']){
				// Вызвать сохранение
				$namer = explode('_',$name);
				
				$sql = sys::sql("SELECT
									sect1.`name`+1 ind
								FROM
									`prefix_Sections` sect1
								WHERE 
									sect1.`parent_id`='$id'
								ORDER BY ind DESC
								LIMIT 1;
				",1);
		
				if (count($sql)>0){
					$name =  $sql[0]['ind'];
				}else{
					$name =  '1';
				}
		
				$POST['parent'] = $id;
				$POST['name'] = $name;
				$POST['title'] = $name;
				// Базовый класс картинки
				$POST['parent_name'] = $namer[0];
		
				$id=modules_structure_admin::addElementSCR($POST,'','client');
		
				unset($POST);
		
				$POST['name'] = $name;
				$POST['title'] = $name;
				$POST['id']=$id;
				if ($author == 'client'){
					$POST['author_id']=$_SESSION['user_ID'];
				} else {
					$POST['author_id']=$author;
				}
				// Имя компонента картинки
				$FLES[$namer[1]]['tmp_name']=$fileName;
				$FLES[$namer[1]]['type']='image/jpeg';
		
				$i=modules_structure_admin::editElementSCR($POST,$FLES,'client');
				unlink($fileName);
			}
		}
	}
}
?>