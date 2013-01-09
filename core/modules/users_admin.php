<?php

/*
 *	класс user с набором системных методов
 */
//!
if ( modules_users_sys::getPolicy('users_admin_access') ){

class modules_users_admin{
	const TITLE = 'Пользователи';
	const POSITION = '6';
	static public $policy = array(
		array('access','Право на доступ к модулю'),
		array('addPolicy','Право на добавление политик'),
		array('deletePolicy','Право на удаление политик'),
		array('addUser','Право на добавление пользователя'),
		array('deleteUser','Право на удаление пользователя'),
		array('editUser','Право на редактирование пользователя'),
		array('addGroup','Право на добавление группы'),
		array('editGroup','Право на редактирование политик группы'),
		array('deleteGroup','Право на удаление группы')
	);

	//==================================================================================================
	// Функция определения адресата запроса
	function onLoad($GET,$POST,$FILES){
		modules_users_admin::autoCreatePolicy();
		if (!isset($GET['action'])){modules_users_admin::show();}
		if ($GET['action']=='startPage'){modules_users_admin::startPage($GET['author']);}
		if ($GET['action']=='buildTree'){modules_users_admin::buildTree($GET['author']);}
		if ($GET['action']=='addElement'){modules_users_admin::addElement($GET['parent_id'],$GET['author']);}
		if ($GET['action']=='addElementSCR'){modules_users_admin::addElementSCR($POST,$FILES,$GET['author']);}
		if ($GET['action']=='editElement'){modules_users_admin::editElement($GET['id'],$GET['from']);}
		if ($GET['action']=='editElementSCR'){modules_users_admin::editElementSCR($POST,$FILES,$GET['author']);}
		if ($GET['action']=='deleteElement'){modules_users_admin::deleteElement($GET['id'],$GET['author']);}
		if ($GET['action']=='addPolicyAccess') {modules_users_admin::addPolicyAccess($GET['group'],$GET['policy']);}
		if ($GET['action']=='delPolicyAccess') {modules_users_admin::delPolicyAccess($GET['group'],$GET['policy']);}
		if ($GET['action']=='delGroup') {modules_users_admin::delGroup($GET['group']);}
		if ($GET['action']=='addGroup') {modules_users_admin::addGroup($POST);}
		if ($GET['action']=='delPolicy') {modules_users_admin::delPolicy($GET['policy']);}
		if ($GET['action']=='addPolicy') {modules_users_admin::addPolicy($POST);}
	}
	// Функция определения адресата запроса
	//==================================================================================================

	/**
	 * Функция автоматического создания политик из массива $policy
	 * указанного в файлах модулей.
	 */
	function autoCreatePolicy(){
		// Создаём массив политик
		$policyArray = array();
		// Загружаем политики из БД
		$policy=sys::sql("SELECT * FROM `prefix_policy`",0);
		// Формируем массив политик по принципу Название - Описание
		while($row = mysql_fetch_array($policy)){
			$policyArray[$row['name']] = $row['description'];
		};
		// Проверяем настройки политик на уровне модулей
		$handle=opendir('../core/modules');
		// Запускаем цикл чтения
		while ($file = readdir($handle)) {
			// Отсеиваем ярлыки на вышестоящие папки и выводим
			if ($file=='..' or $file=='.' or $file[0]=='.'){} else {
				// Отрезаем расширение
				$MName = explode('.', $file);
				// Запрашиваем политики
				eval('if (isset(modules_'.$MName[0].'::$policy)){$modulePolicy = modules_'.$MName[0].'::$policy;}');
				if ($modulePolicy){
					foreach($modulePolicy as $policyTPL){
						$policyName = $MName[0].'_'.$policyTPL[0];
						if (!isset($policyArray[$policyName])){
							$query=sys::sql("INSERT INTO `prefix_policy` (`name` ,`description`) VALUES ('".$policyName."',  '".$policyTPL[1]."');",0);
						}
					}
				}
				unset($modulePolicy);
			};

		}
		// Закрываем папку
		closedir($handle);
	}



	// Функция вывода настроек
	function show(){
		// Формируем дерево
		$SEND['tree'] = modules_users_admin::buildTree();
		$SEND['path'] = 'Пользователи';
		$SEND['content'] = modules_users_admin::startPage();
		// Формируем контент
		$SEND['content'] = admin::draw('users/page',$SEND);
		// Указываем файл JavaScript'а
		$SEND['js'] = 'users/js.js';
		// Указываем заголовок страницы
		$SEND['title'] = 'Ползователи';
		// Выводим админку
		echo admin::draw('page_index',$SEND);

	}

	// Функция вывода стартовой страницы
	function startPage($author = ''){
		$policy=sys::sql("SELECT * FROM `prefix_policy` ORDER BY name",0);
		$groups=sys::sql("SELECT * FROM `prefix_groups`",0);

		// Формируем шапку таблицы
		while($row = mysql_fetch_array($groups)){
			$groupsArray[$row['name']]=$row['policy'];
			$DATA['name'] = $row['name'];
			$SEND['groupNames'] .= admin::draw('users/startPage.policyTableGroupName',$DATA);
		}
		unset($DATA);
		$SEND['header'] = admin::draw('users/startPage.policyTableHeader',$SEND);

		// Формируем строки таблицы
		while($row = mysql_fetch_array($policy)){
			$DATA['policy'] = $row['name'];
			$DATA['policyDescription'] = $row['description'];
			foreach($groupsArray as $key=>$value){
				$DATA2['name'] = $key.'-'.$row['name'];
				if (strpos($value, ':'.$row['name'].':')>-1){
					$DATA2['value'] = 'checked';
				}else{
					$DATA2['value'] = '';
				}
				if ($key == 'root'){
					$DATA2['value'] = 'checked';
				}
				$DATA['groupColumns'] .= admin::draw('users/startPage.policyCheckBox',$DATA2);
			}
			$SEND['policyRows'] .= admin::draw('users/startPage.policyTableRow',$DATA);
			$DATA['groupColumns'] = '';
			unset($DATA2);
		}
		unset($DATA);

		// Формируем подвал страницы
		foreach($groupsArray as $key=>$value){
			$DATA2['name'] = 'delete-'.$key;
			$DATA['deleteBTNS'] .= admin::draw('users/startPage.groupDeleteBTN',$DATA2);
		}
		unset($DATA2);
		$SEND['footer'] .= admin::draw('users/startPage.policyTableFooter',$DATA);
		unset($DATA);
		$SEND['policy'] = admin::draw('users/startPage.policy',$SEND);
		$out = admin::draw('users/startPage',$SEND);
		
		if ($author == 'admin'){
			echo $out;
		}else{
			return $out;
		}

	}




	// Функция построения дерева элементов
	function buildTree($author=''){

		$id = mysql_result(
			sys::sql("SELECT
						sect.`id`
					FROM
						`prefix_Sections` sect,
						`prefix_ClassSections` class
					WHERE
						sect.`title` = 'Пользователи' AND
						sect.`name` = 'users' AND
						sect.`base_class` = class.`id` AND
						class.`name` = 'users' AND
						class.`type` = 'users'
			;",0)
		,0);

		// Выбираем дочерние элементы 1-го уровня
		$sql = sys::sql("SELECT `id`,`title` FROM `prefix_Sections` WHERE `parent_id`='$id'",0);

		// Указываем дочерние элементы
		$SEND['childNode'] = modules_users_admin::findChild($id);
		// Указываем ID элемента
		$SEND['id'] = $id;
		// Указываем Название элемента
		$SEND['title'] = 'Пользователи';

		if ($author=='admin'){
			echo admin::draw('users/tree',$SEND);
		}else{
			return admin::draw('users/tree',$SEND);
		}
		
	}

	

	// Функция поиска дочерних элементов
	function findChild($id='0'){

		// Выбираем дочерние элементы
		$sql = sys::sql("SELECT `id`,`title` FROM `prefix_Sections` WHERE `parent_id`='$id'",0);

		while ($res = mysql_fetch_array($sql)){
			// Проверяем наличие дочерних элементов
			$sq2 = sys::sql("SELECT `id` FROM `prefix_Sections` WHERE `parent_id`='".$res['id']."'",0);
			if (mysql_num_rows($sq2)>0){
				// Формируем дочерние элементы
				$res['child'] = modules_users_admin::findChild($res['id']);
				// Указываем шаблон
				$tpl = 'users/tree.elementHasChild';
			}else{
				// Указываем шаблон
				$tpl = 'users/tree.element';
			}
			// Выводим дочерние элементы дерева
			$out .= admin::draw($tpl,$res);
		}

		return $out;

	}

	
	
	
	// Диалог создания нового элемента
	function addElement($parent_id,$author=''){
		// Если создается корень
		if ($parent_id==0){
		
			$BT['value'] = 1;
			$BT['title'] = 'Корень'; 
			$BT['name'] = 'root';
			$SEND['option'] = admin::draw('users/select.option',$BT);
			
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
				// Формируем список допустимых базовых классов
				while ($res = mysql_fetch_array($sql)){
					$BT['value'] = $res['id'];
					$BT['title'] = $res['title']; 
					$BT['name'] = $res['name'];
					$SEND['option'] .= admin::draw('users/select.option',$BT);
				}
				
			}else{
				
				/*
				*	Если у выбранного элемента в базовом классе нет дочерних элементов
				*	выводим сообщение об ошибке				
				*/				
				
				$REP['title'] = 'Ошибка: Невозможно создать дочерний элемент';
				$REP['report'] = 'В Базовом Классе родительского элемента не указан ни один тип дочернего элемента. Перейти к редактированию базового класса.';
				echo admin::draw('users/report',$REP);
				return;
				
			}
			
		}
		// Указываем файл JavaScript
		$SEND['js'] = 'users/addDialog.js';
		// Указываем родительский ID
		$SEND['parent'] = $parent_id;

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
		// Выводим диалог
		echo admin::draw('users/addDialog',$SEND);
	}

	
	
	
	// Функция создания нового элемента
	function addElementSCR($POST,$FILES='',$author=''){

		// Внесение элемента в БД (таблица _Sections)
		$sql = sys::sql("INSERT
						INTO
							`prefix_Sections`
						VALUES (
							'',
							'',
							'".$POST['parent']."',
							'".$POST['name']."',
							'".$POST['title']."',
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
		if ($POST['parent']=='0'){
			echo $id;
		}else{
			modules_users_admin::editElement($id);
		}

	}

	
	
	
	
	// Диалог редактирования элемента
	function editElement($id,$from = ''){
		// Выборка данных о редактируемом элементе
		$sql = sys::sql("SELECT
							sect.`name`,
							sect.`title`,
							class.`title`,
							class.`id`
						FROM
							`prefix_Sections` sect,
							`prefix_ClassSections` class
						WHERE
							sect.`id` = '$id' AND
							class.`id` = sect.`base_class`
		",0);

		$page_datas = mysql_fetch_array($sql);															// Преобразуем данные из БД в массив

		// Формируем массив на вывод
		$SEND['js'] = 'users/editDialog.js';
		$SEND['name'] = $page_datas['0'];
		$SEND['title'] = $page_datas['1'];
		$SEND['base_class'] = $page_datas['2'];
		$SEND['id'] = $id;
		$SEND['from'] = $from;
		// Формируем массив на вывод

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

		echo admin::draw('users/editDialog',$SEND);
	}

	
	
	
	// Функция сохранения изменений в контенте элемента
	function editElementSCR($POST,$FILES,$author=''){
		// Если есть поле Пароль
		if(isset($POST['password'])){
			// Выбираем старое значение из БД
			$sql = sys::sql("
				SELECT
					data
				FROM
					`prefix_TVarchar`
				WHERE
					`parent_id` = '".$POST['id']."' AND
					`name` = 'password'
			",0);

			if (mysql_num_rows($sql)){
				$oldData=mysql_result($sql,0);
			};
			// Если значение изменилось, то создаем его хеш
			if ($oldData!=$POST['password']){
				$POST['password']=md5($POST['password']);
			}
		}
		modules_structure_admin::editElementSCR($POST,$FILES,$author);
	}

	
	
	
	
	// Функция удаления элемента
	function deleteElement($id,$author = ''){

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
			modules_users_admin::deleteElement($row['id']);
		}


		// Удаляем сам элемент
		$sql = sys::sql("DELETE FROM `prefix_Sections` WHERE `id` = '$id' LIMIT 1;",0);

		if ($author == 'admin'){
			echo 'Удаление успешно завершено!';
		}
	}

	// Функция добавления политики к группе
	function addPolicyAccess($group,$policy){
		if (modules_users_sys::getPolicy('users_admin_editGroup')){
			$query=sys::sql("UPDATE `prefix_groups` SET `policy` = CONCAT(policy,':".$policy.":') WHERE name='".$group."';",0);
			echo 'ok';
		}else{
			echo 'У вас нет права на редактирование групповых политик!';
		}
	}

	// Функция удаления политики из группы
	function delPolicyAccess($group,$policy){
		if (modules_users_sys::getPolicy('users_admin_editGroup')){
			if ($group!='root'){
				$query=sys::sql("UPDATE `prefix_groups` SET `policy` = REPLACE(policy,':".$policy.":','') WHERE name='".$group."';",0);
				echo 'ok';
			}else{
				echo 'ok';
			}
		}else{
			echo 'У вас нет права на редактирование групповых политик!';
		}
	}

	// Функция удаления группы
	function delGroup($group){
		if (modules_users_sys::getPolicy('users_admin_deleteGroup')){
			if ($group!='root'){
				$query=sys::sql("DELETE FROM `prefix_groups` WHERE name='".$group."' LIMIT 1;",0);
			}
			echo 'ok';
		}else{
			echo 'У вас нет права на удаление групп!';
		}
	}
	
	// Функция добавления группы
	function addGroup($POST){
		if (modules_users_sys::getPolicy('users_admin_deleteGroup')){
			$query=sys::sql("SELECT * FROM `prefix_groups` WHERE name='".$POST['groupName']."'",0);
			if (mysql_num_rows($query)==0){
				$query=sys::sql("INSERT INTO `prefix_groups` VALUES ('".$POST['groupName']."','".$POST['groupTitle']."','".$POST['groupDesc']."','');",0);
				echo 'ok';
			}else{
				echo 'Группа с таким именем уже существует!';
			}
		}else{
			echo 'У вас нет права на создание групп!';
		}
	}
	
	// Функция удаления политики
	function delPolicy($policy){
		if (modules_users_sys::getPolicy('users_admin_deletePolicy')){
			$query=sys::sql("DELETE FROM `prefix_policy` WHERE name='".$policy."' LIMIT 1;",0);
			$query=sys::sql("UPDATE `prefix_groups` SET `policy` = REPLACE(policy,':".$policy.":','');",0);
			echo 'ok';
		}else{
			echo 'У вас нет права на удаление политик!';
		}
	}
	
	// Функция добавления политики
	function addPolicy($POST){
		if (modules_users_sys::getPolicy('users_admin_addPolicy')){
			$query=sys::sql("SELECT * FROM `prefix_policy` WHERE `name`='_".$POST['policyName']."'",0);
			if (mysql_num_rows($query)==0){
				$query=sys::sql("INSERT INTO `prefix_policy` VALUES ('_".$POST['policyName']."','".$POST['policyDesc']."')",0);
				echo 'ok';
			}else{
				echo 'Политика с таким именем уже существует!';
			}
		}else{
			echo 'У вас нет права на создание политик!';
		}
	}

}
}