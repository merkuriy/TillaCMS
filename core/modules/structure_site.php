<?php

class modules_structure_site {

	/*
	 *
	 */
    function metaTitle () {

        if (isset(view::$data['page.metaTitle'])) {
            $title = view::$data['page.metaTitle'];
        } elseif (!$title = view::attr('metaTitle')) {
            $title = view::attr('title') . ' — ' . modules_settings_sys::get('site.title');
        }
        return '<title>' . $title . '</title>';
    }

    function metaKeywords () {
        if (!$keywords = view::attr('metaKeywords')) {
            if (!$keywords = modules_settings_sys::get('site.keywords')) return;
        }
        return '<meta name="keywords" content="' . $keywords . '">';
    }

    function metaDescription () {
        if (!$description = view::attr('metaDescription')) {
            if (!$description = modules_settings_sys::get('site.description')) return;
        }
        return '<meta name="description" content="' . $description . '">';
    }

    function metaAll () {
        return modules_structure_site::metaTitle()
            .  modules_structure_site::metaKeywords()
            .  modules_structure_site::metaDescription();
    }


    /*
	 *    Вывод копирайт строки сайта с учетом текущего языка
	 */
    function copyright ($preTitle = '', $postTitle = '') {

        global $CONF;

        $creatYear = modules_settings_sys::get('site.startYear');
        $curYear = date('Y');

        $result = '© ';

        if ($curYear == $creatYear) {
            $result .= $creatYear;
        } else {
            $result .= $creatYear.' — '.$curYear;
        }

        if (empty($CONF['language']) || (
                $CONF['defaultLanguage'] != $CONF['language']
                && !$title = modules_settings_sys::get('site.title.' . $CONF['language']))
        ) {
            $title = modules_settings_sys::get('site.title');
        }

        return $result . ' ' . $preTitle . $title . $postTitle;
    }


	/*
	 * Функция для проверки условия в шаблонах
	 */
	function condition ($variable, $condition, $true='', $false='') {
		if ($variable == $condition) {
			return $true;
		} else {
			return $false;
		}
	}


	/*
	 * Функция для оптравки почтового сообщения
	 */
	function sendMail($addressTo,$message,$header){
		$__smtp = array(
		    "host"      => modules_settings_sys::get('smtpServer'), //smtp сервер
		    "debug"     => 0,                   //отображение информации дебаггера (0 - нет вообще)
		    "auth"      => true,                 //сервер требует авторизации
		    "port"      => modules_settings_sys::get('smtpPort'),                    //порт (по-умолчанию - 25)
		    "username"  => modules_settings_sys::get('smtpUsername'),//имя пользователя на сервере
		    "password"  => modules_settings_sys::get('smtpPassword'),//пароль
		    "addreply"  => "your@email.com",//ваш е-mail
		    "replyto"   => "your@email.com"      //e-mail ответа
		);
		$send['mailBody'] = $message;
		
		$mailAltBody = 
			str_replace('<b>', "",
			str_replace('</b>', "",
			str_replace('<big><b>', "===========================\n\t",
			str_replace('</b></big>', "\n===========================",
			str_replace('<br>', "\n",
				$send['mailBody']
			) ) ) ) 
		)."\n\n\n".'!!!ВНИМАНИЕ: ваш почтовый клиент некоректно отображает письмо в HTML виде.'."\n";
		
		
		
		
		require_once("../core/includes/PHPMailer/class.phpmailer.php");
	
		$mail = new PHPMailer(true);

		$send['mailLogin'] = 'info';
		$send['mailName'] = modules_settings_sys::get('mailFrom');
		 
		$mail->IsSMTP();

		try {
			$mail->Host       = $__smtp['host'];
			$mail->SMTPDebug  = $__smtp['debug'];
			$mail->SMTPAuth   = $__smtp['auth'];
			$mail->Port       = $__smtp['port'];
			$mail->Username   = $__smtp['username'];
			$mail->Password   = $__smtp['password'];
			$mail->CharSet    = 'utf-8';
			$mail->Subject	  = $header;
			$mail->Subject    = htmlspecialchars($subject);
			$mail->AltBody	  = &$mailAltBody;
			$mail->SetLanguage ('ru');
			$mail->AddAddress( $addressTo, $send['mailName']);
			$mail->SetFrom( $mail->Username, modules_settings_sys::get('mailFrom'));
			$mail->MsgHTML($send['mailBody']);
			$mail->Send();
			return true;
		} catch (phpmailerException $e) {
		  	echo $e->errorMessage();
		} catch (Exception $e) {
		  	echo $e->getMessage();
		}
	}

    function sendComment($POST) {
			unset($data['id']);
	    $POST['base_class'] = 'comment';
	    $POST['title']      = $POST['author'].' '.$POST['url'];
	    $POST['parent_id']  = 42;
	    $POST['page_id']    = $POST['page'];
			modules_structure_sys::set($POST);
		
			echo 'ok';
    }
}