<?php

/*
 *	класс Structure с набором системных методов
 */
class modules_structure_admin{
	const TITLE = 'Контент';
	const POSITION = '1';


	//==================================================================================================
	// Функция определения адресата запроса
	function onLoad($GET,$POST,$FILES){
		if (!isset($GET['action'])){modules_structure_admin::show();}
/*		if ($GET['action']=='buildDDMenu'){modules_structure_admin::buildDDMenu($GET['author']);}
		if ($GET['action']=='buildTree'){modules_structure_admin::buildTree($GET['id'],$GET['author']);}*/
		if ($GET['action']=='findChild'){modules_structure_admin::findChild($GET['id'],$GET['author']);}
		if ($GET['action']=='addElement'){modules_structure_admin::addElement($GET['parent_id'],$GET['author'],$GET['parentHide']);}
		if ($GET['action']=='addElementSCR'){modules_structure_admin::addElementSCR($POST,$FILES,$GET['author']);}
		if ($GET['action']=='editElement'){modules_structure_admin::editElement($GET['id']);}
		if ($GET['action']=='editElementSCR'){modules_structure_admin::editElementSCR($POST,$FILES,$GET['author']);}
		if ($GET['action']=='deleteElement'){modules_structure_admin::deleteElement($GET['id'],$GET['author']);}
		if ($GET['action']=='uploader'){components_TMultiUpLoad::load($GET['id']);}
		if ($GET['action']=='multiSave'){components_TMultiUpLoad::saveFile($GET['pageid'],$FILES,$GET['name']);}
		if ($GET['action']=='updatePosition'){modules_structure_admin::updatePosition($GET['id'],$GET['parent'],$GET['pos']);}
		if ($GET['action']=='getParent'){modules_structure_admin::getParent($GET['id']);}
	}
	// Функция определения адресата запроса
	//==================================================================================================




	//==================================================================================================
	// Функция вывода главной страницы структуры
	function show(){
		if ($_SERVER['REDIRECT_URL']=='/panel'){
			header("Location: /panel/structure");
		}
		$sql = sys::sql("SELECT `id` FROM `prefix_Sections` WHERE `id`='1';",0);						// Проверяем наличие в БД Главного Меню

		if (mysql_num_rows($sql)==0){
			$sql = sys::sql("INSERT INTO `prefix_Sections` VALUES ('1','0','0','MainMenu','Главное меню','1','');",0);
		}
		
		$SEND['path'] = 'Структура';
		$SEND['content'] = admin::draw('structure/page',$SEND);											// Формируем контент
		$SEND['js'] = 'structure/js.js';																// Указываем файл JavaScript'а
		$SEND['title'] = 'Структура';																	// Указываем заголовое страницы
		
		echo admin::draw('page_index',$SEND);															// Выводим админку

	}
	// Функция вывода главной страницы структуры
	//==================================================================================================



	//==================================================================================================
    // Функция поиска дочерних элементов
    function findChild($id,$author=''){
        $sql = sys::sql("
            SELECT sect1.`id` , sect1.`title`,sect1.`pos`, COUNT(sect2.`parent_id`) countChild
            FROM
                (
                    SELECT sect.`id` , sect.`title`, sect.`pos`
                    FROM
                        `prefix_Sections` sect
                    WHERE sect.`parent_id` = '$id'
                ) sect1
            LEFT JOIN
                `prefix_Sections` sect2
            ON sect1.`id` = sect2.`parent_id`
            GROUP BY sect1.`id`
            ORDER BY sect1.`pos`, sect1.`id`
        ",0);

        while ($res = mysql_fetch_array($sql)){
            if ($res['countChild']>0){
                $child[] = array('label'=>$res['title'], 'id'=>$res['id'], 'items'=>'ajax', 'url'=>'?module=structure&action=findChild&id='.$res['id'].'&author=admin');
            }else{
                $child[] = array('label'=>$res['title'], 'id'=>$res['id']);
            }
        }

        echo json_encode($child);
    }
    // Функция поиска дочерних элементов
    //==================================================================================================




	//==================================================================================================
	// Диалог создания нового элемента
	function addElement($parent_id,$author='',$parentHide=''){

		if ($parent_id==0){																				// Если создается корень
		
			$BT['value'] = 1;
			$BT['title'] = 'Корень'; 
			$BT['name'] = 'root';
			$SEND['option'] = admin::draw('structure/select.option',$BT);
			
		}else{
		
			// Доступные базовые типы
			$sql = sys::sql("SELECT
								class.`id`,
								class.`title`,
								class.`name`
							FROM
								`prefix_ClassSections` bclass,
								`prefix_ClassSections` class,
								`prefix_Sections` sect
							WHERE
								sect.`id` = '$parent_id' AND
								bclass.`parent_id` = sect.`base_class` AND
								bclass.`type` = 'type_children' AND
								class.`name` = bclass.`name` AND
								class.`type` = 'type'
							GROUP BY bclass.`title`
							ORDER BY bclass.`id`;
			",0);
			
			if (mysql_num_rows($sql)>0){
			
				while ($res = mysql_fetch_array($sql)){													// Формируем список допустимых базовых классов
					$BT['value'] = $res['id'];
					$BT['title'] = $res['title']; 
					$BT['name'] = $res['name'];
					$SEND['option'] .= admin::draw('structure/select.option',$BT);
				}
				
			}else{
				
				/*
				*	Если у выбранного элемента в базовом классе нет дочерних элементов
				*	выводим сообщение об ошибке				
				*/				
				
				$REP['title'] = 'Ошибка: Невозможно создать дочерний элемент';
				$REP['report'] = 'В Базовом Классе родительского элемента не указан ни один тип дочернего элемента. Перейти к редактированию базового класса.';
				echo admin::draw('structure/report',$REP);
				return;
				
			}
			
		}

		$SEND['js'] = 'structure/addDialog.js';															// Указываем файл JavaScript
		$SEND['parent'] = $parent_id;																	// Указываем родительский ID
		$SEND['parentHide'] = $parentHide;

		/****************************/
		// Определение чернового имени
		$sql = sys::sql("SELECT
							sect1.`name`+1 ind
						FROM
							`prefix_Sections` sect1
						WHERE 
							sect1.`parent_id`='$parent_id'
						ORDER BY ind DESC
						LIMIT 1;
		",1);

		if (count($sql)>0){
			$SEND['name'] = $sql[0]['ind'];
		}else{
			$SEND['name'] = '1';
		}
		$SEND['title'] = $SEND['name'];
		// Определение чернового имени
		/****************************/

		echo admin::draw('structure/addDialog',$SEND);													// Выводим диалог

	}
	// Диалог создания нового элемента
	//==================================================================================================




	//==================================================================================================
	// Функция создания нового элемента
	function addElementSCR($POST,$FILES='',$author=''){

		// Проверяем на наличие данных для создания элемента
		if (isset($POST) and isset($POST['name']) and $POST["name"]>""){
			
			if ($author == 'client'){
				$parent_Class_id = sys::sql("SELECT `id` FROM `prefix_ClassSections` WHERE `name`='".$POST['parent_name']."' AND `type`='type';",0);
				$POST['type'] = mysql_result($parent_Class_id,0);
			}
	
			$sql = sys::sql("SELECT MAX(`pos`) pos FROM `prefix_Sections` WHERE `parent_id`='".$POST['parent']."'",1);
			$pos = $sql[0]['pos']+1;
	
			// Внесение элемента в БД (таблица _Sections)
			$sql = sys::sql("INSERT
							INTO
								`prefix_Sections`
							VALUES (
								'',
								'".$pos."',
								'".$POST['parent']."',
								'".htmlspecialchars($POST['name'])."',
								'".htmlspecialchars($POST['title'])."',
								'".$POST['type']."',
								''
							)
			;",0);
	
			// Узнаем ID созданного элемента
			$id=mysql_insert_id();
	
			// Определение дочерних элементов 
			$sql = sys::sql("SELECT
								`id`,
								`name`,
								`value`
							FROM
								`prefix_ClassSections`
							WHERE
								`parent_id`='".$POST['type']."' AND
								`type`='attr';
			",0);
	
			while($row = mysql_fetch_array($sql)){
				$POST["parentId"]=$id;
				$POST['dataName']=$row['name'];
				eval('components_'.$row["value"].'::createStr($POST);');
			}
	
			// Выводим диалог редактирования элемента
			if ($author == 'client'){ return $id; }
	
			modules_structure_admin::editElement($id);
		}

	}
	// Функция создания нового элемента
	//==================================================================================================




	//==================================================================================================
	// Диалог редактирования элемента структуры
	function editElement($id){


		// Выборка данных о редактируемом элементе
		$sql = sys::sql("
			SELECT
				sect.`name`,
				sect.`title`,
				class.`title`,
				class.`id`,
				sect.`parent_id`
			FROM
				`prefix_Sections` sect,
				`prefix_ClassSections` class
			WHERE
				sect.`id` = '$id' AND
				class.`id` = sect.`base_class`
		",0);

		$page_datas = mysql_fetch_array($sql);															// Преобразуем данные из БД в массив

		/**************************/
		// Формируем URL страницы
		if ($id!=1){
			$parentID = $page_datas['parent_id'];
			$adres = '/'.$page_datas['0'];
			while($parentID!=0){
				$sql = sys::sql("
					SELECT
						sect.`name`,
						sect.`parent_id`
					FROM
						`prefix_Sections` sect
					WHERE
						sect.`id` = '$parentID'
				",0);
				$URL_datas = mysql_fetch_array($sql);
				if ($parentID!='1'){
					$adres = '/'.$URL_datas['name'].$adres;
				}
				$parentID = $URL_datas['parent_id'];
			}
		}else{
			$adres = '/';
		}
		/**************************/
		
		/**************************/
		// Формируем массив на вывод
		$SEND['js'] = 'structure/editDialog.js';
		$SEND['name'] = $page_datas['0'];
		$SEND['title'] = $page_datas['1'];
		$SEND['base_class'] = $page_datas['2'];
		$SEND['id'] = $id;
		$SEND['adres'] = $adres;
		$SEND['absoluteAdres'] = 'http://www.'.$_SERVER[HTTP_HOST].$adres;
		// Формируем массив на вывод
		/**************************/
		
		// Выборка дочерних элементов
    	$sql = sys::sql("
			SELECT
				`name`,
				`title`,
				`value`
			FROM
				`prefix_ClassSections`
			WHERE
				`parent_id`='".$page_datas['3']."'
			ORDER BY `id`;
		",0);

		// Вывод дочерних элементов на редактирование
		while($row = mysql_fetch_array($sql)){
			if ($row['value']!=''){
				$POST['dataName']=$row['name'];
				$POST['parentId']=$id;
				eval('components_'.$row['value'].'::createStr($POST);');
				$TComponent=$row['value'];
				$name=$row['name'];
				$title=$row['title'];
				eval('$SEND["content"] .= components_'.$TComponent.'::edit($name,$id,$title);');
      		}
		}

		echo admin::draw('structure/editDialog',$SEND);													// Вывод диалога редактирования

	}
	// Диалог редактирования элемента структуры
	//==================================================================================================




	//==================================================================================================
	// Функция сохранения изменений в контенте элемента
	function editElementSCR($POST, $FILES, $author=''){
		if( isset($_GET['id']) && isset($_GET['name']) ){
			
			$sql = sys::sql("
				SELECT
					class.`value`
				FROM
					`prefix_Sections` sect,
					`prefix_ClassSections` class
				WHERE
					sect.`id` = ".$_GET['id']." AND
					class.`name` = '".$_GET['name']."' AND
					class.`parent_id` = sect.`base_class`
				LIMIT 1
			",0);
			
			
			
			if( mysql_num_rows($sql) ){
					
				$component = mysql_result($sql,0);
				$POSTC['parent_id'] = $_GET['id'];
				$POSTC['dataName'] = $_GET['name'];
				
				eval('components_'.$component.'::save($POSTC,$_FILES);');
			};
			
			return ;
		}
		
		if( isset($POST['name']) and isset($POST['title']) ){
			// Обновляем Title и Name
			$sql = sys::sql("
				UPDATE
					`prefix_Sections`
				SET
					`name` = '".$POST['name']."',
					`title` = '".htmlspecialchars($POST['title'])."'
				WHERE
					`id` = '".$POST['id']."'
				LIMIT 1;
			",0);
		}
		
		$id = $POST['id'];
		
		unset($POST['id']);
		unset($POST['name']);
		unset($POST['title']);

		foreach ($POST as $key => $value){
						
			$keyShort=explode('/',$key);
			if( count($keyShort)==1 ){
				
				
				
				$sql = sys::sql("
					SELECT
						class.`value`
					FROM
						`prefix_Sections` sect,
						`prefix_ClassSections` class
					WHERE
						sect.`id` = ".$id." AND
						class.`name` = '$key' AND
						class.`parent_id` = sect.`base_class`
					LIMIT 1
				",0);
				
				if( mysql_num_rows($sql) ){
					
					$component=mysql_result($sql,0);
					$POSTC['parent_id']=$id;
					$POSTC['dataName']=$key;
					$POSTC['data']=$value;
					
					eval('components_'.$component.'::save($POSTC,$_FILES);');
				};
				
			} else {
				
				if ($keyShort[1]=='pass'){

					$sql = sys::sql("
						SELECT
							class.`value`
						FROM
							`prefix_Sections` sect,
							`prefix_ClassSections` class
						WHERE
							sect.`id` = '".$id."' AND
							class.`name` = '".$keyShort[0]."' AND
							class.`parent_id` = sect.`base_class`
					",0);

					if (mysql_num_rows($result)){
						$component=mysql_result($result,0);
						$POSTC['parent_id'] = $id;
						$POSTC['dataName']=$keyShort[0];
						$POSTC['pass']=$value;
						$POSTC['group']=$POST[$keyShort[0].'/group'];
						eval('components_'.$component.'::save($POSTC,$_FILES);');
					};
				}
			}
    	}
		if (!$FILES==''){
			foreach ($FILES as $key => $value) {
				$keyShort=explode('/',$key);
				if ($keyShort[1]=='pict'){} else {

					$sql = sys::sql("SELECT
										class.`value`
									FROM
										`prefix_Sections` sect,
										`prefix_ClassSections` class
									WHERE
										sect.`id` = '".$id."' AND
										class.`name` = '".$keyShort[0]."' AND
										class.`parent_id` = sect.`base_class`
					",0);
					if (mysql_num_rows($sql)>0){
						$component=mysql_result($sql,0);
						$POSTC['parent_id']=$id;
						$POSTC['dataName']=$keyShort[0];
						$POSTC['data']=$value;
						eval('components_'.$component.'::save($POSTC,$FILES,$keyShort[0]);');
					};
				};
			}
    	}

		if ($author != 'client'){
			echo 'Изменения сохранены';
		}

	}
	// Функция сохранения изменений в контенте элемента
	//==================================================================================================




	//==================================================================================================
	// Функция удаления элемента
	function deleteElement($id,$author = ''){

		if (isset($id) and $id>0){
			// Узнаем атрибуты
			$sql = sys::sql("SELECT
								class.`value`,
								class.`name`
							FROM
								`prefix_Sections` sect,
								`prefix_ClassSections` class
							WHERE
								class.`parent_id` = sect.`base_class` AND
								class.`type` = 'attr' AND
								sect.`id` = '$id'
			;",0);
	
			// Удаляем атрибуты
			while ($row = mysql_fetch_array($sql)){
				eval('components_'.$row['value'].'::deleteAttr($row["name"],$id);');
			}
	
			// Находим все дочерние элементы
			$sql = sys::sql("SELECT
								`id`
							FROM
								`prefix_Sections`
							WHERE
								`parent_id` = '$id'
			;",0);
	
			// Вызываем удаление дочерних элементов
			while ($row = mysql_fetch_array($sql)){
				modules_structure_admin::deleteElement($row['id']);
			}
	
	
			// Удаляем сам элемент
			$sql = sys::sql("DELETE FROM `prefix_Sections` WHERE `id` = '$id' LIMIT 1;",0);
	
			if ($author == 'admin'){
				echo 'Удаление успешно завершено!';
			}
		}
	}
	// Функция удаления элемента
	//==================================================================================================

	//==================================================================================================
	// Функция обновления позиции раздела
	function updatePosition($id, $parent, $pos){
        
		//echo $id.'==='.$parent.'==='.$pos."\n\n\n";
        
		$sql = sys::sql("
            SET @newPos := -1;
        ;",0);
        
        $sql = sys::sql("
            UPDATE
                `prefix_Sections`
            SET
                `pos` = @newPos := @newPos + if( @newPos = ".($pos-1).", 2, 1 )
            WHERE
                `parent_id` = '$parent' AND
                `id` <> $id
            ORDER BY
                `pos`, `id`
        ;",0);
        
        $sql = sys::sql("
            UPDATE
                `prefix_Sections`
            SET
                `pos` = $pos,
                `parent_id` = '$parent'
            WHERE
                `id` = $id
        ;",0);
        
        
    }
    // Функция обновления позиции
    //==================================================================================================

	function getParent($id){
		if ($id == 0){
			$child[] = array('id'=>'0','label'=>'Корень');
			echo json_encode($child);
		}else{
			$sql = sys::sql("SELECT `parent_id` FROM `prefix_Sections` WHERE `id`='".$id."'",1);
			if ($sql[0]['parent_id']=='0'){
				$child[] = array('id'=>$sql[0]['parent_id'],'label'=>'Корень');
				echo json_encode($child);
			}else{
				$sq2 = sys::sql("SELECT `title` FROM `prefix_Sections` WHERE `id`='".$sql[0]['parent_id']."'",1);
				$child[] = array('id'=>$sql[0]['parent_id'],'label'=>$sq2[0]['title']);
				echo json_encode($child);
			}
		}
	}

	//==================================================================================================
	// Функция создания таблицы
	function createTable(){

		$sql = sys::sql("CREATE TABLE IF NOT EXISTS
							`prefix_Sections` (
								`id` int(11) NOT NULL auto_increment,
								`pos` int(11) NOT NULL,
								`parent_id` int(11) NOT NULL,
								`name` varchar(255) NOT NULL,
								`title` varchar(255) NOT NULL,
								`base_class` int(11) NOT NULL,
								`atributes` varchar(255) NOT NULL,
								PRIMARY KEY  (`id`)
							)
							ENGINE=MyISAM
							AUTO_INCREMENT=1
							CHARACTER SET utf8 COLLATE utf8_general_ci
							COMMENT='Таблица Структуры'
							AUTO_INCREMENT=1
		;",0);

		$sql = sys::sql("INSERT INTO
							`prefix_Sections`
						VALUES (
							'',
							'0',
							'0',
							'MainMenu',
							'Главное меню',
							'1',
							''
						)
		;",0);

	}
	// Функция создания таблицы
	//==================================================================================================
}
// Конец класса
?>