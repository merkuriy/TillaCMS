var cyr2lat = function(str) {
  var cyr2latChars = new Array(
    ['а', 'a'], ['б', 'b'], ['в', 'v'], ['г', 'g'],
    ['д', 'd'],  ['е', 'e'], ['ё', 'yo'], ['ж', 'zh'], ['з', 'z'],
    ['и', 'i'], ['й', 'y'], ['к', 'k'], ['л', 'l'],
    ['м', 'm'],  ['н', 'n'], ['о', 'o'], ['п', 'p'],  ['р', 'r'],
    ['с', 's'], ['т', 't'], ['у', 'u'], ['ф', 'f'],
    ['х', 'h'],  ['ц', 'c'], ['ч', 'ch'],['ш', 'sh'], ['щ', 'shch'],
    ['ъ', ''],  ['ы', 'y'], ['ь', ''],  ['э', 'e'], ['ю', 'yu'], ['я', 'ya'],

    ['А', 'A'], ['Б', 'B'],  ['В', 'V'], ['Г', 'G'],
    ['Д', 'D'], ['Е', 'E'], ['Ё', 'YO'],  ['Ж', 'ZH'], ['З', 'Z'],
    ['И', 'I'], ['Й', 'Y'],  ['К', 'K'], ['Л', 'L'],
    ['М', 'M'], ['Н', 'N'], ['О', 'O'],  ['П', 'P'],  ['Р', 'R'],
    ['С', 'S'], ['Т', 'T'],  ['У', 'U'], ['Ф', 'F'],
    ['Х', 'H'], ['Ц', 'C'], ['Ч', 'CH'], ['Ш', 'SH'], ['Щ', 'SHCH'],
    ['Ъ', ''],  ['Ы', 'Y'],
    ['Ь', ''],
    ['Э', 'E'],
    ['Ю', 'YU'],
    ['Я', 'YA'],

    ['a', 'a'], ['b', 'b'], ['c', 'c'], ['d', 'd'], ['e', 'e'],
    ['f', 'f'], ['g', 'g'], ['h', 'h'], ['i', 'i'], ['j', 'j'],
    ['k', 'k'], ['l', 'l'], ['m', 'm'], ['n', 'n'], ['o', 'o'],
    ['p', 'p'], ['q', 'q'], ['r', 'r'], ['s', 's'], ['t', 't'],
    ['u', 'u'], ['v', 'v'], ['w', 'w'], ['x', 'x'], ['y', 'y'],
    ['z', 'z'],

    ['A', 'A'], ['B', 'B'], ['C', 'C'], ['D', 'D'],['E', 'E'],
    ['F', 'F'],['G', 'G'],['H', 'H'],['I', 'I'],['J', 'J'],['K', 'K'],
    ['L', 'L'], ['M', 'M'], ['N', 'N'], ['O', 'O'],['P', 'P'],
    ['Q', 'Q'],['R', 'R'],['S', 'S'],['T', 'T'],['U', 'U'],['V', 'V'],
    ['W', 'W'], ['X', 'X'], ['Y', 'Y'], ['Z', 'Z'],

    [' ', '-'],['0', '0'],['1', '1'],['2', '2'],['3', '3'],
    ['4', '4'],['5', '5'],['6', '6'],['7', '7'],['8', '8'],['9', '9'],
    ['-', '-']
  );

  var newStr = new String();
  for (var i = 0; i < str.length; i++) {
    ch = str.charAt(i);
    var newCh = '';
    for (var j = 0; j < cyr2latChars.length; j++) {
      if (ch == cyr2latChars[j][0]) {
        newCh = cyr2latChars[j][1];
      }
    }
    // Если найдено совпадение, то добавляется соответствие, если нет - пустая строка
    newStr += newCh;
  }
  // Удаляем повторяющие знаки - Именно на них заменяются пробелы.
  // Так же удаляем символы перевода строки, но это наверное уже лишнее
  return newStr.replace(/[-]{2,}/gim, '-').replace(/\n/gim, '');
}


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

function $alert(message, type) {
  if (type != '') {
    type = 'alert-' + type;
  }
  $('<div class="alert ' + type + '" style="display: none;">' + message + '</div>')
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

function $createContent(parentId) {
  $.ajax({
    url:      '/api.post/structure_panel.get_child_model?id=' + parentId,
    dataType: 'json',
    success:  function(data) {
      $('#content-id').val(parentId);
      var $type = $('#content-type').empty();
      $.each(data, function() {
        $type.append('<option value="'+this.name+'">'+this.title+'</option>');
      });
      $('#content-create-modal').removeClass('.hidden').modal();
    }
  });
}

function $confirm(header, message, trueText, falseText, trueFunction, falseFunction) {
  var $confirmModal = $('#confirm-modal');
  $confirmModal.find('h3').html(header);
  $confirmModal.find('p').html(message);
  $('#confirm-true-btn')
    .html(trueText)
    .off('click')
    .on('click', function() {
      trueFunction();
      $confirmModal.off('hidden.confirmFalseFunction').modal('hide');
    });
  $('#confirm-false-btn')
    .html(falseText)
    .off('click')
    .on('click', function() {
      falseFunction();
      $confirmModal.off('hidden.confirmFalseFunction').modal('hide');
    });

  $confirmModal
    .off('hidden.confirmFalseFunction')
    .on( 'hidden.confirmFalseFunction', falseFunction)
    .modal();
}


/**
 * Loading settings
 */
var $load_settings = function() {
  $.ajax({
    url: "/api.post/structure_panel.get_settings",
    dataType: 'json',
    success: function(data) {

      $.template('td',
        '<tr data-id="${id}"><td>${name}</td><td><span>${value}</span>' +
          '<div class="float-right"><i class="icon-edit"></i><i class="icon-trash"></i></div></td></tr>'
      );
      $.tmpl("td", data).appendTo("#settings-rows");
    }
  });
}



Tilla = {};
/**
 * Tilla Text Editor TinyMCE
 */
Tilla.TinyMCE = (function () {

  return {
    render: initTinyMCE
  }


  function initTinyMCE (selector, saveCallback) {

    $(selector).tinymce({
      // Location of TinyMCE script
      script_url: '/core/templates/js/tiny_mce/tiny_mce.js',

      // General options
      theme: 'advanced',
      language : "ru",
      relative_urls : false,
      plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,images",
      file_browser_callback : "upload",

      // Theme options
      theme_advanced_buttons1 : "mylistbox,mysplitbutton,bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,undo,redo,link,unlink,|,fullscreen",
      theme_advanced_buttons2 : "",
      theme_advanced_buttons3 : "",
      fullscreen_settings : {
        theme_advanced_buttons1 : "save,|,undo,redo,|,cut,copy,paste,pastetext,pasteword,|,search,replace,|,cleanup,removeformat,visualaid,visualchars,nonbreaking,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,outdent,indent,blockquote,|,sub,sup,|,bullist,numlist,formatselect,code,|,fullscreen",
        theme_advanced_buttons2 : "tablecontrols,|,insertlayer,moveforward,movebackward,absolute,|,link,unlink,anchor,|,insertdate,inserttime,|,charmap,emotions,iespell,media,image,images,advhr,hr,|,cite,abbr,acronym,del,ins,attribs,styleprops,|,typograf",
        theme_advanced_path_location : "bottom"
      },
      theme_advanced_toolbar_location : "top",
      theme_advanced_toolbar_align : "left",
      theme_advanced_statusbar_location : "bottom",
      theme_advanced_resizing : true,

      setup: setup
    });

    function setup (ed) {

      fullscreenFix(ed);
      ed.onActivate.add(fullscreenFix);
      ed.onLoadContent.add(onLoadContent);
    }

    function onLoadContent (ed) {
      ed.addCommand('mceSave', saveCallback);
    }
  }

  function fullscreenFix (ed) {

    if (ed.editorId != 'mce_fullscreen') {
      tinyMCE.myActiveEditor = {};
      tinyMCE.myActiveEditor = ed;
    }
  }

})();




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
        $('[data-id="' + id + '"] span').text($('#title').val());
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
    $('#content-create-modal').modal('hide');
  });

  $(document.body).on('hidden', '#content-create-modal', function(e) {
    var $modal = $(e.target);
    $modal.find('input').val('');
    $modal.find('select').empty();
  });

  $('#create-content-btn').live('click', function() {
    var
      parentId = $('#content-id').val(),
      title = $('#content-name').val();

    $.ajax({
      url:  '/api.post/structure_panel.add_new_element',
      data: {
        parent_id  : parentId,
        name       : $('#content-uri').val(),
        title      : title,
        base_class : $('#content-type').val()
      },
      dataType: 'json',
      success:  function(data) {

        $('#content-create-modal').modal('hide');
        $alert('Новый элемент успешно создан!', 'success');

        if (!(parentId && parentId > 1)) parentId = 'root';
        StructureTree.addNode(parentId, data.id, title);
        Structure.showSection(data.id);
      },
      error: function() {
        // TODO: нужно обрабатывать ошибки
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
            $title = $('#model-add-child option[value="'+id+'"]').text(),
            $name  = $('#model-add-child option[value="'+id+'"]').data('name');

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
      url: "/api.post/structure_panel.get_component_settings?id="+id+"&component="+$component,
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
          $('#TImageSettings').data('id', id);
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

    $('#' + $component + 'Settings').modal('show').data('id', id);
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
        id    : id
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


  /**
   * Generate name
   */
  $('#content-name').live('keyup', function(e) {
    $('#content-uri').val(cyr2lat($(this).val()));
  });


  /**
   * Remove image
   */
  $('.remove-image').live('click', function(){
    name = $(this).closest('.controls').find('input').attr('name');
    id   = $(this).closest('form').find('input[name="id"]').val();
    self = $(this);
    var data = {
      id   : id
    }

    data[name] = '#delete';

    $.ajax({
      url: '/panel/structure?action=editElementSCR&author=admin',
      data: data,
      type: 'POST',
      cache: false,
      success: function(msg){
        console.log(msg);
        if (msg == 'Изображение удалёно.<br/>Изменения сохранены') {
          $alert('Изображение успешно удалено', 'success');
          self.closest('.controls').find('img').attr('src', 'http://placehold.it/37x28');
        }
      }
    });
  });

});