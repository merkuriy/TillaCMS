var $loadPage = function(href) {
  $.ajax({
    url: "/api.post/structure_panel.draw_sub?tpl=" + href,
    success: function(data) {
      $('#content').html(data);
      History.pushState(null, null, '/panel/'+href);
    }
  });

  $('.nav .active').removeClass('active');
  $('a[href="'+href+'"]').parent('li').addClass('active');
}

var $alert = function(message, type) {
  if (type != '') {
    type = 'alert'+type;
  }
  $('<div class="alert '+type+'" style="display: none;">'+message+'</div>')
    .appendTo('#alert-block')
    .fadeIn('slow', function() {
      var self = this;
      setTimeout(
        function() {
          $(self).fadeOut('slow');
        },
        1000
      );
    });
}

$().ready(function() {

  /**
   * Init history plugin
   */
  History = window.History;
  if ( !History.enabled ) {
      return false;
  }

  if (History.getState().hash != '/panel/' && History.getState().hash != '/panel') {
    $loadPage(History.getState().hash.replace('/panel/', ''));
  }



  /**
   * Top navigation links
   */
  $('.nav a').live('click', function() {

    if ($(this).data('no-ajax') != true) {
      $loadPage($(this).attr('href'));

      return false;
    }
  });



  /**
   * TBoolev action
   */
  $('.tboolev button').live('click', function() {
    $(this).closest('.tboolev').find('input').val($(this).data('value'));
    return false;
  });



  /**
   * Content save action
   */
  $('#content-save').live('click', function() {
    $('#node-content form').ajaxSubmit({
      success : function() {
        $alert('Сохранение прошло успешно', 'success');
      }
    });
    return false;
  });



  /**
   * Content cancel action
   */
  $('#content-cancel').live('click', function() {
    $('#node-content').html('');
    return false;
  });
});