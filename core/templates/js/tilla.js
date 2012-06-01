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
    type = 'alert-'+type;
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

var $createContent = function(id) {
  $.ajax({
    url:      "/api.post/structure_panel.get_child_model?id=" + id,
    dataType: 'json',
    success:  function(data) {
      $('#content-id').val(id);
      $type = $('#content-type');
      $type.html('');
      $.each(data, function() {
        $type.append('<option value="'+this.name+'">'+this.title+'</option>');
      });
      $('#content-create-modal').removeClass('.hidden').modal();
    }
  });
}

var $confirm = function(header, message, trueText, falseText, trueFunction, falseFunction) {
  $('#confirm-modal').find('h3').html(header);
  $('#confirm-modal').find('p').html(message);
  $('#confirm-true-btn')
    .html(trueText)
    .die('click')
    .live('click', trueFunction)
    .live('click', function() {
      $('#confirm-modal').modal('hide');
    });
  $('#confirm-false-btn')
    .html(falseText)
    .die('click')
    .live('click', falseFunction)
    .live('click', function() {
      $('#confirm-modal').modal('hide');
    });

  $('#confirm-modal').modal();
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
        var $id = $('#node-content input[name="id"]').val();
        $('[data-id="' + $id + '"] span').text($('#title').val());
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



  /**
   * Create content functions
   */
  $('#create-root').live('click', function() {
    $createContent(0);
  });

  $('#cancel-content-btn').live('click', function() {
    $modal = $('#content-create-modal');
    $modal.find('input').val('');
    $modal.find('select').html('');
    $modal.modal('hide');
  });

  $('#create-content-btn').live('click', function() {
    $.ajax({
      url:      "/api.post/structure_panel.add_new_element",
      data:     {
        'parent_id'  : $('#content-id').val(),
        'name'       : $('#content-uri').val(),
        'title'      : $('#content-name').val(),
        'base_class' : $('#content-type').val()
      },
      dataType: 'json',
      success:  function(data) {
        $id = $('#content-id').val();

        if ($id == 0) {
          $parent = $('#tree ul');
        } else {
          if ($('[data-id="' + $id + '"] .title .icon-folder-close').size() == 1) {
            if ($('[data-parent="' + $id + '"]').size() == 1) {
              $('[data-id="' + $id + '"] .title i').click();
            } else {
              $.tree({'action' : 'load', 'id' : $id});
              $('[data-id="' + $id + '"] .title i').removeClass('icon-folder-close').addClass('icon-folder-open');
            }
            $parent = $('[data-parent="' + $id + '"]');
          } else if ($('[data-id="' + $id + '"] .title .icon-folder-open').size() == 1) {
            $parent = $('[data-parent="' + $id + '"]');
          } else {
            $('[data-id="' + $id + '"]').parent('li').append('<ul data-parent="' + $id + '"></ul>');
            $('[data-id="' + $id + '"] .title i').removeClass('icon-file').addClass('icon-folder-open');
            $parent = $('[data-parent="' + $id + '"]');
          }
        }

        $parent.append(
          '<li><a href="#" data-id="' + data.id + '" data-no-ajax="true">' +
          '<div class="title"><i class="icon-file"></i> ' +
          $('#content-name').val() +
          '</div><div class="control"><i class="icon-plus"></i>' +
          '<i class="icon-edit"></i><i class="icon-trash"></i></div></a></li>'
        );

        $('#cancel-content-btn').click();
        $alert('Новый элемент успешно создан!', 'success');

        setTimeout(function() {
          $('[data-id="'+data.id+'"]').find('.icon-edit').click();
        }, 100);
      }
    });

    return false;
  });
});