<?php

/*
 *	Модуль структуры - Навигация по частям страниц
 *	и всё что с этим связано
 */
class modules_structure_navigate {
	
	
	
	/*
	 *	Навигационная панель для архива новостей по годам
	 */	 
	function newsArchive($tpl_name='',$activeYear=false){
		
		global $system;
		
		if( $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['tplType']!='table' ){
			echo '!!! Навигацию возможно вызвать только в шаблоне типа table !!!';
			return false; /// error
		}
		
		//$system['tplLevel'][modules_structure_tpl::getTplLevelLast()]['level']['section']['id']
		
		$result = sys::sql('
			SELECT YEAR( d.`data` ) tdyear
			FROM `prefix_Sections` s, `prefix_TDate` d
			WHERE s.`id`=d.`parent_id` AND s.`base_class`=38
			GROUP BY tdyear
			ORDER BY tdyear DESC
		',1);
		
		$buttons='';
		
		/*
		if( strlen( $system['tplLevel'][modules_structure_tpl::getTplLevelLast()]['archive'] )<4 ){
			$system['tplLevel'][modules_structure_tpl::getTplLevelLast()]['archive']=$result[0]['tdyear'];
		}*/
		
		if( !$activeYear ) $activeYear = $system['urlParam']['archive'];
		
		foreach( $result as $value ){
			
			view::$data['archive'] = &$value['tdyear'];
			
			if( view::$data['archive']==$activeYear ){
				$buttons .= view::tpl('navigateButton.active', $tpl_name );
			}else{
				$buttons .= view::tpl('navigateButton', $tpl_name );
			}
			
		}
		
		
		view::$data['buttons']=&$buttons;
		
		return view::tpl('navigate', $tpl_name );
		
	}
	
	
	
	
	/*
	 *	Упрощенная навигационная панель
	 *	для перехода по частям страницы
	 */	 
	function partsLight($tpl_name=''){
		
		global $system;
		
		
		if( $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['tplType']!='table' ){
			echo '!!! Навигацию возможно вызвать только в шаблоне типа table !!!';
			return false; /// error
		}
		
		
		$part_last = ceil( 
			(	$system['tplLevel'][modules_structure_tpl::getTplLevelLast()]['countChild']-
				$system['tplLevel'][modules_structure_tpl::getTplLevelLast()]['startChild']+1
			)/	$system['tplLevel'][modules_structure_tpl::getTplLevelLast()]['limitChild']
		);
		
		
		
		
		if($part_last<=1) return false;
		
		
		$buttons='';
		
		for( $i=1; $i<=$part_last; $i++ ){
			
			view::$data['part']=&$i;
			if( view::$data['part']==$system['tplLevel'][modules_structure_tpl::getTplLevelLast()]['part'] ){
				$buttons .= view::tpl('navigateButton.active', $tpl_name );
			}else{
				$buttons .= view::tpl('navigateButton', $tpl_name );
			}
			
		}
		
		view::$data['buttons']=&$buttons;
		
		return view::tpl('navigate', $tpl_name );
		
		
		
	}
	
	
	
	
	
	
	
	
	/*
	 *	Стандартная навигационная панель
	 *	для перехода по частям страницы
	 */	 
	function parts($tpl_name='', $limitParts=0 ){
		
		global $system;
		
		
		if( $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['tplType']!='table' ){
			echo '!!! Навигацию возможно вызвать только в шаблоне типа table !!!';
			return false; /// error
		}
		
		
		$part_last = ceil( 
			(	$system['tplLevel'][modules_structure_tpl::getTplLevelLast()]['countChild']-
				$system['tplLevel'][modules_structure_tpl::getTplLevelLast()]['startChild']+1
			)/	$system['tplLevel'][modules_structure_tpl::getTplLevelLast()]['limitChild']
		);
		
		
		
		
		if($part_last<=1) return false;
		
		
		$buttons='';
		
		$partActive = $system['tplLevel'][modules_structure_tpl::getTplLevelLast()]['part'];
		
		
		$i = $partActive - $limitParts;
		if( $i > 1 ){
			view::$data['part'] = 1;
			view::$data['buttonFirst'] = view::tpl('navigateButtonFirst', $tpl_name );
		}else{
			$i = 1;
		}
			
		for( $i; $i<$partActive; $i++ ){
			view::$data['part']=&$i;
			$buttons .= view::tpl('navigateButton', $tpl_name );
		}
		
		
		
		view::$data['part']=$partActive;
		$buttons .= view::tpl('navigateButton.active', $tpl_name );
		
		
		
		$max = $partActive + $limitParts;
		if( $max < $part_last ){
			view::$data['part']=&$part_last;
			view::$data['buttonLast'] = view::tpl('navigateButtonLast', $tpl_name );
		}else{
			$max = $part_last;
		}
			
		for( $i=$partActive+1; $i<=$max; $i++ ){
			view::$data['part']=&$i;
			$buttons .= view::tpl('navigateButton', $tpl_name );
		}
		
		
		
		if( $partActive > 1 ){
			
			view::$data['part'] = $partActive-1;
			view::$data['buttonPrev'] = view::tpl('navigateButtonPrev', $tpl_name );
		}
		
		if( $partActive < $part_last ){
			
			view::$data['part'] = $partActive+1;
			view::$data['buttonNext'] = view::tpl('navigateButtonNext', $tpl_name );
		}
			
		
		view::$data['buttons'] = &$buttons;
		
		return view::tpl('navigate', $tpl_name );
		
		
		
	}
	
	
	
	
	
	
	
	
	/*
	 *	Навигационная панель для изменения количества
	 *	выводимых на странице дочерних элементов
	 *	
	 *	$number	- (минимально 1, по умолчанию 5) количество выводимых кнопок
	 *	$period	- (минимально 1, по умолчанию 5) отступ между числами	 
	 *	$first	- (минимально 1, по умолчанию 5) позиция с которой начинается вывод	 
	 */	 
	function countChild( $tpl_name='', $number='', $period='', $first=''){
		
		global $system;
		
		
		if( !($number>0) ) $number=5;
		if( !($period>0) ) $period=5;
		if( !($first>0) ) $first=5;
		
		$last = $number*($period-1)+$first;
		
		
		if( $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['tplType']!='table' ){
			echo '!!! Навигацию возможно вызвать только в шаблоне типа table !!!';
			return false; /// error
		}
		
		
		//вычисляем количество частей раздела
		if( !($system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['limitChild']>0) ){
			$system['structure']['params']['countChild'] = 
						modules_structure_view::getAttributeSection('__countChild');
		}
		if( !($system['structure']['params']['countChild']>0) ){
			$system['structure']['params']['countChild']=0;
		}
		
		
		
		
		/*
		//вычисляем количество частей раздела
		if( !($system['structure']['params']['countChild']>0) ){
			$system['structure']['params']['countChild'] = 
						modules_structure_view::getAttributeSection('__countChild');
		}
		if( !($system['structure']['params']['countChild']>0) ){
			$system['structure']['params']['countChild']=0;
		}
		
		
		$minimacros['buttons']='';
		
		for($cci=$first; $cci<=$last; $cci+=$period ){
			
			if($system['structure']['params']['countChild'] == $cci){
				$active='active';
			}else{
				$active='';
			}
			
			if( !($system['structure']['params']['part']>1) ){
				$system['structure']['params']['part']=1;
			}
			
			
			$system['structure']['newparams']['countChild'] = $cci;
			$system['structure']['newparams']['part'] = ceil( 
				(($system['structure']['params']['part']-1) * 
				$system['structure']['params']['countChild']+1) /$cci
			);
			
			$minimacros['buttons'] .= modules_structure_tpl::parseTamplate('navigate_buttons',$tpl_name,$active,
				array(
					'title' => $cci,
					'url' => view::attr('_url.newparams')
				)
			);
			
		}
		
		if( $system['structure']['params']['countChild']>0 ){
			$minimacros['title'] = $system['structure']['params']['countChild'];
		}else{
			$minimacros['title'] = '&infin;';
		}
		$minimacros['url'] = view::attr('_url.newparams');
		
		return modules_structure_tpl::parseTamplate('navigate',$tpl_name,'',$minimacros);
		
		
		*/
		
		
		
		
	}
  
}
?>