<?php

/*
 *	Компонент TNFiles (файлы)
 *	==============================
 *	Компонент файлов (позволяет сохранять файлы на сервер) 
 */

class components_TNFiles {
	
	//=====================================
	//Функция вывода данных
	function view($name, $parentId, $param=''){
		return '';
	}
	
	
	//=====================================
	//Функция вывода на редактирование
	function edit($name, $id, $title){
		
		$SEND['id']=$id;
		$SEND['title'] = $title;
		$SEND['name'] = $name;
		
		$path = '../data/files/'.$id;
		
		
		if( file_exists($path) ){
			
			$files = scandir( $path );
			
			unset( $files[0] );
			unset( $files[1] );
			
			$SEND['files'] = '';
			
			foreach( $files as $val ){
				$SEND['files'] .= admin::draw('TNFiles/file', array(
					'id' => $id,
					'val' => $val
				));
			}
			
		}
		
		return admin::draw('TNFiles/editDialog',$SEND);;

	}

	//=====================================
	//Функция сохранения данных
	function save($POST,$FILES,$name='', $param=''){
		
		//file_put_contents('1.txt', print_r($GLOBALS, 1), FILE_APPEND );
		
		// удаление файла
		if( substr($POST['data'], 0, 7) == '#delete'){
			
			unlink( '../data/files/'.$POST['parent_id'].'/'.substr($POST['data'], 8) );
			
			echo 'Файл удалён<br/>';
			
			return false;
		}
		
		
		if (isset($_POST['chunks'])){
			
			$path = '../data/files/'.$POST['parent_id'];
			
			if ($_POST['chunk'] == 0){
				//если это первая часть файла
				
				if (!file_exists($path))
					mkdir($path, 0755);
				
				move_uploaded_file($_FILES['file']['tmp_name'], $path.'/'.$_FILES['file']['name']);
				
			}else{
				
				$file = fopen($path.'/'.$_FILES['file']['name'], "ab");
				
				if ($file) {
					// Read binary input stream and append it to temp file
					$in = fopen($_FILES['file']['tmp_name'], "rb");
		
					if ($in) {
						while ($buff = fread($in, 4096))
							fwrite($file, $buff);
					} else
						die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
		
					fclose($file);
					unlink($_FILES['file']['tmp_name']);
				}
			}
			
			
			
			
		}
		
	}


	//=====================================
	//Функция создания записи
	function createStr($POST){
		
		return false;
	}


	//=====================================
	//Функция удаления записи
	function deleteAttr($name,$id){
		
		if( !file_exists('../data/files/'.$id) )
			return false;
		
		
		function deldir($d){
			
			$dh = opendir( $d );
			
			while( false !== ($f = readdir($dh)) ){
				
				if ( $f != "." && $f != ".." ){
					
					$path = $d . "/" . $f;
					if( is_dir( $path ) ){
						//$deldir($path);
					}else
						unlink( $path );
				}
			}
			
			rmdir( $d );
		};
		
		deldir('../data/files/'.$id);
		
	}


	//=====================================
	//Функция Проверки условий
	function condition($name,$parentId,$cond){
		return false;
	}
}

//инициализация компонента
if (!file_exists('../data/files')) {
	mkdir("../data/files", 0777);
};


?>