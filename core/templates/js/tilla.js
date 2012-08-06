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


/**
 * Loading settings
 */
var $load_settings = function() {
  $.ajax({
    url: "/api.post/structure_panel.get_settings",
    dataType: 'json',
    success: function(data) {
      var $td = '<tr data-id="${id}"><td>${name}</td><td><span>${value}</span><div class="float-right"><i class="icon-edit"></i><i class="icon-trash"></i></div></td></tr>';
      $('#settings-rows').html();
      $.template('td', $td);
      $.tmpl("td", data).appendTo("#settings-rows");
    }
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



  /**
   * Editing models
   */
  $('#model-child-modal').live('click', function() {

    $.ajax({
      url: "/api.post/structure_panel.get_model_tree",
      dataType: 'json',
      success: function(data) {
        var $option = '<option value="${id}" data-name="${name}">${title}</option>';
        $('#model-add-child').html('');
        $.template('option', $option);
        $.tmpl("option", data).appendTo("#model-add-child");
      }
    });
  });


  $('#model-add-child-cancel').live('click', function() {
    $('#childModal').modal('hide');
  });


  $('#model-add-child-submit').live('click', function() {

    $.ajax({
      url: "/api.post/structure_panel.model_add_child?type="+$('#model-add-child').val()+"&id="+$('#model-content input[name="id"]').val(),
      dataType: 'json',
      success: function(data) {
        var $id    = $('#model-add-child').val(),
            $title = $('#model-add-child option[value="'+$id+'"]').text(),
            $name  = $('#model-add-child option[value="'+$id+'"]').data('name');

        $('#model-childs').append(
          '<tr data-id="'+data+'"><td>'+$title+'</td><td>'+$name+'</td><td class="width14"><i class="icon-trash control"></i></td></tr>'
        );

        $('#childModal').modal('hide');
        $alert('Дочерний элемент добавлен!', 'success');
      }
    });
    return false;
  });



  $('#model-attr-modal').live('click', function() {
    $('#model-add-title').val('');
    $('#model-add-name').val('');
    $('#model-add-attr').val('');
    $('#model-add-name').closest('.control-group').removeClass('error');
    $('#model-add-title').closest('.control-group').removeClass('error');
  });



  $('#model-add-attr-cancel').live('click', function() {
    $('#attrModal').modal('hide');
  });


  $('#model-add-attr-submit').live('click', function() {
    var save = true;

    if ($('#model-add-name').val() == '') {
      $('#model-add-name').closest('.control-group').addClass('error');
      save = false;
    } else {
      $('#model-add-name').closest('.control-group').removeClass('error');
    }

    if ($('#model-add-title').val() == '') {
      $('#model-add-title').closest('.control-group').addClass('error');
      save = false;
    } else {
      $('#model-add-title').closest('.control-group').removeClass('error');
    }

    if (save) {
      $.ajax({
        method: "GET",
        url: "/api.post/structure_panel.model_add_attr",
        data : {
          id   : $('#model-content input[name="id"]').val(),
          value: $('#model-add-attr').val(),
          name : $('#model-add-name').val(),
          title: $('#model-add-title').val()
        },
        dataType: 'json',
        success: function(data) {

          if ($('#model-add-attr').val() == 'THidden' || $('#model-add-attr').val() == 'TSelect' || $('#model-add-attr').val() == 'TText' || $('#model-add-attr').val() == 'Timage') {
            control = '<i class="icon-cog control"></i>';
          } else {
            control = '';
          }

          $('#model-attrs').append(
            '<tr data-id="'+data+'"><td>'+$('#model-add-title').val()+'</td><td>'+$('#model-add-name').val()+'</td><td class="info"><i class="icon-th"></i> '+$('#model-add-attr').val()+'</td><td class="width28">'+control+'<i class="icon-trash control"></i></td></tr>'
          );

          $('#attrModal').modal('hide');
          $alert('Атрибут добавлен!', 'success');
        }
      });
    }
    return false;
  });


  // Removing child
  $('#model-childs .icon-trash, #model-attrs .icon-trash').live('click', function() {
    var self = $(this);

    $confirm(
      'Удаление элемента',
      'Вы действительно хотите удалить данный элемент?',
      'Да, удалить',
      'Нет, отменить',
      function() {
        $.ajax({
          type: "GET",
          url: "/api.post/structure_panel.model_remove_child?id="+self.closest('tr').data('id'),
          success: function(msg){
            $alert('Удаление успешно завершено!', 'success');
            self.closest('tr').remove();
          }
        });
      },
      function() {
        $alert('Удаление отменено', 'success');
      }
    );
  });


  // Attr settings
  $('#model-attrs .icon-cog').live('click', function() {
    var $component = $.trim($(this).closest('tr').find('td:eq(2)').text()),
        $id        = $(this).closest('tr').data('id');

    $.ajax({
      type: "GET",
      dataType: 'json',
      url: "/api.post/structure_panel.get_component_settings?id="+$id+"&component="+$component,
      success: function(msg){
        if ($component == 'THidden') {
          $('#model-thidden-default').val(msg.value);
        }

        if ($component == 'TText') {
          $('#model-ttext-type option[value="'+msg.value+'"]').attr('selected', 'selected');
        }

        if ($component == 'TSelect') {
          var $tselect_markup = '<tr data-id="${id}"><td>${title}</td><td>${name}</td><td class="width14"><i class="icon-trash control"></i></td></tr>';

          $("#model-tselect-values").html('');
          $.template('model-tselect', $tselect_markup);
          $.tmpl("model-tselect", msg.value).appendTo("#model-tselect-values");
        }

        if ($component == 'TImage') {
          $('.timage-rule').remove();
          $('#TImageSettings').data('id', $id);
          var resize = ['Не масштабировать', 'Вписывыть в обслать', 'Подрезать под обслать'];
          $.each(msg.rule, function() {
            $('#TImageSettings .control-row').before(
                '<tr data-id="'+this.id+'" class="timage-rule">'
              + '<td><span data-name="psevdo">'+this.psevdo+'</span></td>'
              + '<td><span data-name="width">'+this.width+'</span></td>'
              + '<td><span data-name="height">'+this.height+'</span></td>'
              + '<td><span data-name="resize" data-val="'+this.resize+'">'+resize[this.resize]+'</span></td>'
              + '<td><span data-name="address">'+this.path+'</span><div class="float-right"><i class="icon-edit control"></i> <i class="icon-trash control"></i></div></td>'
              + '</tr>'
            );
          });
        }
      }
    });

    $('#' + $component + 'Settings').modal('show').data('id', $id);
  })


  $('#add-image-settings').live('click', function() {
    var save = true;
    var resize = ['Не масштабировать', 'Вписывыть в обслать', 'Подрезать под обслать'];

    if ($('#width').val() == '') {save = false;}
    if ($('#height').val() == '') {save = false;}

    if (save) {
      $.ajax({
        type: "POST",
        data: {
          parent_id : $('#TImageSettings').data('id'),
          width     : $('#width').val(),
          height    : $('#height').val(),
          psevdo    : $('#psevdo').val(),
          resize    : $('#resize').val(),
          path      : $('#address').val()
        },
        url: "/api.post/structure_panel.create_image_settings",
        success: function(msg){
          $('#TImageSettings .control-row').before(
              '<tr data-id="'+msg+'" class="timage-rule">'
            + '<td><span data-name="psevdo">'+$('#psevdo').val()+'</span></td>'
            + '<td><span data-name="width">'+$('#width').val()+'</span></td>'
            + '<td><span data-name="height">'+$('#height').val()+'</span></td>'
            + '<td><span data-name="resize" data-val="'+$('#resize').val()+'">'+resize[$('#resize').val()]+'</span></td>'
            + '<td><span data-name="address">'+$('#address').val()+'</span><div class="float-right"><i class="icon-edit control"></i> <i class="icon-trash control"></i></div></td>'
            + '</tr>'
          );
        }
      });
    }

    return false;
  });



  /**
   * Removing image rule
   */
  $('#TImageSettings .icon-trash').live('click', function() {
    var self = $(this),
        eq   = $(this).closest('tr').data('id');

    $confirm(
      'Удаление правла',
      'Вы действительно хотите удалить данное правило?',
      'Да, удалить',
      'Нет, отменить',
      function() {
        $.ajax({
          type: "GET",
          url: "/api.post/structure_panel.remove_image_settings?id="+eq,
          success: function(msg){
            $alert('Удаление успешно завершено!', 'success');
            $('#TImageSettings tr[data-id="'+eq+'"]').remove();
          }
        });
      },
      function() {
        $alert('Удаление отменено', 'success');
      }
    );
  });



  /**
   * TImage edit settings
   */
  $('#TImageSettings .icon-edit').live('click', function() {
    var $tr = $(this).closest('tr');

    $tr.find('span').each(function() {
      var name = $(this).data('name');
      if (name != 'resize') {
        $(this).replaceWith('<input type="text" id="edit-'+name+'" value="'+$(this).text()+'" data-old="'+$(this).text()+'">');
      } else {
        var val  = $(this).data('val');
        $(this).replaceWith(
          '<select id="edit-resize" data-old="'+val+'">'
          + '<option value="0">Не масштабировать</option>'
          + '<option value="1">Вписывыть в обслать</option>'
          + '<option value="2">Подрезать под область</option>'
          + '</select>'
        );
        $('#edit-resize').val(val);
      }
    });
    $tr.find('.float-right').html('<i class="icon-ok save-settings"></i> <i class="icon-remove"></i>');
  });



  /**
   * Cancel TImage setting edit
   */
  $('#TImageSettings .icon-remove').live('click', function() {
    var $tr = $(this).closest('tr'),
        resize = ['Не масштабировать', 'Вписывыть в обслать', 'Подрезать под обслать'];

    $tr.replaceWith(
        '<tr data-id="'+$tr.data('id')+'" class="timage-rule">'
      + '<td><span data-name="psevdo">'+$('#edit-psevdo').data('old')+'</span></td>'
      + '<td><span data-name="width">'+$('#edit-width').data('old')+'</span></td>'
      + '<td><span data-name="height">'+$('#edit-height').data('old')+'</span></td>'
      + '<td><span data-name="resize" data-val="'+$('#edit-resize').data('old')+'">'+resize[$('#edit-resize').data('old')]+'</span></td>'
      + '<td><span data-name="address">'+$('#edit-address').data('old')+'</span><div class="float-right"><i class="icon-edit control"></i> <i class="icon-trash control"></i></div></td>'
      + '</tr>'
    );
  });



  /**
   * Save TImage settings
   */
  $('#TImageSettings .save-settings').live('click', function() {
    var save   = true,
        $tr    = $(this).closest('tr'),
        resize = ['Не масштабировать', 'Вписывыть в обслать', 'Подрезать под обслать'];

    if ($('#edit-width').val() == '') {save = false;}
    if ($('#edit-height').val() == '') {save = false;}

    if (save) {
      $.ajax({
        type: "POST",
        data: {
          parent_id : $('#TImageSettings').data('id'),
          id        : $tr.data('id'),
          width     : $('#edit-width').val(),
          height    : $('#edit-height').val(),
          psevdo    : $('#edit-psevdo').val(),
          resize    : $('#edit-resize').val(),
          path      : $('#edit-address').val()
        },
        url: "/api.post/structure_panel.save_image_settings",
        success: function(msg){
          $alert('Настройка успешно сохранена', 'success');

          $tr.replaceWith(
              '<tr data-id="'+$tr.data('id')+'" class="timage-rule">'
            + '<td><span data-name="psevdo">'+$('#edit-psevdo').val()+'</span></td>'
            + '<td><span data-name="width">'+$('#edit-width').val()+'</span></td>'
            + '<td><span data-name="height">'+$('#edit-height').val()+'</span></td>'
            + '<td><span data-name="resize" data-val="'+$('#edit-resize').val()+'">'+resize[$('#edit-resize').val()]+'</span></td>'
            + '<td><span data-name="address">'+$('#edit-address').val()+'</span><div class="float-right"><i class="icon-edit control"></i> <i class="icon-trash control"></i></div></td>'
            + '</tr>'
          );
        }
      });
    }
  });



  /**
   * Вызов диалога создания модели
   */
  $('#create-model').live('click', function() {
    $('#model-create-modal').modal('show');
  });



  /**
   * Отмена создания модели
   */
  $('#cancel-model-btn').live('click', function() {
    $('#model-create-modal input').val('');
    $('#model-create-modal').modal('hide');
  });



  /**
   * Создание модели
   */
  $('#create-model-btn').live('click', function() {
    var save = true;

    if ($('#model-title').val() == '') {save = false;}
    if ($('#model-call').val() == '') {save = false;}

    if (save) {
      $.ajax({
        type: "POST",
        data: {
          title : $('#model-title').val(),
          name  : $('#model-call').val()
        },
        url: "/api.post/structure_panel.create_model",
        success: function(msg) {
          $parent = $('#model_tree ul');
          $parent.append(
            '<li><a href="#" data-id="' + msg + '" data-no-ajax="true">' +
            '<div class="title"><i class="icon-file"></i> ' +
            $('#model-title').val() +
            '</div><div class="control">' +
            '<i class="icon-edit"></i><i class="icon-trash"></i></div></a></li>'
          );

          $('#cancel-model-btn').click();
          $('a[data-id="'+msg+'"] .icon-edit').click();
          $alert('Модель успешно создана', 'success');
        }
      });
    }
  });



  /**
   * Отмена редактирования модели
   */
  $('#model-cancel').live('click', function() {
    $('#model-content').html('');
    return false;
  });



  /**
   * Сохранение модели
   */
  $('#model-save').live('click', function() {
    $.ajax({
      type: "POST",
      data: {
        title : $('#title').val(),
        name  : $('#name').val(),
        id    : $('#model-id').val()
      },
      url: "/api.post/structure_panel.update_model",
      success: function() {
        $('#model_tree a[data-id="'+$('#model-id').val()+'"]').html(
          '<div class="title"><i class="icon-file"></i> ' +
          $('#title').val() +
          '</div><div class="control">' +
          '<i class="icon-edit"></i><i class="icon-trash"></i></div>'
        );
        $alert('Модель успешно сохранена', 'success');
      }
    });
    return false;
  });



  $('.close-dialog').live('click', function() {
    $(this).closest('.modal').modal('hide');
  });



  /**
   * Save THidden settings
   */
  $('#model-thidden-settings-submit').live('click', function() {
    $.ajax({
      type: "POST",
      data: {
        id    : $('#THiddenSettings').data('id'),
        value : $('#model-thidden-default').val()
      },
      url: "/api.post/structure_panel.save_hidden_settings",
      success: function() {
        $alert('Значение успешно сохранено', 'success');
        $('#THiddenSettings').modal('hide');
      }
    });
    return false;
  });



  /**
   * Add TSelect setting
   */
  $('#TSelectSettings .icon-plus').live('click', function() {
    $.ajax({
      type: "POST",
      data: {
        id    : $('#TSelectSettings').data('id'),
        title : $('#model-tselect-visible').val(),
        name  : $('#model-tselect-system').val()
      },
      url: "/api.post/structure_panel.save_select_settings",
      success: function(msg) {
        $alert('Значение успешно сохранено', 'success');
        $('#model-tselect-values').append(
            '<tr data-id="'+msg+'"><td>'
          + $('#model-tselect-visible').val()
          + '</td><td>'
          + $('#model-tselect-system').val()
          + '</td><td class="width14"><i class="icon-trash control"></i></td></tr>'
        );

        $('#model-tselect-visible').val('');
        $('#model-tselect-system').val('');
      }
    });
  });



  /**
   * Remove TSelect settings
   */
  $('#TSelectSettings .icon-trash').live('click', function() {
    var $tr = $(this).closest('tr'),
        $id = $tr.data('id');

    $.ajax({
      type: "POST",
      data: {
        id    : $id
      },
      url: "/api.post/structure_panel.remove_select_settings",
      success: function(msg) {
        $alert('Значение успешно сохранено', 'success');
        $tr.remove();
      }
    });
  });



  /**
   * Save TText settings
   */
  $('#model-ttext-settings-submit').live('click', function() {
    $.ajax({
      type: "POST",
      data: {
        id    : $('#TTextSettings').data('id'),
        type  : $('#model-ttext-type').val()
      },
      url: "/api.post/structure_panel.save_text_settings",
      success: function(msg) {
        $alert('Значение успешно сохранено', 'success');
        $('#TTextSettings').modal('hide');
      }
    });
  });



  /**
   * Remove site settings
   */
  $('#settings-rows .icon-trash').live('click', function() {
    var self = $(this);
    $confirm(
      'Удаление настройки',
      'Вы действительно хотите удалить данную настройку?',
      'Да, удалить',
      'Нет, отменить',
      function() {
        $.ajax({
          type: "GET",
          url: "/api.post/structure_panel.remove_settings?id="+self.closest('tr').data('id'),
          success: function(msg){
            $alert('Удаление успешно завершено!', 'success');
            self.closest('tr').remove();
          }
        });
      },
      function() {
        $alert('Удаление отменено', 'success');
      }
    );
  });



  /**
   * Edit site settings
   */
  $('#settings-rows .icon-edit').live('click', function() {
    var self     = $(this).closest('tr'),
        name     = self.find('td:eq(0)'),
        val      = self.find('span'),
        cont     = self.find('div'),
        old_name = name.text(),
        old_val  = val.text();

    name.html('<input type="text" value="'+old_name+'" data-val="'+old_name+'" class="name" />');
    val.html('<input type="text" value="'+old_val+'" data-val="'+old_val+'" class="val" />');
    self.find('input').css('margin-bottom', '0');
    cont.html('<i class="icon-ok"></i><i class="icon-remove"></i>');
  });



  /**
   * Cancel edit site settings
   */
  $('#settings-rows .icon-remove').live('click', function() {
    var self     = $(this).closest('tr'),
        name     = self.find('td:eq(0)'),
        val      = self.find('span'),
        cont     = self.find('div'),
        old_name = name.find('input').data('val'),
        old_val  = val.find('input').data('val');

    name.html(old_name);
    val.html(old_val);
    cont.html('<i class="icon-edit"></i><i class="icon-trash"></i>');    
  });



  /**
   * Save edit site settings
   */
  $('#settings-rows .icon-ok').live('click', function() {
    var self     = $(this).closest('tr'),
        name     = self.find('td:eq(0)'),
        val      = self.find('span'),
        cont     = self.find('div'),
        old_name = name.find('input').val(),
        old_val  = val.find('input').val();

    $.ajax({
      type: "GET",
      url: "/api.post/structure_panel.update_settings?id="+self.data('id'),
      data : {
        name  : old_name,
        value : old_val
      },
      success: function(msg){
        $alert('Сохранение успешно завершено!', 'success');
        name.html(old_name);
        val.html(old_val);
        cont.html('<i class="icon-edit"></i><i class="icon-trash"></i>');
      }
    });
  });



  /**
   * Save edit site settings
   */
  $('#settings .icon-plus').live('click', function() {
    var name     = $('#settings-name').val(),
        val      = $('#settings-val').val();

    $.ajax({
      type: "POST",
      url: "/api.post/structure_panel.create_settings",
      data : {
        name  : name,
        value : val
      },
      success: function(msg){
        $alert('Сохранение успешно завершено!', 'success');
        $("#settings-rows").append(
          '<tr data-id="'+msg.id+'"><td>'+msg.name+'</td><td><span>'+msg.value+'</span><div class="float-right"><i class="icon-edit"></i><i class="icon-trash"></i></div></td></tr>'
        );
        $('#settings-name').val('');
        $('#settings-val').val('');
      },
      dataType : 'JSON'
    });
  });
});