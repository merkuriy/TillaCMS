
function main(){
	
	
	$(window).resize(
		function(){
			setSizes();
		}
	);
	
	setTimeout(setSizes, 10);
	
	$('a.debug_point').click(
		function(){
			
			$('#active_point').attr('id', '');
			$(this).attr('id', 'active_point');
			
			//<h1>point #'.$debug_point.'</h1>
			$('#point_content2').html('<h1>point #'+$(this).attr('name')+'</h1>');
			
			
			$.get($(this).attr('title')+'?'+Math.random() ,
				function(data){
					
					
					oldata = $('#point_content2').html();
					$('#point_content2').text(data);
					$('#point_content2').html( oldata+$('#point_content2').html() );
					
				}
			);
			
		}
	);
	
	
}






function setSizes(){
	$('#main_content').add('#point_content')
	.css( 'height', 10)
	.css( 'height', $(document).height());
}








