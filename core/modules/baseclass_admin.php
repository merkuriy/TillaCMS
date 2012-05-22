<?php

/**
 *	класс BaseClass с набором системных методов
 */
class modules_baseclass_admin{
	const TITLE = 'Базовые классы';
	const POSITION = '2';


	//==================================================================================================
	// Функция определения адресата запроса
	function onLoad($GET,$POST,$FILES){
		if (!isset($GET['action'])){modules_baseclass_admin::show();}
		if ($GET['action']=='buildTree'){modules_baseclass_admin::buildTree($GET['author']);}
		if ($GET['action']=='addClass'){modules_baseclass_admin::addClass($GET['author']);}
		if ($GET['action']=='addClassSCR'){modules_baseclass_admin::addClassSCR($POST);}
		if ($GET['action']=='editClass'){modules_baseclass_admin::editClass($GET['id'],$GET['author']);}
		if ($GET['action']=='getChildClass'){modules_baseclass_admin::getChildClass($GET['id'],$GET['author']);}
		if ($GET['action']=='getChildAttr'){modules_baseclass_admin::getChildAttr($GET['id'],$GET['author']);}
		if ($GET['action']=='saveClass'){modules_baseclass_admin::saveClass($GET['id'],$POST);}
		if ($GET['action']=='addChildClass'){modules_baseclass_admin::addChildClass($GET['id'],$POST);}
		if ($GET['action']=='addChildAttr'){modules_baseclass_admin::addChildAttr($GET['id'],$POST);}
		if ($GET['action']=='delChildClass'){modules_baseclass_admin::delChildClass($GET['id']);}
		if ($GET['action']=='delChildAttr'){modules_baseclass_admin::delChildAttr($GET['id']);}
		if ($GET['action']=='deleteClass'){modules_baseclass_admin::deleteClass($GET['id'],$GET['author']);}
		if ($GET['action']=='editComponentSettings') {modules_baseclass_admin::editComponentSettings($GET['id']);}
		if ($GET['action']=='saveComponentSettings') {modules_baseclass_admin::saveComponentSettings($GET['component'],$GET['id'],$POST);}
		if ($GET['action']=='createComponentSettings') {modules_baseclass_admin::createComponentSettings($GET['id'],$POST);}
		if ($GET['action']=='delComponentSettings') {modules_baseclass_admin::delComponentSettings($GET['component'],$GET['id']);}
		if ($GET['action']=='renameChildAttr') {modules_baseclass_admin::renameChildAttr($GET['newTitle'],$GET['id']);}
	}
	// Функция определения адресата запроса
	//==================================================================================================
	
	
	
	
	//==================================================================================================
	// Функция вывода главной страницы Базовых Классов
	function show(){
		$sql = sys::sql("SELECT `id` FROM `prefix_ClassSections` WHERE `id`='1';",0);					// Проверяем наличие в БД Корня

		if (mysql_num_rows($sql)==0){
			$sql = sys::sql("INSERT INTO `prefix_ClassSections` VALUES ('1','0','root','Корень','type','');",0);
		}
		
		$SEND['tree'] = modules_baseclass_admin::buildTree();											// Формируем дерево
		$SEND['path'] = 'Базовые Классы';
		$SEND['content'] = admin::draw('baseclass/page',$SEND);											// Формируем контент
		$SEND['js'] = 'baseclass/js.js';																// Указываем файл JavaScript'а
		$SEND['title'] = 'Базовые Классы';																// Указываем заголовое страницы
		
		echo admin::draw('page_index',$SEND);															// Выводим админку
	}
	// Функция вывода главной страницы Базовых Классов
	//==================================================================================================




	//==================================================================================================
	// Функция построения дерева базовых классов
	function buildTree($author = ''){

		$sql = sys::sql("SELECT
							`id`,
							`title`
						FROM
							`prefix_ClassSections`
						WHERE
							`type` = 'type'
						ORDER BY `id`
		;",0);

		while ($row = mysql_fetch_array($sql)){
			$SEND['childNode'] .= admin::draw('baseclass/tree.element',$row); 
		}

		$SEND['title'] = 'Базовые классы';
		$SEND['id'] = '0';

		if ($author=='admin'){
			echo admin::draw('baseclass/tree',$SEND);
		}else{
			return admin::draw('baseclass/tree',$SEND);
		}

	}
	// Функция построения дерева базовых классов
	//==================================================================================================




	//==================================================================================================
	// Диалог создания Базового Класса
	function addClass($author){

		$SEND['js'] = 'baseclass/addDialog.js';

		/****************************/
		// Определение чернового имени
		$sql = sys::sql("SELECT
							class.`name`+1 ind
						FROM
							`prefix_ClassSections` class
						WHERE 
							class.`type` = 'type'
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

		echo admin::draw('baseclass/addDialog',$SEND);													// Выводим диалог

	}
	// Диалог создания Базового Класса
	//==================================================================================================




	//==================================================================================================
	// Функция создания Базового Класса
	function addClassSCR($POST){

		$sql = sys::sql("INSERT
						INTO `prefix_ClassSections`
						VALUES ('','0','".$POST['name']."','".$POST['title']."','type','')
		;",0);

		$id = mysql_insert_id();

		modules_baseclass_admin::editClass($id);

	}
	// Функция создания Базового Класса
	//==================================================================================================




	//==================================================================================================
	// Диалог редактирования Базового Класса
	function editClass($id,$author = ''){

		$sql = sys::sql("
			SELECT
				`name`,
				`title`
			FROM
				`prefix_ClassSections`
			WHERE
				`id` = '$id'
		;",0);
		
		$SEND = mysql_fetch_array($sql);
		$SEND['id'] = $id;
		$SEND['childElement'] = modules_baseclass_admin::getChildClass($id);
		$SEND['attr'] = modules_baseclass_admin::getChildAttr($id);
		$SEND['childClassOption'] = modules_baseclass_admin::getChildClassOption();
		$SEND['childAttrOption'] = modules_baseclass_admin::getChildAttrOption();
		$SEND['js'] = 'baseclass/editDialog.js';

		echo admin::draw('baseclass/editDialog',$SEND);

	}
	// Диалог редактирования Базового Класса
	//==================================================================================================




	//==================================================================================================
	// Функция вывода дочерних классов
	function getChildClass($id,$author = ''){

		$sql = sys::sql("SELECT
							`name`,
							`title`,
							`id`
						FROM
							`prefix_ClassSections`
						WHERE
							`parent_id` = '$id' AND
							`type` = 'type_children'
		",0);

		while ($row = mysql_fetch_array($sql)){
			$out .= admin::draw('baseclass/editDialog.child',$row);
		}

		if ($author == 'admin'){
			echo $out;
		}else{
			return $out;
		}

	}
	// Функция вывода дочерних классов
	//==================================================================================================




	//==================================================================================================
	// Функция вывода дочерних атрибутов
	function getChildAttr($id,$author = ''){

		$sql = sys::sql("
			SELECT
				`name`,
				`title`,
				`id`,
				`value`
			FROM
				`prefix_ClassSections`
			WHERE
				`parent_id` = '$id' AND
				`type` = 'attr'
		",0);

		while ($row = mysql_fetch_array($sql)){

			eval('$class_methods = get_class_methods(components_'.$row['value'].');');
			for ($x=0; $x<count($class_methods); $x++){
				if ($class_methods[$x]=='editSettings'){$row['settings']=admin::draw('baseclass/editDialog.settings',$row);};
			}

			$out .= admin::draw('baseclass/editDialog.attr',$row);
		}

		if ($author == 'admin'){
			echo $out;
		}else{
			return $out;
		}

	}
	// Функция вывода дочерних атрибутов
	//==================================================================================================




	//==================================================================================================
	// Функция вывода добавляемых дочерних классов
	function getChildClassOption(){

		$sql = sys::sql("SELECT
							`id`,
							`title`
						FROM
							`prefix_ClassSections`
						WHERE
							`type` = 'type' AND
							`id` > 1
		;",0);

		while ($row = mysql_fetch_array($sql)){
			$out .= admin::draw('baseclass/select.option',$row);
		}

		return $out;

	}
	// Функция вывода добавляемых дочерних классов
	//==================================================================================================




	//==================================================================================================
	// Функция вывода добавляемых дочерних атрибутов
	function getChildAttrOption(){

		$component = admin::components();

		for ($x=0; $x<count($component); $x++){
			$SEND['id'] = $component[$x];
			$SEND['title'] = $component[$x];
			$out .= admin::draw('baseclass/select.option',$SEND);
		}

		return $out;

	}
	// Функция вывода добавляемых дочерних атрибутов
	//==================================================================================================




	//==================================================================================================
	// Функция сохранения изменений в Базовом классе
	function saveClass($id,$POST){

		$sql = sys::sql("UPDATE
							`prefix_ClassSections`
						SET
							`title` = '".$POST['title']."',
							`name` = '".$POST['name']."'
						WHERE
							`id` = '$id'
		;",0);

		echo 'Изменения успешно сохранены!';

	}
	// Функция сохранения изменений в Базовом классе
	//==================================================================================================




	//==================================================================================================
	// Функция добавления дочернего класса
	function addChildClass($id,$POST){

		$sql = sys::sql("SELECT
							`title`,
							`name`
						FROM
							`prefix_ClassSections`
						WHERE
							`id` = '".$POST['type']."'
		;",0);

		$type = mysql_fetch_array($sql);

		$sql = sys::sql("INSERT	INTO
							`prefix_ClassSections`
						VALUES (
							'',
							'$id',
							'".$type['name']."',
							'".$type['title']."',
							'type_children',
							''
						)
		;",0);

		modules_baseclass_admin::getChildClass($id,'admin');

	}
	// Функция добавления дочернего класса
	//==================================================================================================




	//==================================================================================================
	// Функция добавления дочернего атрибута
	function addChildAttr($id,$POST){

		$sql = sys::sql("INSERT	INTO
							`prefix_ClassSections`
						VALUES (
							'',
							'$id',
							'".$POST['name']."',
							'".$POST['title']."',
							'attr',
							'".$POST['component']."'
						)
		;",0);

		modules_baseclass_admin::getChildAttr($id,'admin');

	}
	// Функция добавления дочернего атрибута
	//==================================================================================================




	//==================================================================================================
	// Функция удаления дочернего класса
	function delChildClass($id){

		$sql = sys::sql("SELECT
							`parent_id`
						FROM
							`prefix_ClassSections`
						WHERE
							`id` = '$id'
						LIMIT 1
		;",0);

		$parent_id = mysql_result($sql,0);

		$sql = sys::sql("DELETE
						FROM
							`prefix_ClassSections`
						WHERE
							`id` = '$id'
						LIMIT 1
		;",0);

		modules_baseclass_admin::getChildClass($parent_id,'admin');

	}
	// Функция удаления дочернего класса
	//==================================================================================================




	//==================================================================================================
	// Функция удаления дочернего атрибута
	function delChildAttr($id){

		$parent_id = mysql_result(sys::sql("SELECT `parent_id` FROM `prefix_ClassSections` WHERE `id`='$id';",0),0);

		$component = mysql_result(sys::sql("SELECT `value` FROM `prefix_ClassSections` WHERE `id` ='$id';",0),0);

		if ($component!=''){
			$name = mysql_result(sys::sql("SELECT `name` FROM `prefix_ClassSections` WHERE `id` ='$id';",0),0);
			$based_id = sys::sql("SELECT `id` FROM `prefix_Sections` WHERE `base_class` ='$parent_id';",0);
			while($based = mysql_fetch_array($based_id)){
				eval('components_'.$component.'::deleteAttr($name,$based["id"]);');
			}
			$result = sys::sql("DELETE FROM `prefix_ClassSections` WHERE `id` = '$id' LIMIT 1;",0);
		} else {
			$result = sys::sql("DELETE FROM `prefix_ClassSections` WHERE `id` = '$id' LIMIT 1;",0);
		}

		modules_baseclass_admin::getChildAttr($parent_id,'admin');

	}
	// Функция удаления дочернего атрибута
	//==================================================================================================




	//==================================================================================================
	// Функция удаления дочернего атрибута
	function renameChildAttr($newTitle,$id){

		$parent_id = mysql_result(sys::sql("SELECT `parent_id` FROM `prefix_ClassSections` WHERE `id`='$id';",0),0);

		$sql = sys::sql("UPDATE
							`prefix_ClassSections`
						SET
							`title` = '$newTitle'
						WHERE
							`id` = '$id'
		;",0);

		modules_baseclass_admin::getChildAttr($parent_id,'admin');

	}
	// Функция удаления дочернего атрибута
	//==================================================================================================




	//==================================================================================================
	// Функция удаления класса
	function deleteClass($id,$author = ''){

		// Выбираем элементы структуры основанные на данном базовом классе 
		$sql = sys::sql("SELECT
							`id`
						FROM
							`prefix_Sections`
						WHERE
							`base_class` = '$id'
		;",0);

		// Удаляем все элементы структуры основанные на данном базовом классе
		while ($row = mysql_fetch_array($sql)){
			modules_structure_admin::deleteElement($row['id']);
		}

		// Узнаем имя удаляемого класса
		$sql = sys::sql("SELECT
							`name`
						FROM
							`prefix_ClassSections`
						WHERE
							`id` = '$id'
		;",0);

		$name = mysql_result($sql,0);

		// Удаляем все дочерние элементы Базового класса
		$sql = sys::sql("DELETE
						FROM
							`prefix_ClassSections`
						WHERE
							`name` = '$name'
		;",0);

		// Удаляем базовый класс
		$sql = sys::sql("DELETE
						FROM
							`prefix_ClassSections`
						WHERE
							`id` = '$id'
						LIMIT 1
		;",0);

		modules_baseclass_admin::buildTree($author);

	}
	// Функция удаления класса
	//==================================================================================================




	/**************************************************************************************************/
	/*							Функции редактирования настроек компонентов							  */
	/**************************************************************************************************/


	//==================================================================================================
	// Вызов диалога настроек компонента
	function editComponentSettings($id){

		$component = mysql_result(
			sys::sql("SELECT
						`value`
					FROM
						`prefix_ClassSections`
					WHERE
						`id` = '$id'
			;",0)
		,0);

	    eval('components_'.$component.'::editSettings($id);');

	}
	// Вызов диалога настроек компонента
	//==================================================================================================


	//==================================================================================================
	// Вызов сохранения настроек компонента
	function saveComponentSettings($component,$id,$POST){

		eval('components_'.$component.'::saveSettings($id,$POST);');

	}
	// Вызов сохранения настроек компонента
	//==================================================================================================
  

	//==================================================================================================
	// Вызов создания настроек компонента
	function createComponentSettings($id,$POST){

		$component = mysql_result(
			sys::sql("SELECT
						`value`
					FROM
						`prefix_ClassSections`
					WHERE
						`id` = '$id'
			;",0)
		,0);

		eval('components_'.$component.'::createSettings($id,$POST);');

	}
	// Вызов создания настроек компонента
	//==================================================================================================
  

	//==================================================================================================
	// Вызов удаления настройки компонента
	function delComponentSettings($component,$id){

		eval('components_'.$component.'::delSettings($id);');

	}
	// Вызов удаления настройки компонента
	//==================================================================================================


	/**************************************************************************************************/
	/*							Функции редактирования настроек компонентов							  */
	/**************************************************************************************************/




	//==================================================================================================
	// Функция создания таблиц
	function createTable(){

		$sql=sys::sql("CREATE TABLE IF NOT EXISTS
						`prefix_ClassSections` (
							`id` int(11) NOT NULL auto_increment,
							`parent_id` int(11) NOT NULL,
							`name` varchar(255) NOT NULL,
							`title` varchar(255) NOT NULL,
							`type` varchar(255) NOT NULL,
							`value` varchar(255) NOT NULL,
							PRIMARY KEY  (`id`)
						)
						ENGINE=MyISAM
						CHARACTER SET utf8 COLLATE utf8_general_ci
						COMMENT='Таблица Базовых Типов'
						AUTO_INCREMENT=1
		;",0);

		$sql = sys::sql("SELECT
							`name`
						FROM
							`prefix_ClassSections`
						WHERE
							`id`='1'
		;",0);

		if (mysql_num_rows($sql)==0){    
			$sql=sys::sql("INSERT INTO
								`prefix_ClassSections`
							VALUES(
								'1',
								'0',
								'root',
								'Корень',
								'type',
								''
							)
			;",0);
		}

	}
	// Функция создания таблиц
	//==================================================================================================
}
// Конец класса
?>