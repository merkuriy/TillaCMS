<?php

/*
 *	класс Update с набором системных методов
 *  служит для обновления версии CMS
 */
class modules_update_admin{
	const TITLE = 'Обновление';
	const POSITION = '10';
	const UPD_URL = 'http://tilla.googlecode.com/svn/updates/';

	//==================================================================================================
	// Функция определения адресата запроса
	function onLoad($GET,$POST,$FILES){
		if (!isset($GET['action'])){modules_update_admin::show();}
		if ($GET['action']=='updateCMS'){modules_update_admin::update();}
	}
	// Функция определения адресата запроса
	//==================================================================================================




	//==================================================================================================
	// Функция вывода данных об обновлении
	function show(){

		$SEND['path'] = 'Обновление';
		$SEND['currentVersion'] = modules_update_admin::getCurrentVersion();
		$SEND['accessibleVersion'] = modules_update_admin::getAccessibleVersion();
		$SEND['update_btn'] = modules_update_admin::updateBTN();
		$SEND['content'] = admin::draw('update/page',$SEND);
		$SEND['title'] = 'Обновление';
		$SEND['js'] = 'update/js.js';
		echo admin::draw('page_index',$SEND);

	}
	// Функция вывода данных об обновлении
	//==================================================================================================


	//==================================================================================================
	// Функция определения текущей версии
	function getCurrentVersion(){
		if (isset($_SESSION['cur_branch'])){
			$version = $_SESSION['cur_version'];
		}else{
			$version = '0';
			$_SESSION['cur_branch'] = 'branch0';
			$_SESSION['cur_version'] = '0';
			$_SESSION['cur_number'] = 0;
		}
		return $version;
	}
	// Функция определения текущей версии
	//==================================================================================================
	

	//==================================================================================================
	// Функция определения доступной версии
	function getAccessibleVersion(){
		copy(modules_update_admin::UPD_URL.'lastStableVersion.ini','../version.ini');
		$ini_array = parse_ini_file("../version.ini",true);

		$_SESSION['upd_version'] = $ini_array[$_SESSION['cur_branch']]['accessibleVersionTitle'];
		$_SESSION['upd_number'] = $ini_array[$_SESSION['cur_branch']]['accessibleVersionNumber'];
		$_SESSION['upd_file'] = $ini_array[$_SESSION['cur_branch']]['updatefile'];
		
		return $_SESSION['upd_version'];
	}
	// Функция определения доступной версии
	//==================================================================================================


	//==================================================================================================
	// Функция отображения кнопки обновления
	function updateBTN(){
		if ($_SESSION['upd_number']>$_SESSION['cur_number']){
			$out = '<button id="updateBTN">Обновить</button>';
		}else{
			$out = 'Обновление для данной версии CMS недоступно!';
		}
		return $out;
	}
	// Функция отображения кнопки обновления
	//==================================================================================================
	
	//==================================================================================================
	// Функция обновления системы
	function update(){
		require_once('../core/includes/pclzip.lib.php');
		if(copy(modules_update_admin::UPD_URL.$_SESSION['upd_file'],'../newVersion.zip')){
			$zip = new PclZip('../newVersion.zip');
			if ($zip->extract(PCLZIP_OPT_PATH, "../") == 0) {
				die('Ошибка: Не удалось распокавать файл. Отчет: '.$zip->errorInfo(true));
			}else{
				unlink('../newVersion.zip');
				unlink('../version.ini');
				echo 'Обновление успешно завершено!';
			}
		}else{
			echo 'Не удалось скачать обновление!';
		}
	}
	// Функция обновления системы
	//==================================================================================================

}
// Конец класса
?>