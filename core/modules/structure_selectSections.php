<?php

/* 
 * Модуль структуры
 */
class modules_structure_selectSections {
	
	/*
	 *  Случайным образом выводит, определённое количество разделов,
	 *  удовлетворяющие условию
	 *  
	 *	$tpl_name	- имя шаблона
	 *	$condition	- условие, для выборки
	 *	$countSections - максимальное количество разделов
	 */
	function random( $tpl_name='' , $condition='', $countSections='' ){
		
		global $system;
		
		//обработка условий
		if($condition==''){
			$query_condition='';
		}else{
			$query_condition = 'WHERE ';
			
			$arr_conditions = explode(';', $condition);
			foreach( $arr_conditions as $key => $value ){
				if($key>0) $query_condition .= ' or ';
				$arr2 = explode('=', $value);
				$query_condition .= 'section.`'.$arr2[0].'`="'.$arr2[1].'"';
			}
		}
		
		//обработка лимита
		if( $countSections>0 ){
			$limit='LIMIT '.$countSections;
		}else{
			$limit='';
		}
		
		
		$result = sys::sql('
			SELECT section.`id` id
			FROM `prefix_Sections` section
			'.$query_condition.'
			ORDER BY RAND()
			'.$limit.'
			;
		',1);
		
		
		
		$sections='';
		
		foreach( $result as $value ){
			$sections .= modules_structure_tpl::tpl('section', $tpl_name,$value['id']);
		}
		
		return modules_structure_tpl::parseTamplate('select',$tpl_name,'',
			array(
				'sections' => $sections
			)
		);
		
	}
	
	
	/*
	 *  Выводит коментарии к разделу начиная с последних
	 *  
	 *	$tpl_name	- имя шаблона
	 *	$partID		- id раздела
	 *	$countSections - максимальное количество выводимых коментариев
	 */	 
	function komment_view( $tpl_name='' , $partID='', $countSections='' ){
		
		//обработка лимита
		if( $countSections>0 ){
			$limit='LIMIT '.$countSections;
		}else{
			$limit='';
		}
		
		if( $partID=='' ){
			$partID = view::attr('id');
		}
		
		$result = sys::sql('
			SELECT section.`id` id
			FROM
				`prefix_Sections` section,
				`prefix_TInteger` tinteger
			WHERE section.`base_class`=110 and
				section.`id` = tinteger.`parent_id` and
				tinteger.`name` = "partID" and
				tinteger.`data`="'.$partID.'"
			ORDER BY id DESC
			'.$limit.'
			;
		',1);
		
		$sections='';
		
		if( isset($result[0]['id']) ){
			foreach( $result as $value ){
				$sections .= modules_structure_tpl::tpl('section', $tpl_name, $value['id']);
			}
		}else{
			$sections = '<div class="block_komment_03">Комментарии отсутствуют</div>';
		}
		
		return $sections;
		
	}
	
}
?>