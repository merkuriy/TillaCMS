<?
/* 
*	Модуль Структура (SYS)
*/


class modules_structure_sys {
	//========================================
	// Функция добавления комментария
	function add($POST){
		$FILES=$_FILES;
		if (isset($_SESSION['user_login'])){
			$brother=sys::sql("SELECT `id` FROM `prefix_Sections` WHERE `parent_id`='".$POST['parent']."'",0);
			if (!isset($POST['name'])){
				$name=(mysql_num_rows($brother)+1);
			}else {
				$name=$POST['name'];
			}
			if (!isset($POST['title'])){
				$title=(mysql_num_rows($brother)+1);
			}else {
				$title=$POST['title'];
			}
			$CREAT['parent_name']=$POST['parent_name'];
			$CREAT['name']=$name;
			$CREAT['title']=$title;
			$CREAT['attr']='';
			$CREAT['name']=$name;
			$CREAT['parent']=$POST['parent'];
			$id=modules_structure_admin::addElementSCR($CREAT,'','client');
			$POST['id']=$id;
			$POST['name'] = $CREAT['name'];
			$POST['title'] = $CREAT['title'];
			$POST['userID']=mysql_result(sys::sql("SELECT `id` FROM `prefix_Sections` WHERE `name`='".$_SESSION['user_login']."'",0),0);
			if (!isset($POST['Date']) or $POST['Date']=='0000-00-00 00:00:00'){
				$POST['Date']=date("Y-m-d").' '.date("H:i:s");
			}
			modules_structure_admin::editElementSCR($POST,$FILES,'client');
		}else{
			echo 'Только авторизованные пользователи могут производить данные действия!';
		}
	}
	
	
	//=================================
	// Функция редактирования элементов
	function edit($POST){
		$FILES=$_FILES;
		if (isset($_SESSION['user_login'])){
			$base_class= mysql_result(sys::sql("SELECT `id` FROM `prefix_ClassSections` WHERE `name`='".$POST['parent_name']."' AND type='type'",0),0);
			$id=mysql_result(sys::sql("SELECT `id` FROM `prefix_Sections` WHERE `name`='".$POST['name']."' AND `base_class`='$base_class'",0),0);
			$POST['id']=$id;
			$POST['userID']=mysql_result(sys::sql("SELECT `id` FROM `prefix_Sections` WHERE `name`='".$_SESSION['user_login']."'",0),0);
			if (!isset($POST['Date']) or $POST['Date']=='0000-00-00 00:00:00'){
				$POST['Date']=date("Y-m-d").' '.date("H:i:s");
			}
			modules_structure_admin::editElementSCR($POST,$FILES,'client');
		}else{
			echo 'Только авторизованные пользователи могут производить данные действия!';
		}
	}
	
	//=================================
	// Функция удаления элементов
	function delete($POST){
		if (isset($_SESSION['user_login']) and modules_users_sys::getPolicy('delKomment')){
			modules_structure_admin::deleteElement($POST['id'],'client');
		}else{
			echo 'Только авторизованные пользователи могут производить данные действия!';
		}
	}
	
	//===================================
	// Функция пакетного получения данных
	function get($request){
		// Формируем массивы
		$searchSect	= array();
		$searchComp	= array();
		$params		= array();
		$components	= array();
		$needDataFromComponent = false;
		$sqlSelect	= 'SELECT sections.id';
		$sqlFrom	= ' FROM prefix_Sections as sections';
		$sqlWhere	= ' WHERE ';
		
		global $system;
		// Обходим входной массив и разбиваем искомые данные и ключи поиска
		foreach($request as $key=>$value) {
			// Проверяем принадлежность ключа к таблице Sections
			if ($key=='id' or $key=='name' or $key=='title' or $key=='parent_id' or $key=='base_class' or $key == 'pos'){
				$key = 'sections.'.$key;
				if ($value == 'null' or $value == '') {
					$searchSect[$key] = '';
				}else{
					$params[$key] = $value;
				}
			}else{
				$needDataFromComponent = true;
				if ($value=='null' or $value==''){
					$searchComp[$key] = '';
				}else{
					$params[$key] = $value;
				}
			}
		}

		// Если необходимы данные из компонентов и не указан базовый класс
		if ($needDataFromComponent and !isset($params['sections.base_class'])){
			// Выводим ошибку
			return "Для выполнения операции, требуется указать базовый класс!";
		}else{
			// Поиск толко по Структуре
			// Формируем секцию Select
			foreach($searchSect as $key=>$value){
				$sqlSelect .= ', '.$key;
			}
			// Формируем секцию Where
			foreach($params as $key=>$value){
				if ($sqlWhere==' WHERE '){
					$append = '';
				}else{
					$append = ' AND ';
				}
				if (is_array($value)){
					$sqlWhere .= $append.'(';
					$count = count($value);
					$i = 0;
					foreach($value as $key2=>$value2){
						$sqlWhere .= ' '.$key.' = "'.$value2.'"';
						$i++;
						if ($i<$count){
							$sqlWhere .= ' OR ';
						}
					}
					$sqlWhere .= ')';
				}else{
					$sqlWhere .= $append.$key.' = "'.$value.'"';
				}
			}

			if (isset($searchSect['sections.pos'])) {
				$sqlAfterWhere = " ORDER BY sections.pos";
			}
			$query = $sqlSelect.$sqlFrom.$sqlWhere.$sqlAfterWhere;

			// Получаем искомые данные из структуры
			$tempSections = sys::sql($query,1);

			// Запускаем поиск по компонентам
			if ($needDataFromComponent){
				// Выбираем базовый класс
				$sql = sys::sql("SELECT name,value FROM prefix_ClassSections WHERE `parent_id`='".$request['base_class']."' AND `type`='attr'",1);
				// Формируем массив атрибутов и компонентов БК
				foreach($sql as $key=>$value){
					$components[$value['name']] = $value['value'];
				}
				// Выбираем необходимые компоненты
				$i = 0;
				foreach($tempSections as $val){
					foreach($searchComp as $key=>$value){
						$tempName = explode('.',$key);
						$system['section'][$tempSections[$i]['id']]['base_class'] = $request['base_class'];
						eval('$tempValue = components_'.$components[$tempName[0]].'::view("'.$tempName[0].'",'.$tempSections[$i]['id'].',"'.$tempName[1].'");');
						$tempSections[$i][$key] = $tempValue;
					}
					$i++;
				}

			}
			return($tempSections);
			
		}
	}
	
	
	/*
	 * Функция сохранения(добавления) разделов
	 */
	function set (&$request = false) {
		// Проверяем, что передали массив
		if (is_array($request)) {
			
			// Проверяем тип запроса: множественное или единичное сохранение
			if (is_array( current($request) )) {
				// Множественное сохранение
				$result = array();
				
				foreach($request as $key => $val){
					
					if (isset($_FILES[$key]))
						$files = &$_FILES[$key];
					else
						$files = false;
					
					$result[$key] = modules_structure_sys::set_one($val, $files);
				}
				
				return $result;
			}else{
				// Единичное сохранение
				return modules_structure_sys::set_one($request, $_FILES);
			}
		}else{
			// Ошибка - ошидается, что $request это массив
			return false;
		}
	}
	
	
	function set_one($request,$files=''){
		// Проверяем тип запроса: обновление или добавление
		if (isset($request['id'])){
			// Сохранение

			// Проверяем существование элемента
			$data = sys::sql("SELECT name,title FROM prefix_Sections WHERE id='".$request['id']."'",1);
			if (is_array($data[0])){

				// Проверяем наличие Названия
				if (!isset($request['name'])){
					$request['name'] = $data[0]['name'];
				}

				// Проверяем наличие Title
				if (!isset($request['title'])){
					$request['title'] = $data[0]['title'];
				}

				modules_structure_admin::editElementSCR($request,$files,'client');
				
				return true;

			}else{

				return false;

			}
		}else{
			// Добавление раздела
			
			// Проверям на существование указателя родительского элемента
			if (isset($request['parent_id']) AND isset($request['base_class'])){
				// Проверяем существование указанного родителя в БД
				$parentName  = sys::sql("SELECT id FROM prefix_Sections WHERE id='".$request['parent_id']."' LIMIT 1;", 1);
				// Проверяем существование указанного класса в БД
				$parentClass = sys::sql("SELECT name FROM prefix_ClassSections WHERE id='".$request['base_class']."' OR name='".$request['base_class']."' LIMIT 1;", 1);

				if (isset($parentName[0]['id']) AND isset($parentClass[0]['name'])){

					$sql = sys::sql("SELECT COUNT(`id`) pos FROM `prefix_Sections` WHERE `parent_id`='".$request['parent_id']."'",1);

					$request['parent']		= $request['parent_id'];
					$request['parent_name']	= $parentClass[0]['name'];
					if (!isset($request['name']))
						$request['name']	= $sql[0]['pos']+1;
					if (!isset($request['title']))
						$request['title']	= $sql[0]['pos']+1;
					
					$request['id']	= modules_structure_admin::addElementSCR($request, $files, 'client');
					
					modules_structure_admin::editElementSCR($request, $files, 'client');
					
					return $request['id'];
				}else{

					return false;

				}

			}else{
				return false;
			}
		}
	}
	
	/*
	 * Функция для удаления по ID или Базовому классу
	 */
	function del($request){
		// Если передан массив значений
		if (is_array($request[0])){
			// Рекурсивно вызываем все внутренние элементы массива
			foreach($request as $key=>$value){
				$result[$key] = modules_structure_sys::del($value);
			}
			return $result;

		// Если передано одно значение
		}else{
			if (isset($request['parent_id'])){
				// Удаление всех дочерних элементов по родителю

				// Выбираем все элементы для удаления
				$child = sys::sql('SELECT `id` FROM `prefix_Sections` WHERE `parent_id`='.$request['parent_id'],1);

				foreach($child as $value){
					modules_structure_admin::deleteElement($value['id'],'client');
				}

				return true;
			}
			if (isset($request['id'])){
				// Удаление элемента по ID
				modules_structure_admin::deleteElement($request['id'],'client');
				return true;
			}
		}
	}
}
?>