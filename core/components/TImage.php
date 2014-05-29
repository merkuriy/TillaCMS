<?php

/*
 *	Компонент TImages (изображение)
 *	===============================
 *	Комопнент хранения изображений (позволяет загружать их на сервер)
 */
class components_TImage {
	
	//=====================================
	//Функция вывода на редактирование
	function edit($name,$parentId,$title){
		
		$fileExists = false;
		
		
		$data_child_element=sys::sql("SELECT `id` FROM `prefix_TImage` WHERE `name`='$name' AND `parent_id`='$parentId';",0);
		
		$id = mysql_result($data_child_element,0);
		
		if (mysql_num_rows($data_child_element)>0) {
			
			$parentClass=mysql_result(sys::sql("SELECT `base_class` FROM `prefix_Sections` WHERE `id`='$parentId';",0),0);
			$parent=mysql_result(sys::sql("SELECT `id` FROM `prefix_ClassSections` WHERE `parent_id`='$parentClass' AND `value`='TImage' AND `name`='$name';",0),0);
			$rule=sys::sql("SELECT `id`,`parent_id`,`width`,`height`,`logo`,`psevdo`,`cropw`,`croph`,`resize`,`path` FROM `prefix_ImageSettings` WHERE `parent_id`='$parent';",0);
			$x=0;     
			while($row = mysql_fetch_array($rule)){
				$SEND['path'] = $id.$row['psevdo'];
				
				if (file_exists("../data/images/".$SEND['path'].".jpg")){
					if ($x==0){
						$fileExists = true;
						$x=1;
					}
				}
			}
		}
		
		$SEND['title'] = $title;
		$SEND['name'] = $name;
		
		if( $fileExists )
			return admin::draw('TImage/editDialog',$SEND);
			
		return admin::draw('TImage/editDialogEmpty',$SEND);
		
	}

	function yiq($r,$g,$b) { return (($r*0.299)+($g*0.587)+($b*0.114)); } 
	
	
	// =====================================
	// Функция сохранения данных
	function save($POST, $FILES, $name='', $param=''){
		global $system;
		
		if ($POST['data']=='#delete') {
			components_TImage::deleteAttr($POST['dataName'], $POST['parent_id']);
			
			echo 'Изображение удалёно.<br/>';
			return false;
		}
		
		$POST['parentId'] = $POST['parent_id'];
		components_TImage::createStr($POST);
			
		$id = mysql_result(
			sys::sql("SELECT `id` FROM `prefix_TImage` WHERE `name`='".$POST['dataName']."' AND `parent_id`='".$POST['parent_id']."';", 0), 0
		);
		
		if (empty($POST['attrBCid'])){
			// ищем ID атрибута в базовом классе
			$POST['attrBCid'] = mysql_result(sys::sql("
				SELECT
					c.`id`
				FROM
					`prefix_Sections` s,
					`prefix_ClassSections` c
				WHERE
					s.`id`='".$POST['parent_id']."' AND
					c.`parent_id` = s.`base_class` AND
					c.`value`='TImage' AND
					c.`name`='".$POST['dataName']."'
				LIMIT 1
				;", 0), 0
			);
		}
		
		if (empty($system['imageSettings'][$attrBCid])) {
			$system['imageSettings'][$attrBCid] = sys::sql('
				SELECT
					`id`, `parent_id`, `width`, `height`, `logo`, `logowidth`, `logoheight`, `psevdo`, `cropw`, `croph`, `resize`, `path`
				FROM `prefix_ImageSettings`
				WHERE `parent_id`="'.$POST['attrBCid'].'"
				;', 1
			);
		}
		
		foreach( $system['imageSettings'][$attrBCid] as $row ){
			
			if (strpos($row['psevdo'],'_bw')>-1){
				$bw = true;
				//echo 'BlackAndWhite';
			}else{
				$bw = false;
				//echo 'Colour!';
			}
			
			//сохранение изображения
			if (isset($FILES[$name])) {
				$file		= $FILES[$name]["tmp_name"];
				$file_type	= $FILES[$name]["type"];
				$error_flag	= $FILES[$name]["error"];
				
				// Если ошибок не было
				if($error_flag == 0){
					$type = explode('/',$file_type);
					if ($type[0]=="image") {
						// Узнаем тип файла
						$size=GetImageSize($file);
						$width=$size[0];
						$height=$size[1];
						if ($row['width']==0){$row['width']=$width;}
						if ($row['height']==0){$row['height']=$height;}
						// Узнаем размеры
						eval('$img_in=ImageCreateFrom'.$type[1].'($file);');
						
						// Если требуется создание черно-белого изображения
						if ($bw){
							$img_src = $img_in;
							//echo $width;
							//echo $height;
							$img_in = ImageCreate($width,$height);
							for ($c = 0; $c < 256; $c++) {
								ImageColorAllocate($img_in, $c,$c,$c);
							}
							imagecopy($img_in, $img_src, 0, 0, 0, 0, $width,$height);
						}
						
						$img_out = ImageCreateTrueColor($row['width'],$row['height']);
						$bgc = imagecolorallocate($img_out, 255, 255, 255);
						if ($row['resize']==0){
							// уменьшение без маштабирования
							imagecopy($img_out, $img_in, 0, 0, 0, 0, $row['width'],$row['height']);
						}
						if ($row['resize']==2){
							if ($row['width']>imagesx($img_in)){$row['width']=imagesx($img_in);};
							if ($row['height']>imagesy($img_in)){$row['height']=imagesy($img_in);};
							// уменьшение с маштабированием
							$wm=$width/$row['width'];
							$hm=$height/$row['height'];
							if ($wm>$hm) {
								$hm=$wm;
							}
							$ah=(int) $height/$hm;
							$aw=(int) $width/$hm;
							if ($row['cropw']!='' and $aw>$row['width']){
								$start=$aw-$row['width'];
								$x1=-(int) ($start/100)*$row['cropw'];
							} else {
								$x1=0;
							}
							if ($row['croph']!='' and $ah>$row['height']){
								$start=$ah-$row['height'];
								$y1=-(int) ($start/100)*$row['croph'];
							} else {
								$y1=0;
							}
							if ($aw<$row['width']) {
								$x1=(int) ($row['width']-$aw)/2;
							}
							if ($ah<$row['height']) { 
								$y1=(int)($row['height']-$ah)/2;
							}
							$img_out = ImageCreateTrueColor($aw,$ah);
                            if ($type[1]=='png'){
                                imagealphablending($img_out, false);
                                imagesavealpha($img_out,true);
                                $transparent = imagecolorallocatealpha($img_out, 255, 255, 255, 127);
                                imagefilledrectangle($img_out, 0, 0, (int) $width/$hm,(int) $height/$hm, $transparent);
                            }else{
                                $bgc = imagecolorallocate($img_out, 255, 255, 255);
                            }

							ImageCopyResampled($img_out,$img_in,0,0,0,0,(int) $width/$hm,(int) $height/$hm,$width,$height);
						}
						if ($row['resize']==1){
							// уменьшение с маштабированием
							$wm=$width/$row['width'];
							$hm=$height/$row['height'];
							if ($wm<$hm) {
								$hm=$wm;
							} 
							$ah=(int) $height/$hm;
							$aw=(int) $width/$hm;
							if ($row['cropw']!='' and $aw>$row['width']){
								$start=$aw-$row['width'];
								$x1=-(int)(($start/100)*$row['cropw']);
							} else {
								$x1=0;
							}
							if ($row['croph']!='' and $ah>$row['height']){
								$start=$ah-$row['height'];
								$y1=-(int)(($start/100)*$row['croph']);
							} else {
								$y1=0;
							}
							if ($aw<$row['width']) {
								$x1=(int)(($row['width']-$aw)/2);
							}
							if ($ah<$row['height']) { 
								$y1=(int)(($row['height']-$ah)/2);
							}

                            if ($type[1]=='png'){
                                imagealphablending($img_out, false);
                                imagesavealpha($img_out,true);
                                $transparent = imagecolorallocatealpha($img_out, 255, 255, 255, 127);
                                imagefilledrectangle($img_out, 0, 0, (int) $width/$hm,(int) $height/$hm, $transparent);
                            }else{
                                $bgc = imagecolorallocate($img_out, 255, 255, 255);
                            }

							ImageCopyResampled($img_out,$img_in,$x1,$y1,0,0,(int) $width/$hm,(int) $height/$hm,$width,$height);
						}
            			// Наложение логотипа
            			if ($row['logo']!=''){
              				$logo  = ImageCreateFromPng('../data/images/'.$row['logo'].'.png'); // логотип
              				if (!$logo) {
                				echo 'Логотип не загружен!';
              				}
              				$logoSize=GetImageSize('../data/images/'.$row['logo'].'.png');
              				$logoWidth=$logoSize[0];
              				$logoHeight=$logoSize[1];
              				if ($row['width']>((int) $width/$hm)){
              					$width = (int) $width/$hm;
							}else{
								$width = $row['width'];
							}
              				if ($row['height']>((int) $height/$hm)){
              					$height = (int) $height/$hm;
							}else{
								$height = $row['height'];
							}

              				if ($row['logowidth']=='left'){$logox=0;}
              				if ($row['logowidth']=='center'){$logox=(int) (($width-$logoWidth)/2);}
              				if ($row['logowidth']=='right'){$logox=$width-$logoWidth;}
              				if ($row['logoheight']=='top'){$logoy=0;}
              				if ($row['logoheight']=='middle'){$logoy=(int) (($height-$logoHeight)/2);}
              				if ($row['logoheight']=='bottom'){$logoy=$height-$logoHeight;}
              				$img=$img_out;
              				imagecopy($img_out, $img, 0, 0, 0, 0, $row['width'], $row['height']);
              				imagecopy($img_out, $logo, $logox, $logoy, 0, 0, $logoWidth, $logoHeight);
            			}
            			// Сохранение на сервер
            			if ($type[1]=="png"){
                            imagepng($img_out,"../data/images/".$id.$row['psevdo'].".png");
            			}else{
                            imagejpeg($img_out,"../data/images/".$id.$row['psevdo'].".jpg",100);
            			}
            			imagedestroy($img_out);
            			imagedestroy($img_in);
          			} else {
            			echo 'Недопустимый тип файла!'; 
          			}
        		}
      		}
    	}
    	if ($param=='client'){return;} else {}	
	}


	//=====================================
	//Функция создания записи
	function createStr($POST){
		
		$result = sys::sql("SELECT `id` FROM `prefix_TImage` WHERE `name`='".$POST['dataName']."' AND `parent_id`='".$POST['parentId']."'",0);
		
		if (mysql_num_rows($result) == 0) {
			$result = sys::sql("INSERT INTO `prefix_TImage` ( `id` , `name` , `parent_id` ) VALUES ('', '".$POST['dataName']."', '".$POST['parentId']."');",0);
		}
	}


	//=====================================
	//Функция удаления записи
	function deleteAttr($name,$id){
		$data_child_element=sys::sql("SELECT `id` FROM `prefix_TImage` WHERE `parent_id`='$id';",0);
		while($child = mysql_fetch_array($data_child_element)){
			$parentClass=mysql_result(sys::sql("SELECT `base_class` FROM `prefix_Sections` WHERE `id`='$id';",0),0);
			$parent=mysql_result(sys::sql("SELECT `id` FROM `prefix_ClassSections` WHERE `parent_id`='$parentClass' AND `value`='TImage'",0),0);
			$rule=sys::sql("SELECT `id`,`parent_id`,`width`,`height`,`logo`,`psevdo`,`cropw`,`croph`,`resize`,`path` FROM `prefix_ImageSettings` WHERE `parent_id`='$parent';",0);
			while($row = mysql_fetch_array($rule)){
				if (file_exists("../data/images/".$child['id'].$row['psevdo'].".jpg")){
					unlink("../data/images/".$child['id'].$row['psevdo'].".jpg");
				}
			}
		}
		$result = sys::sql("DELETE FROM `prefix_TImage` WHERE `parent_id` = '$id' AND `name` = '$name' LIMIT 1;",0);
	}


	/*
	 * Вывод данных
	 */
	function view ($name, $parentId, $param='') {
		
		global $system;
		
		//components_TImage::createTable();
		list($param, $dop) = explode('-', $param);
		
		//Если раздела и базового класса нет в кеше получаем их
		if (empty($system['section'][$parentId])){
			modules_structure_view::newSection();
		}
		
		//Если настроек атрибута базового класса нет в кеше получаем их из базы
		if (empty(
				$system['classSection'][
					$system['section'][$parentId]['base_class']
				]['settings.'.$name]
		)) {
			$settings = sys::sql("
				SELECT
					settings.`psevdo` psevdo,
					settings.`path` path
				FROM
					`prefix_ClassSections` baseClass,
					`prefix_ImageSettings` settings
				WHERE
					baseClass.`parent_id` = ".$system['section'][$parentId]['base_class']." AND
					baseClass.`name` = '$name' AND
					baseClass.`id` = settings.`parent_id` 
			;", 1);
			
			foreach ($settings as $val) {
				$system['classSection'][
					$system['section'][$parentId]['base_class']
				]['settings.'.$name][
					$val['psevdo']
				]['path'] = $val['path'];
			}
		}
		
		
		//если псевдо параметр имени не задан, получаем первый в списке псевдо имён
		if ($param == '') {
			reset($system['classSection'][ $system['section'][$parentId]['base_class'] ]['settings.'.$name]);
			$param = key($system['classSection'][ $system['section'][$parentId]['base_class'] ]['settings.'.$name]);
		}
		
		//Получаем id изображения
        $fileId = sys::sql("
            SELECT `id`
            FROM `prefix_TImage`
            WHERE `name`='$name' AND `parent_id`='$parentId'
            LIMIT 1
        ;");
        if ($fileId !== false && mysql_num_rows($fileId) > 0) {
            $fileId = mysql_result($fileId, 0);
            $file = '../data/images/'.$fileId.$param.'.jpg';
            if (!file_exists($file)) {
                $file = '../data/images/' . $fileId . $param . '.png';
                if (!file_exists($file)) {
                    $file = false;
                }
            }
        } else {
            $file = false;
        }

		if ($file === false) {
            //Получаем путь к изображению заменителю пустышки
            $placeholder = $system['classSection'][ $system['section'][$parentId]['base_class'] ]['settings.'.$name][$param]['path'];
            if ($placeholder == '') {
                return '';
            } else {
                $placeholderScheme = substr($placeholder, 0, 5);
                if ($placeholderScheme == 'data:' or $placeholderScheme == 'http:' or
                    $placeholder[0] == '/' or substr($placeholder, 0, 8) == 'https://'
                ) {
                    return $placeholder;
                }
                return '/data/images/' . $placeholder;
            }
		}
		
		switch ($dop) {
			case 'filesize':
				return round(filesize($file) / 1048576, 2);
				
			case 'width':
				$type = substr($file, -3);
				
				switch ($type) {
					case 'jpg':
						return imagesx( ImageCreateFromJpeg($file));
					case 'gif':
						return imagesx( ImageCreateFromGif($file));
					case 'png':
						return imagesx( ImageCreateFromPNG($file));
				}
				
				return false;
				
			case 'height':
				$type = substr($file, -3);
				
				switch ( $type ){
					case 'jpg':
						return imagesy( ImageCreateFromJpeg($file));
					case 'gif':
						return imagesy( ImageCreateFromGif($file));
					case 'png':
						return imagesy( ImageCreateFromPNG($file));
				}
				
				return false;
		}
		
		return substr($file, 2);
	}


	//=====================================
	//Функция вывода настроек на редактирование
	function editSettings($id){

		$sql = sys::sql("SELECT
							`id`,
							`parent_id`,
							`width`,
							`height`,
							`logo`,
							`logowidth`,
							`logoheight`,
							`psevdo`,
							`cropw`,
							`croph`,
							`resize`,
							`path`
						FROM
							`prefix_ImageSettings`
						WHERE
							`parent_id`='$id'
		;",1);		

		foreach ($sql as $key => $value) {
			$SEND['rule'][] = $value;
		}

		// while($row = mysql_fetch_array($sql)){
		// 	$SEND['rule'][] = $row;
		// }

		$SEND['parent'] = mysql_result(
			sys::sql("SELECT
						`parent_id`
					FROM
						`prefix_ClassSections`
					WHERE
						`id` = '$id'
			;",0)
		,0);

		$SEND['id'] = $id;
		$SEND['js'] = 'TImage/editRuleDialog.js';

		// echo admin::draw('TImage/editRuleDialog',$SEND);
		return $SEND;
	}


	//=====================================
	//Функция сохранения настроек
	function saveSettings($id,$POST){
		$result = sys::sql("
			UPDATE `prefix_ImageSettings` SET
				`width` = '".$POST['width']."',
				`height` = '".$POST['height']."',
				`logo` = '".$POST['logo']."',
				`logowidth` = '".$POST['logow']."',
				`logoheight` = '".$POST['logoh']."',
				`psevdo` = '".$POST['psevdo']."',
				`cropw` = '".$POST['cropw']."',
				`croph` = '".$POST['croph']."',
				`resize` = '".$POST['resize']."',
				`path` = '".$POST['path']."'
				WHERE `id` ='$id' LIMIT 1 ;",0);
	}
  
  
	//=====================================
	//Функция создания настройки
	function createSettings($id,$POST){
		$result = sys::sql("
			INSERT INTO `prefix_ImageSettings` (`id`,`parent_id`,`width`,`height`,`logo`,`logowidth`,`logoheight`,`psevdo`,`cropw`,`croph`,`resize`,`path`)
			VALUES (
				'',
				'$id',
				'".$POST['width']."',
				'".$POST['height']."',
				'".$POST['logo']."',
				'".$POST['logow']."',
				'".$POST['logoh']."',
				'".$POST['psevdo']."',
				'".$POST['cropw']."',
				'".$POST['croph']."',
				'".$POST['resize']."',
				'".$POST['path']."'
			);",0);
	}
  

	//=====================================
	//Функция удаления настройки
	function delSettings($id){
		$result = sys::sql("DELETE FROM `prefix_ImageSettings` WHERE `id` = '$id' LIMIT 1;",0);
	}
	
	
	//=====================================
	//Функция Проверки условий
	function condition($name,$parentId,$cond){
		return false;
	}
	
}



// Инициализация компонента
// TODO: это лучше делать только на стороне админки, или как то подругому ограничить частоту вызова

if (!file_exists('../data/images')) {
	mkdir("../data/images", 0777);
};

$query=sys::sql("
	CREATE TABLE IF NOT EXISTS `prefix_TImage` (
		`id` int(11) NOT NULL auto_increment,
		`name` varchar(255) NOT NULL,
		`parent_id` int(11) NOT NULL,
		PRIMARY KEY  (`id`)
	) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8 COLLATE utf8_general_ci AUTO_INCREMENT=1
;",0);

$query=sys::sql("	
	CREATE TABLE IF NOT EXISTS `prefix_ImageSettings` (
		`id` INT NOT NULL AUTO_INCREMENT,
		`parent_id` INT( 11 ) NOT NULL,
		`width` INT( 5 ) NOT NULL,
		`height` INT( 5 ) NOT NULL,
		`logo` VARCHAR( 255 ) NOT NULL,
		`logowidth` VARCHAR( 255 ) NOT NULL,
		`logoheight` VARCHAR( 255 ) NOT NULL,
		`psevdo` VARCHAR( 255 ) NOT NULL,
		`cropw` VARCHAR( 10 ) NOT NULL,
		`croph` VARCHAR( 10 ) NOT NULL,
		`resize` INT( 1 ) NOT NULL,
		`path` VARCHAR( 255 ) NOT NULL DEFAULT  '',
		PRIMARY KEY ( `id` )
	) CHARACTER SET utf8 COLLATE utf8_general_ci
;",0);