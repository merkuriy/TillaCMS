<?php

/*
 *	класс SYS с набором системных методов
 */
class sys {
	
	/*
	 * Упрощенный запрос к БД
	 */
	function sql ($sql, $output_type) {

		global $CONF;
		
		$CONF['colsql']++;

		if (empty($CONF["sqllink"])) {
		    $CONF["sqllink"] = mysql_select_db($CONF["db_name"],
                mysql_connect($CONF["db_host"], $CONF["db_login"], $CONF["db_pass"])
            );
		    
		    mysql_query("SET NAMES utf8") or die(mysql_error());
			//mysql_query("SET CHARACTER SET utf8") or die(mysql_error());
		}
		
		$result = mysql_query( ' '.preg_replace('/prefix_/', $CONF["db_prefix"], $sql) )
			or die("Invalid query: " . mysql_error());
		
			
		if($output_type==''){
			return $result;
		}else{
			$output = array();
			while( $row = mysql_fetch_array( $result, MYSQL_ASSOC ) ){
		     	$output[] = $row;
			}
			
			return $output;
		}

	}

	//==========================================================================
	// Функция предварительной загрузки
	function preLoad(){
		if (file_exists('../config.php')){
			include ('../config.php');
		}
		return $CONF;
	}

	function getFields($table){
		global $CONF;

		if(empty($CONF["sqllink"])){
			$CONF["sqllink"] = mysql_select_db($CONF["db_name"],mysql_connect($CONF["db_host"],$CONF["db_login"], $CONF["db_pass"]));
		    
			mysql_query("SET NAMES utf8") or die(mysql_error());
			mysql_query("SET CHARACTER SET utf8") or die(mysql_error());
		}
		$table=str_replace('prefix_', $CONF["db_prefix"], $table);
		$fields = mysql_list_fields($CONF["db_name"], $table);
		return $fields;
	}
  
	//========================================
	// Функция вывода баннера
	function banner($category,$param=''){
		
		global $system;
		
		echo $category;
		
		unset($active_ID);
		
		$category_id=mysql_result(sys::sql("SELECT `id` FROM `prefix_Sections` WHERE `name`='$category'",0),0);
		$banners=sys::sql("SELECT `id` FROM `prefix_Sections` WHERE `parent_id`='$category_id'",0);
		
		while($ban = mysql_fetch_array($banners)){
			$active_banners=sys::sql("SELECT `id` FROM `prefix_TBoolev` WHERE `name`='Active' AND `parent_id`='".$ban['id']."' AND `data`='true'",0);
			if (mysql_num_rows($active_banners)>0){
				$active_ID[]=$ban['id'];
			}
		}
		
		if (count($active_ID)==0){return '';};
		if (count($active_ID)==count($system['banner'][$category_id])){
			return '';
		} else {
			do{
				$x=rand(0, count($active_ID)-1);
			}while(sys::getBannerId($active_ID[$x],$category_id));

			$system['banner'][$category_id][]=$active_ID[$x];
			$countView=mysql_result(sys::sql("SELECT `data` FROM `prefix_TInteger` WHERE `name`='Viewed' AND `parent_id`='".$active_ID[$x]."';",0),0);
			$totalView=mysql_result(sys::sql("SELECT `data` FROM `prefix_TInteger` WHERE `name`='Pokaz' AND `parent_id`='".$active_ID[$x]."';",0),0);
			$publicDays=mysql_result(sys::sql("SELECT `data` FROM `prefix_TInteger` WHERE `name`='countDays' AND `parent_id`='".$active_ID[$x]."';",0),0);
			if ($publicDays>0){
				$publicDate=mysql_result(sys::sql("SELECT DATE_FORMAT(DATE_ADD(`data`,INTERVAL $publicDays DAY),'%Y-%m-%d') FROM `prefix_TDate` WHERE `name`='Date' AND `parent_id`='".$active_ID[$x]."';",0),0);
			}
			if ($countView>=$totalView){
				$result=sys::sql("UPDATE `prefix_TBoolev` SET `data`='false' WHERE `name`='Active' AND `parent_id`='".$active_ID[$x]."';",0);
			}
			$curDate=date("Y-m-d");
			if (($publicDate<$curDate) and ($publicDays>0)){
				$result=sys::sql("UPDATE `prefix_TBoolev` SET `data`='false' WHERE `name`='Active' AND `parent_id`='".$active_ID[$x]."';",0);
			}
			$countView+=1;
			$result=sys::sql("UPDATE `prefix_TInteger` SET `data`='$countView' WHERE `name`='Viewed' AND `parent_id`='".$active_ID[$x]."';",0);
			$link=mysql_result(sys::sql("SELECT `data` FROM `prefix_TText` WHERE `name`='Link' AND `parent_id`='".$active_ID[$x]."'",0),0);
			$img=mysql_result(sys::sql("SELECT `data` FROM `prefix_TFiles` WHERE `name`='Image' AND `parent_id`='".$active_ID[$x]."'",0),0);
			$width=mysql_result(sys::sql("SELECT `data` FROM `prefix_TInteger` WHERE `name`='width' AND `parent_id`='".$category_id."'",0),0);
			$height=mysql_result(sys::sql("SELECT `data` FROM `prefix_TInteger` WHERE `name`='height' AND `parent_id`='".$category_id."'",0),0);
			
			$target=mysql_result(sys::sql("SELECT `data` FROM `prefix_TSelect` WHERE `name`='target' AND `parent_id`='".$active_ID[$x]."'",0),0);
			
			$img=str_replace("..", "", $img);
			$exeimg = substr($img,strlen($img)-3,strlen($img));
			if( $exeimg=='swf' ){
				
				if( $link=='' ){
					$link = '';
				}else{
					$link = '<a style="position:absolute;display:block;z-index:999999;width:'.$width.'px;height:'.$height.'px;text-decoration:none;overflow:hidden;font-size:9999px;line-height:9999px;" href="'.$link.'" target="'.$target.'">&nbsp; &nbsp</a>';
				}
				
				return
					$link.
					'<div id="banner_zzz"></div>'.
					'<script src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js" type="text/javascript"></script>'.
					'<script type="text/javascript">'.
					'swfobject.embedSWF("'.$img.'", "banner_zzz", "'.$width.'", "'.$height.'", "9.0.0", false, false, { wmode:"transparent", allowscriptaccess:"sameDomain" } );'.
					'</script>';
				
				
			}else{
				
				if ($link==''){
					$link1='<span '.$param.'>';
					$link2='</span>';
				}else{
					$link1='<a href="'.$link.'" target="'.$target.'" '.$param.'>';
					$link2='</a>';
				}
				
				return $link1.'<img width="'.$width.'" height="'.$height.'" src="'.$img.'" />'.$link2;
				
			}
		}
	}
  
	function getBannerId($id,$category){
		global $system;
		if (!isset($system['banner'][$category])){
			return false;
		}else{
			foreach($system['banner'][$category] as $value){
				if ($id==$value){
					return true;
				};
			}
			return false;
		}
	}

	function logEntry($event,$id='',$title=''){
		$filename = '../core/errors/log.txt';
		$somecontent = date("Y-m-d")." ".date("H:i:s").";".$_SESSION['user_login'].";".$event.";".$id.";".$title."\n";
		if (is_writable($filename)) {
		    if (!$handle = fopen($filename, 'a')) {exit;}
		    if (fwrite($handle, $somecontent) === FALSE) {exit;}
		    fclose($handle);
		}
	}
	
}