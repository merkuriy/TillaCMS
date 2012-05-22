<?php

/* 
 * Модуль пользователи (VIEW)
 */
class modules_users_view {
	//========================================
	// Функция аторизации
	function auth($tpl_name=''){
		if (isset($_SESSION['user_login'])){
			$active='active';
			$minimacros['userName']=$_SESSION['user_login'];
			if (modules_users_sys::getPolicy('admin')){
				$minimacros2['URL']='/panel';
				$minimacros['adminPage']=modules_structure_tpl::parseTamplate('user_adminBTN',$tpl_name,'',$minimacros2);
			}else{
				$minimacros2['URL']='/hidden/user_edit';
				$minimacros['adminPage']=modules_structure_tpl::parseTamplate('user_userBTN',$tpl_name,'',$minimacros2);
			}
		} else {
			$active='';
			$minimacros='';
		}
		return modules_structure_tpl::parseTamplate('user_auth',$tpl_name,$active,$minimacros);
	}
  
	function view_user($tpl_type='', $tpl_name='', $id='',$param=''){
		if (isset($_SESSION['user_login'])){
			return view::tpl($tpl_type, $tpl_name, $id,$param);
		}
	}

	function view_no_user($tpl_type='', $tpl_name='', $id='',$param=''){
		if (!isset($_SESSION['user_login'])){
			return view::tpl($tpl_type, $tpl_name, $id,$param);
		}
	}
  
	function view_user_policy($tpl_type='', $tpl_name='', $id='',$param='',$policy=''){
		if (modules_users_sys::getPolicy($policy)){
			return view::tpl($tpl_type, $tpl_name, $id,$param);
		}
	}
	
	function view_user_no_policy($tpl_type='', $tpl_name='', $id='',$param='',$policy=''){
		if( isset($_SESSION['user_login']) and !modules_users_sys::getPolicy($policy) ){
			return view::tpl($tpl_type, $tpl_name, $id,$param);
		}
	}
  
	function getSession(){
		return session_id();
	}

	function getUserID(){
		return $_SESSION['user_ID'];
	}
	
	function getUserData($parametr){

		if ($parametr == 'icq' or $parametr == 'mail'){
			$sql = sys::sql("SELECT
								`data`
							FROM
								`prefix_TMnemonik`
							WHERE
								`parent_id` = '".$_SESSION['user_ID']."' AND
								`name` = '$parametr'
			;",0);
		} else {
			$sql = sys::sql("SELECT
								`data`
							FROM
								`prefix_TVarchar`
							WHERE
								`parent_id` = '".$_SESSION['user_ID']."' AND
								`name` = '$parametr'
			;",0);
		}

		if (mysql_num_rows($sql)>0){
			$out = mysql_result($sql,0);
		}

		return $out; 

	}
}
?>