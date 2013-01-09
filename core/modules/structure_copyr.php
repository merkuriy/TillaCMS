<?php


/*
 *	Модуль структуры - Создание панели Copyright в футере страницы
 */
class modules_structure_copyr {
	
	/*
	 *    Вывод копирайт строки сайта с учетом текущего языка
	 */
	function copyright(){

		global $CONF;

		$creatYear = modules_settings_sys::get('copyright_creatYear');
		$curYear = date('Y');

		$result = '&copy; ';

		if( $curYear==$creatYear ){
			$result .= $creatYear;
		}else{
			$result .= $creatYear.' - '.$curYear;
		}

		if( $CONF['defaultLanguage']!=$CONF['language'] and
			$orgName=modules_settings_sys::get('copyright_orgName_'.$CONF['language'])
		){
			return $result.' '.$orgName;
		}
		return $result.' '.modules_settings_sys::get('copyright_orgName');
	}
	
	
	static $qfomat01 = array(
		'ru' => 'Разработано в',	//русский
		'ua' => 'Розроблено в',		//украинский
		'be' => 'Распрацавана ў',	//белорусский
		'bg' => 'Разработен в',		//болгарский
		'en' => 'Developed in',		//английский
		'et' => 'Developed in',		//эстонский
		'lv' => 'Developed in',		//латышский
		'fr' => 'Développé par',	//французский
		'de' => 'Developed by'		//немецкий
	);
	
	static $qfomat02 = array(
		'ru' => 'Информация о сайте',
		'ua' => 'Інформація про сайт',
		'be' => 'Інфармацыя аб сайце',
		'bg' => 'За сайта',
		'en' => 'Site information',
		'et' => 'Site information',
		'lv' => 'Par lapu',
		'fr' => 'A propos du site',
		'de' => 'Über Site'
	);
	
	static $qfomat03 = array(
		'ru' => 'веб-студии',
		'ua' => 'веб-студії',
		'be' => 'веб-студыі',
		'bg' => 'web-студио',
		'en' => 'web-studio',
		'et' => 'web-stuudio',
		'lv' => 'web-studijā',
		'fr' => 'web-studio',
		'de' => 'web-studio'
	);
	

	static $qfomat04 = array(
		'ru' => 'Веб-студия «Q-format» - разработка сайтов, фирменного стиля, логотипов',
		'ua' => 'Веб-студія «Q-format» - розробка сайтів, фірмового стилю, логотипів',
		'be' => 'Вэб-студыя «Q-format» - распрацоўка сайтаў, фірменнага стылю, лагатыпаў',
		'bg' => 'Уеб студио «Q-format» - уеб разработки, корпоративна идентичност, лого',
		'en' => 'Web-studio «Q-format» - web development, corporate identity, logos',
		'et' => 'Web-stuudio «Q-format» - Veebidisain, korporatiivne identiteet, logode',
		'lv' => 'Web-studija "Q-format» - web izstrāde, korporatīvā identitāte, logo',
		'fr' => 'Web-studio «Q-format» - le développement web, identité visuelle, logos',
		'de' => 'Web-studio «Q-format» - Web-Entwicklung, Corporate Identity, Logos'
	);
	
	function qformat(){
		
		global $CONF;

		$infoSiteURL = modules_settings_sys::get('infoSiteURL');
		if( !$infoSiteURL ) $infoSiteURL = '/';
		
		return 
			'<div id="q-format_foo1">
				<p id="q-format_foo2">
					'.modules_structure_copyr::$qfomat01[ $CONF['defaultLanguage'] ].'
					<a href="http://q-format.ru" title="'.modules_structure_copyr::$qfomat04[ $CONF['language'] ].'">
						<img src="/site/images/q-format.png" alt="'.
							modules_structure_copyr::$qfomat03[ $CONF['defaultLanguage'] ].
						' «Q-format»" /> &laquo;Q-format&raquo;
					</a>
					<br/>
					<a href="'.$infoSiteURL.'">'.modules_structure_copyr::$qfomat02[ $CONF['defaultLanguage'] ].'</a>
				</p>
			</div>';
		
	}

}