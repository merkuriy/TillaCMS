
function main () {
	
	$(window).resize(function(){
    setSizes();
  });
	
	setTimeout(setSizes, 10);
	
	$('a.debug_point').click(
		function(){

      var $pointContent = $('#point_content2');

			$('#active_point').attr('id', '');
			$(this).attr('id', 'active_point');

      $pointContent.html('<h1>point #'+$(this).attr('name')+'</h1>');
			
			$.get($(this).attr('title')+'?'+Math.random(),
				function(data){

          var oldata = $pointContent.html();

          $pointContent
            .text(data)
            .html(oldata + $('#point_content2').html());
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


main();