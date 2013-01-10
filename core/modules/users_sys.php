<?php

/*
 * Модуль пользователи (SYS)
 */
class modules_users_sys {


    /*
     * Функция аторизации
     */
    function auth($POST,$out = ''){

        // Узнаём идентификатор пользователя
        $idSQL = sys::sql(
            "
				SELECT
					s.`id`,
					vGroup.`data` as `group`,
					p.`policy` as `policy`
				FROM
					`prefix_Sections` as s,
					`prefix_ClassSections` as c,
					`prefix_TVarchar` as vPass,
					`prefix_TVarchar` as vGroup,
					`prefix_TBoolev` as bActive,
					`prefix_groups` as p
				WHERE
					s.`name`='{$POST['user_login']}' AND
					vPass.`data`='".md5($_POST['user_password'])."' AND
					s.`base_class`= c.id AND
					c.`name` = 'user' AND
					c.`type` = 'type' AND
					vPass.`name` = 'password' AND
					vPass.`parent_id` = s.id AND
					vGroup.`name` = 'group' AND
					vGroup.`parent_id` = s.id AND
					bActive.`name` = 'active' AND
					bActive.`parent_id` = s.id AND
					bActive.`data` = 'true' AND
					p.`name` = vGroup.`data`;
			",
            0);

        if (mysql_num_rows($idSQL)>0){
            $usersData=mysql_fetch_assoc($idSQL);

            $_SESSION['user_ID']=$usersData['id'];
            $_SESSION['user_login']=$_POST['user_login'];
            $_SESSION['user_group']=$usersData['group'];

            // Загрузка политик доступа
            $usersData['policy'] = str_replace('::',',',$usersData['policy']);
            $usersData['policy'] = str_replace(':','',$usersData['policy']);
            $policy = explode(',',$usersData['policy']);
            foreach($policy as $val){
                $_SESSION['user_policy'][$val] = true;
            }
            if ($_SESSION['user_group'] == 'root') {
                header('location: /panel/structure');
            } elseif ($out==''){
                if (isset($_SERVER[HTTP_REFERER])){
                    header('location: '.$_SERVER[HTTP_REFERER]);
                } else {
                    modules_Structure_admin::onLoad($_GET,$_POST,$_FILES);
                }
            }else{
                header('location: '.$out);
            }
        }
    }

  
  	//========================================
	// Функция аторизации
	function authtenticate($POST){
		// Узнаём идентификатор пользователя
		$idSQL = sys::sql(
			"
				SELECT
					s.`id`,
					vPass.`data` as `password`,
					vGroup.`data` as `group`,
					bActive.`data` as `active`,
					vFIO.`data` as `fio`,
					vPhone.`data` as `phone`,
					vOrgName.`data` as `orgName`,
					vOrgInn.`data` as `orgInn`,
					vOrgAddress.`data` as `orgAddress`,
					vOrgPhone.`data` as `orgPhone`,
					vOrgOgrn.`data` as `orgOgrn`,
					vOrgRsch.`data` as `orgRsch`,
					vOrgBank.`data` as `orgBank`,
					vOrgKsch.`data` as `orgKsch`,
					vOrgBik.`data` as `orgBik`,
					p.`policy` as `policy`
				FROM
					`prefix_Sections` as s,
					`prefix_ClassSections` as c,
					`prefix_TVarchar` as vPass,
					`prefix_TVarchar` as vGroup,
					`prefix_TBoolev` as bActive,
					`prefix_groups` as p,
					`prefix_TVarchar` as vFIO,
					`prefix_TVarchar` as vPhone,
					`prefix_TVarchar` as vOrgName,
					`prefix_TVarchar` as vOrgInn,
					`prefix_TVarchar` as vOrgAddress,
					`prefix_TVarchar` as vOrgPhone,
					`prefix_TVarchar` as vOrgOgrn,
					`prefix_TVarchar` as vOrgRsch,
					`prefix_TVarchar` as vOrgBank,
					`prefix_TVarchar` as vOrgKsch,
					`prefix_TVarchar` as vOrgBik
				WHERE
					s.`name`='".$POST['user_login']."' AND
					s.`base_class`= c.id AND
					c.`name` = 'user' AND
					c.`type` = 'type' AND
					vPass.`name` = 'password' AND
					vPass.`parent_id` = s.id AND
					vGroup.`name` = 'group' AND
					vGroup.`parent_id` = s.id AND
					bActive.`name` = 'active' AND
					bActive.`parent_id` = s.id AND

					vFIO.`name` = 'fio' AND
					vFIO.`parent_id` = s.id AND
					
					vPhone.`name` = 'phone' AND
					vPhone.`parent_id` = s.id AND
					
					vOrgName.`name` = 'orgName' AND
					vOrgName.`parent_id` = s.id AND
					
					vOrgInn.`name` = 'inn' AND
					vOrgInn.`parent_id` = s.id AND
					
					vOrgAddress.`name` = 'adress' AND
					vOrgAddress.`parent_id` = s.id AND

					vOrgPhone.`name` = 'orgPhone' AND
					vOrgPhone.`parent_id` = s.id AND

					vOrgOgrn.`name` = 'ogrn' AND
					vOrgOgrn.`parent_id` = s.id AND

					vOrgRsch.`name` = 'rsch' AND
					vOrgRsch.`parent_id` = s.id AND

					vOrgBank.`name` = 'bank' AND
					vOrgBank.`parent_id` = s.id AND

					vOrgKsch.`name` = 'ksch' AND
					vOrgKsch.`parent_id` = s.id AND

					vOrgBik.`name` = 'bik' AND
					vOrgBik.`parent_id` = s.id AND

					p.`name` = vGroup.`data`;
			",
		0);
		if (mysql_num_rows($idSQL)>0){
			$usersData=mysql_fetch_assoc($idSQL);
			if ($usersData['password']==md5($_POST['user_password']) and $usersData['active']=='true'){
				$_SESSION['user_ID']=$usersData['id'];
				$_SESSION['user_login']=$_POST['user_login'];
				$_SESSION['user_group']=$usersData['group'];
				$_SESSION['user_FIO']=$usersData['fio'];
				$_SESSION['user_phone']=$usersData['phone'];
				$_SESSION['user_orgName']=$usersData['orgName'];
				$_SESSION['user_orgInn']=$usersData['orgInn'];
				$_SESSION['user_orgAddress']=$usersData['orgAddress'];
				$_SESSION['user_orgPhone']=$usersData['orgPhone'];
				$_SESSION['user_orgOgrn']=$usersData['orgOgrn'];
				$_SESSION['user_orgRsch']=$usersData['orgRsch'];
				$_SESSION['user_orgBank']=$usersData['orgBank'];
				$_SESSION['user_orgBik']=$usersData['orgBik'];
				$_SESSION['user_orgKsch']=$usersData['orgKsch'];
				
				// Загрузка политик доступа
				$usersData['policy'] = str_replace('::',',',$usersData['policy']);
				$usersData['policy'] = str_replace(':','',$usersData['policy']);
				$policy = explode(',',$usersData['policy']);
				foreach($policy as $key => $val){
					$_SESSION['user_policy'][$val] = true;
				}
				echo 'ok';
			}else{
				if ($usersData['active']=='false'){
					echo 'notActive';
				}else{
					echo 'badPassword';
				}
			}
		}else{
			echo 'badPassword';
		}
	}
  
	//========================================
	// Функция деавторизации
	function deauth(){
		session_destroy();
		unset($_SESSION);
		header('location:'.$_SERVER[HTTP_REFERER]);
	}

	/*
	 * Функция регистрации
	 */
	function registration($data='',$sendPassword=false){
		if ($data == ''){
			$data = $_POST;
		}

		if ($sendPassword){
			$append = 'Ваш пароль: '.$data['password'];
		}

		$data['name'] = $data['mail'];
		$data['title'] = $data['mail'];
		$data['parent'] = 2;
		$data['type'] = 4;
		$data['active'] = 'false';
		$data['code'] = rand(100000,999999);
		$data['group'] = 'users';
		$data['password'] = md5($data['password']);

		// Проверяем на существование пользователя с таким-же E-mail
		$sql = sys::sql("
			SELECT
				id
			FROM
				`prefix_Sections`
			WHERE
				name = '".$data['mail']."' AND
				parent_id = 2
		",0);
		
		// Если существует
		if (mysql_num_rows($sql)>0){
			return "double";
		// Если не существует
		}else{
			// Вносим элемент в БД (таблица _Sections)
			$sql = sys::sql("
				INSERT INTO
					`prefix_Sections`
				VALUES (
					'',
					'',
					'".$data['parent']."',
					'".$data['name']."',
					'".$data['title']."',
					'".$data['type']."',
					''
				);
			",0);
	
			// Узнаем ID созданного элемента
			$id=mysql_insert_id();
	
			// Определяем дочерние элементы 
			$sql = sys::sql("
				SELECT
					`id`,
					`name`,
					`value`
				FROM
					`prefix_ClassSections`
				WHERE
					`parent_id`='".$data['type']."' AND
					`type`='attr';
			",0);
	
			while($row = mysql_fetch_array($sql)){
				$temp['parent_id']	= $id;
				$temp['dataName']	= $row['name'];
				$temp['data']		= $data[$row['name']];
				eval('components_'.$row["value"].'::save($temp,"");');
			}

			$sendMail = modules_structure_site::sendMail(
				$data['mail'],
				'Внимание, данное сообщение отправленно для подтверждения регистрации на сайте garazh.ru.<br /> Если вы ничего об этом не знаете, просто удалите это письмо!<br /> Для завершения регистрации на сайте garazh.ru Вам необходимо пройти по ссылке <a href="http://work3.q-format.ru/api.post/users.activate?id='.$id.'&code='.$data['code'].'">http://work3.q-format.ru/api.post/users.activate?id='.$id.'&code='.$data['code'].'</a><br />'.$append,
				'Регистрация'
			);
			
			if ($sendMail){
				return 'ok';
			}else{
				return 'sendError';
			}
		}
		return 'error';		
	}

	/*
	 * Функция сохранения данных пользователя 
	 */
	function saveUserData($data){
		if ($data['password']==''){
			unset($data['password']);
		}else{
			$data['password'] = md5($data['password']);
		}
		
		$data['type'] = 4;

		// Проверка текущей группы
		$data['group'] = 'users';

		$available = array(	'password'=>0,
							'fio'=>0,
							'phone'=>0,
							'orgName'=>0,
							'inn'=>0,
							'adress'=>0,
							'group'=>0,
							'orgPhone'=>0,
							'ogrn'=>0,
							'rsch'=>0,
							'bank'=>0,
							'ksch'=>0,
							'bik'=>0);
		$temp	= sys::sql("SELECT id FROM `prefix_Sections` WHERE name='".$data['mail']."'",1);
		$id		= $temp[0]['id'];

		// Определяем дочерние элементы 
		$sql = sys::sql("
			SELECT
				`id`,
				`name`,
				`value`
			FROM
				`prefix_ClassSections`
			WHERE
				`parent_id`='".$data['type']."' AND
				`type`='attr';
		",0);

		while($row = mysql_fetch_array($sql)){
			if (isset($data[$row['name']]) AND isset($available[$row['name']])){
				$temp['parent_id']	= $id;
				$temp['dataName']	= $row['name'];
				$temp['data']		= $data[$row['name']];
				
				eval('components_'.$row["value"].'::save($temp,"");');
			}
		}
/*
 * 					orgPhone	:$('#regOrgPhone').val(),
					inn			:$('#regInn').val(),
					ogrn		:$('#regOgrn').val(),
					rsch		:$('#regRsch').val(),
					bank		:$('#regBank').val(),
					ksch		:$('#regKsch').val(),
					bik			:$('#regBik').val()*/

		$_SESSION['user_FIO']		=$data['fio'];
		$_SESSION['user_phone']		=$data['phone'];
		$_SESSION['user_orgName']	=$data['orgName'];
		$_SESSION['user_orgInn']	=$data['inn'];
		$_SESSION['user_orgAddress']=$data['adress'];
		$_SESSION['user_group']		=$data['group'];
		$_SESSION['user_orgPhone']	=$data['orgPhone'];
		$_SESSION['user_orgOgrn']	=$data['ogrn'];
		$_SESSION['user_orgRsch']	=$data['rsch'];
		$_SESSION['user_orgBank']	=$data['bank'];
		$_SESSION['user_orgKsch']	=$data['ksch'];
		$_SESSION['user_orgBik']	=$data['bik'];
		

		echo 'ok';
	}


	 /*
	 * Функция активации
	 */
	function activate($data){
		$id		= $data['id'];
		$code	= $data['code'];
		// Вытаскиваем код активации из БД для сравнения с запрошенным
		$sql = sys::sql("
			SELECT
				data
			FROM
				`prefix_TVarchar`
			WHERE
				parent_id = '".$id."' AND
				name = 'code'
		",0);
		// Если пользователь существует
		if (mysql_num_rows($sql)>0){
			// Проверяем соответствие кода активации
			$data = mysql_fetch_array($sql);
			if ($data['data']==$code){
				// если всё нормально - активируем учётку
				$sql = sys::sql("
					UPDATE
						`prefix_TBoolev`
					SET
						`data` =  'true'
					WHERE
						`parent_id` ='".$id."' AND
						`name` = 'active'
					LIMIT 1;
				",0);
				$sql = sys::sql("
					UPDATE
						`prefix_TVarchar`
					SET
						`data` =  ''
					WHERE
						`parent_id` ='".$id."' AND
						`name` = 'code'
					LIMIT 1;
				",0);
				header('location: ../other/registrationOK');
			}else{
				// Иначе сыпем ошибки!
				header('location: ../other/registrationError');
			}
		}else{
			header('location: ../other/registrationError');
		}
	}

	/*
	 * Функция отправки сообщения для восстановления пароля
	 */
	function resetSend($data){
		if (isset($data['user_login'])){

			$sql = sys::sql("
				SELECT
					id
				FROM
					`prefix_Sections`
				WHERE
					name = '".$data['user_login']."'
			",0);

			if (mysql_num_rows($sql)>0){
				$temp = mysql_fetch_array($sql);
				$id = $temp['id'];
				$code = rand(100000,999999);
				
				$sendMail = modules_structure_site::sendMail(
					$data['user_login'],
					'Внимание, данное сообщение отправленно для восстановления пароля на сайте ****.ru. Если вы ничего об этом не знаете, просто удалите это письмо!<br /> Ваш серкретный код для восстановления: '.$code,
					'Восстановление пароля'
				);
				
				if (!$sendMail){
					return 'sendError';
				}

				$sql = sys::sql("
					UPDATE
						`prefix_TVarchar`
					SET
						`data` = '".$code."'
					WHERE
						`parent_id` = '".$id."' AND
						`name` = 'code'
					LIMIT 1;
				",0);
				return 'ok';
			}else{
				return 'error';
			}
		}else{
			return 'error';
		}
	}

	/*
	 * Функция восстановления пароля
	 */
	function resetEnd($data){
		$sql = sys::sql("
			SELECT
				s.id,
				v.data as code
			FROM
				`prefix_Sections` as s,
				`prefix_TVarchar` as v
			WHERE
				s.name = '".$data['user_login']."' AND
				v.parent_id = s.id AND
				v.name = 'code'
		",0);

		if (mysql_num_rows($sql)>0){
			$temp = mysql_fetch_array($sql);
			$id = $temp['id'];
			$code = $temp['code'];

			if ($data['secretCode']==$code){
				$password = md5($data['user_password']);
				
				$sql = sys::sql("
					UPDATE
						prefix_TVarchar
					SET
						data = '".$password."'
					WHERE
						name = 'password' AND
						parent_id = '".$id."'
				",0);
				
				echo 'ok';
			}else{
				echo 'error';
			}

		}else{
			echo 'error';
		}
	}

	/*
	 * Функция получения политики
	 */
	function getPolicy($policy,$true=true,$false=false){
		if (isset($_SESSION['user_group'])){
			if ($_SESSION['user_group']=='root'){
				return $true;
			}else{
				if (isset($_SESSION['user_policy'][$policy])){
					return $true;
				}else{
					return $false;
				};
			}
		}else{
			return $false;
		};
	}
	
	/*
	 * Функция проверки принадлежности к группе
	 */
	function isGroup($group,$true=true,$false=false){
		if (isset($_SESSION['user_group'])){
			if ($_SESSION['user_group']==$group){
				return $true;
			}else{
				return $false;
			}
		}else{
			if ($group=='guest'){
				return $true;
			}else{
				return $false;
			}
		}
	}
	
	/*
	 * Функция получения группы
	 */
	function getGroup(){
		if (isset($_SESSION['user_group'])){
			return $_SESSION['user_group'];
		}else{
			return 'guest';
		}
	}

	/*
	 * Функция получения логина
	 */
	function getLogin(){
		if (isset($_SESSION['user_login'])){
			return $_SESSION['user_login'];
		}else{
			return 'guest';
		}
	}

	/*
	 * Функция получения данных пользователя
	 */
	function getData($dataName){
		if (isset($_SESSION[$dataName])){
			return $_SESSION[$dataName];
		}else{
			return '';
		}
	}

}