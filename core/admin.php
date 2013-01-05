<?php
/*
 *	класс ADMIN с набором системных методов
 */
// Подключение автозагрузчика
include_once ('__autoload_class.php');

// Начало класса 
class admin{

	//==================================================================================================
	// Функция вывода админки
	function draw($tpl, $replace=''){

		global $find,$REPL;

		$replace['link'] = admin::getModulesLink();
		$replace['userName'] = $_SESSION['user_login'];

		$REPL = $replace;

        $tpl = 'templates/' . $tpl . '.tpl';
        if (file_exists($tpl)) {
            $tpl = file_get_contents($tpl);
        } else {
            die('error:draw-tpl(' . $tpl . ')');
        }

		// Если JavaScript задан как файл
		if (isset($replace['js']) and strpos($replace['js'], '.js')>-1){
			$filename = 'templates/'.$replace['js'];
			$handle = fopen($filename, "r");
			$REPL['js'] = fread($handle, filesize($filename));
			fclose($handle);
		}

		if ($replace!=''){
			do{
				$find = false;
				$tpl = preg_replace_callback(
		        	"/\%([A-Za-z0-9-_]{2,20})\%/",
		            create_function(
			            '$matches',
			            'return admin::replace($matches);'
			        ),
		            $tpl
				);
			}while( $find );
		}

		unset($REPL);
		unset($find);
		// Возвращаем страницу
		return $tpl;
	}
	// Функция вывода админки
	//==================================================================================================




	//==================================================================================================
	// Функция замены значения
	function replace($matches){

		global $REPL,$find; 

		$find = true;
		if (isset($REPL[$matches[1]])){
			return $REPL[$matches[1]];
		}else{
//			return $matches[1];
		}
	}
	// Функция замены значения
	//==================================================================================================




	//==================================================================================================
	// Функция формирования ссылок на модули
	function getModulesLink(){
		// Открываем папку modules
		$handle=opendir('../core/modules');

		$x = 0;
		// Запускаем цикл чтения
		while ($file = readdir($handle)) {
			// Отсеиваем ярлыки на вышестоящие папки и выводим
			if ($file=='..' or $file=='.' or $file[0]=='.'){} else {
				// Отрезаем расширение
				$MName = explode('.', $file);
				// Отрезаем постфикс
				$MLink=explode('_', $MName[0]);
				// Запрашиваем название
				if (file_exists('../core/modules/'.$MLink[0].'_admin.php')){ 
                    if (class_exists('modules_'.$MLink[0].'_admin' ,true)){ 
                        eval('$moduleTitle = modules_'.$MLink[0].'_admin::TITLE;'); 
                        // Запрашиваем позицию 
                        eval('$position = modules_'.$MLink[0].'_admin::POSITION;'); 
                        if ($moduleTitle != 'Пользователи' && $moduleTitle != 'Обновление') {
	                        // Записываем в массив название 
	                        $links[$position]['title']=$moduleTitle; 
	                        // Записываем в массив ссылку 
	                        $links[$position]['link']=$MLink[0]; 
	                    }
                    } 
                    $x++;
                }
			};

		}
		// Закрываем папку
		closedir($handle);
		ksort($links);
		// Формируем строку ссылок
		foreach ($links as $key => $value){
			$id = str_replace("/", "", $links[$key]['link']);
			// $link .= '<a id = "'.$id.'" class="linkMenu" href="'.$links[$key]['link'].'">'.$links[$key]['title'].'</a> ';
			$link .= '<li><a href="'.$links[$key]['link'].'">'.$links[$key]['title'].'</a></li>';
		}

		return $link;
	}
	// Функция формирования ссылок на модули
	//==================================================================================================




	//==================================================================================================
	// Функция формирования списка компонентов
	function components(){
		$handle=opendir('../core/components');
		while ($file = readdir($handle)) {
			if ($file==".." or $file=="."){} else {
				$TName = explode(".", $file);
				$component[]=$TName[0];
			};
		}
		closedir($handle); 
		return $component;
	}
	// Функция формирования списка компонентов
	//==================================================================================================




	/**************************************************************************************************/
	/*											Установка CMS										  */
	/**************************************************************************************************/
	//==================================================================================================
	// функция создания файла конфигурации
	function createConfigSCR ($POST) {

		$content = "<?php"
		    . "\n\$CONF['db_name']   = '{$POST['db_name']}';"
            . "\n\$CONF['db_host']   = '{$POST['db_host']}';"
            . "\n\$CONF['db_login']  = '{$POST['db_login']}';"
            . "\n\$CONF['db_pass']   = '{$POST['db_password']}';"
            . "\n\$CONF['db_prefix'] = '{$POST['db_prefix']}';";

		$content = html_entity_decode($content);

	    if (!file_put_contents('../config.php', $content)) {
			die('error: config-file-write');
		}

		admin::createFolders();
	}
	// функция создания файла конфигурации
	//==================================================================================================




	//==================================================================================================
	// функция создания папок
	function createFolders() {

		if (!file_exists('../site')) {
			mkdir('../site', 0777);
		}
		if (!file_exists('../site/templates')) {
			mkdir('../site/templates', 0777);
		}
		if (!file_exists('../site/images')) {
			mkdir('../site/images', 0777);
		}
		if (!file_exists('../data')) {
			mkdir('../data', 0777);
		}
		if (!file_exists('../data/files')) {
			mkdir('../data/files', 0777);
		}
		if (!file_exists('../data/images')) {
			mkdir('../data/images', 0777);
		}
		if (!file_exists('../data/wysiwyg')) {
			mkdir('../data/wysiwyg', 0777);
		}
		admin::databaseWrite();
	}
	// функция создания папок
	//==================================================================================================




	//==================================================================================================
	// функция создания таблиц и записей
	function databaseWrite() {

		global $CONF;
		$CONF = sys::preLoad();

		// Создание таблиц для модулей
		modules_baseclass_admin::createTable();
		modules_structure_admin::createTable();

		$modules = scandir('../core/modules');
		foreach ($modules as $module) {
			if ($module[0] != '.') {
				$module = basename($module, '.php');
				if ($module != 'structure_admin' && $module != 'baseclass_admin' && substr($module, -6) == '_admin') {
                    $module = 'modules_' . $module;
                    if (method_exists($module, 'createTable')) {
                        call_user_func(array($module, 'createTable'));
                    }
				}
			}
		}

		// Создание таблиц для компонентов
		$components = scandir('../core/components');
		foreach ($components as $component) {
			if ($component[0] != '.') {
                $component = 'components_' . basename($component, '.php');
                if (method_exists($component, 'createTable')) {
                    call_user_func(array($component, 'createTable'));
                }
			}
		}

		admin::createAdmin();
	}
	// функция создания таблиц и записей
	//==================================================================================================




	//==================================================================================================
	// функция создания админа
	function createAdmin() {

		// Создаём таблицу групп
		sys::sql("
            CREATE TABLE IF NOT EXISTS `prefix_groups` (
                `name` varchar(255) NOT NULL,
                `title` varchar(255) NOT NULL,
                `description` text NOT NULL,
                `policy` text NOT NULL,
                PRIMARY KEY (`name`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8
		;",0);

		// Создаём таблицу политик
		sys::sql("
            CREATE TABLE IF NOT EXISTS `prefix_policy` (
                `name` varchar(255) NOT NULL,
                `description` text NOT NULL,
                PRIMARY KEY (`name`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8
		;",0);
		
		$sql = sys::sql("
            SELECT
                *
            FROM
                `prefix_groups`
            WHERE
                `name` = 'root'
		;",0);

		if (mysql_num_rows($sql) == 0) {
			sys::sql("
				INSERT INTO
					`prefix_groups`
					(
						`name`,
						`title`,
						`description`,
						`policy`
					)
				VALUES
					(
						'root',
						'Разработчики',
						'Группа для разработчиков системы. Все права автоматически доступны и всегда равны true',
						''
					)
			;",0);
		}

		$sql = sys::sql("
            SELECT
                `id`
            FROM
                `prefix_ClassSections`
            WHERE
                `name` = 'users' AND
                `type` = 'users'
		;",0);

		if (mysql_num_rows($sql)==0){
            sys::sql("
                INSERT INTO
                    `prefix_ClassSections`
                VALUES (
                    '',
                    '0',
                    'users',
                    'Пользователи',
                    'users',
                    ''
                )
			;",0);

            $id_root = $id = mysql_insert_id();

			sys::sql("INSERT INTO `prefix_ClassSections` VALUES ('', '$id', 'user', 'Пользователь', 'type_children', '');",0);

			sys::sql("INSERT INTO `prefix_ClassSections` VALUES ('', 0, 'user', 'Пользователь', 'type', '');",0);
			$id = mysql_insert_id();
			$SEND['baseclass'] = mysql_insert_id();

			sys::sql("INSERT INTO `prefix_ClassSections` VALUES ('', '$id', 'password', 'Пароль', 'attr', 'TVarchar');",0);
			sys::sql("INSERT INTO `prefix_ClassSections` VALUES ('', '$id', 'group', 'Группа', 'attr', 'TVarchar');",0);
			sys::sql("INSERT INTO `prefix_ClassSections` VALUES ('', '$id', 'mail', 'E-mail', 'attr', 'TVarchar');",0);
			sys::sql("INSERT INTO `prefix_ClassSections` VALUES ('', '$id', 'active', 'Активность', 'attr', 'TBoolev');",0);
			sys::sql("INSERT INTO `prefix_ClassSections` VALUES ('', '$id', 'code', 'Код', 'attr', 'TVarchar');",0);

			sys::sql("INSERT INTO `prefix_Sections` VALUES ('', '0', '0', 'users', 'Пользователи', '$id_root', '');",0);

			$SEND['parent_id'] = mysql_insert_id();
		}

		echo admin::draw('_install/step4', $SEND);
	}
	// функция создания админа
	//==================================================================================================




	//==================================================================================================
	// Продолжаем создание админа
	function createAdmin2($POST){

		$sql = sys::sql("INSERT INTO
							`prefix_Sections`
						VALUES (
							'',
							'0',
							'".$POST['parent_id']."',
							'".$POST['login']."',
							'".$POST['login']."',
							'".$POST['baseclass']."',
							''
						)
		;",0);

		$id = mysql_insert_id();

		$password = md5($POST['password']);

		$sql = sys::sql("INSERT INTO
							`prefix_TVarchar`
						VALUES (
							'',
							'password',
							'$id',
							'$password'
						)
		;",0);

		$sql = sys::sql("INSERT INTO
							`prefix_TVarchar`
						VALUES (
							'',
							'group',
							'$id',
							'root'
						)
		;",0);

		$sql = sys::sql("INSERT INTO
							`prefix_TBoolev`
						VALUES (
							'',
							'active',
							'$id',
							'true'
						)
		;",0);

		echo admin::draw('_install/step5');

	}
	// Продолжаем создание админа
	//==================================================================================================




	//==================================================================================================
	// Ввод ключа для CMS
	function enterKey($POST){

		sys::sql("
            CREATE TABLE IF NOT EXISTS
            `prefix_key` (
                `key` text NOT NULL
            )
            ENGINE=MyISAM
            CHARACTER SET utf8 COLLATE utf8_general_ci
		;",0);
		
		$sql = sys::sql("INSERT INTO
							`prefix_key`
						VALUES (
							'".$POST['key']."'
						)
		;",0);

		echo 'Установка завершена!';

	}
	// Ввод ключа для CMS
	//==================================================================================================

	/**************************************************************************************************/
	/*											Установка CMS										  */
	/**************************************************************************************************/




	//==================================================================================================
	//Функция проверки ключа
	function keyValid() {

		$sql = sys::sql('
            SELECT
                `key`
            FROM
                `prefix_key`
            LIMIT 1
		;',0);

		$original = mysql_result($sql,0);

		$to = $_SERVER['HTTP_HOST'];
		$to = str_replace('www.', '', $to);

		for ($x = 0; $x < strlen($to); $x++) {
			$znak[$x] =(int) (((ord($to[$x]) + 341)*17)/7);
			$out = $out + $znak[$x];
		}

		$out = md5($out);	

		if ($out == $original) {
			return true;
		} else {
			return true;
		}
	}
	//Функция проверки ключа
	//==================================================================================================

}
// Конец класса





// Обозначаем кодировку страницы как UTF-8
header('Content-type: text/html; charset="utf-8"',true);
// Запуск сессии
session_start();
// Загрузка CONFIG.php
$CONF = sys::preLoad();

if (empty($CONF) or (isset($_POST['parametr']) && isset($_POST['act']) && $_POST['act'] == 'install')) {

	if ($_POST['parametr'] == 'step1') {
		echo 1;
        admin::createConfigSCR($_POST);
	}elseif ($_POST['parametr'] == 'step4') {
		admin::createAdmin2($_POST);
	}elseif ($_POST['parametr'] == 'step5') {
		admin::enterKey($_POST);
	}else{
		echo admin::draw('_install/step1');
	}
} else {

	if (admin::keyValid()){
		if (modules_users_sys::getPolicy('admin_access')){
/********************************************/
/* Отображение данных о версии CMS			*/
$_SESSION['cur_branch'] = 'branch1';
$_SESSION['cur_version'] = '1.096 beta';
$_SESSION['cur_number'] = 1096;
/* Отображение данных о версии CMS			*/
/********************************************/
			// Если указано имя модуля
			if (isset($_GET['action'])){
				// Вызываем этот модуль
				eval('modules_'.$_GET['module'].'_admin::onLoad($_GET,$_POST,$_FILES);');
		  }else{
				// Иначе выводим страницу приветствия
				modules_structure_panel::draw_template('index');
			}
		}else{
			// Вызов диалога авторизации
			modules_structure_panel::draw_template('auth');
		}
	} else {
		echo 'Введен неверный ключ!';
	}

}