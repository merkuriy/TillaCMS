$.model_tree = function(options){
  var $ = jQuery;

  var load_nodes = function(node) {
    if (!node) {
      node = 0;
    }

    $.ajax({
      url: "/api.post/structure_panel.get_model_tree?parent=" + node,
      dataType: 'json',
      success: function(data) {
        draw_nodes(node, data);
      }
    });
  }


  var draw_nodes = function(node, data) {
    if ($('[data-parent="'+node+'"]').size() == 0) {
      // create ul
      $('[data-id="' + node + '"]').closest('li').append('<ul data-parent="'+node+'"></ul>');
      $('[data-id="' + node + '"]').sortable();
    }
    
    // append nodes
    $.each(data, function() {

      var icon = 'file';

      $('[data-parent="'+node+'"]').append(
        '<li><a href="#" data-id="' + this.id + '" data-no-ajax="true">' +
        '<div class="title"><i class="icon-' + icon + '"></i> ' +
        '<span>' + this.title + '</span>' +
        '</div><div class="control"><i class="icon-edit"></i><i class="icon-trash"></i></div></a></li>'
      );
    });
  }



  if (options) {
    if (options.action == 'load') {
      load_nodes(options.id);
    }
  } else {
    var options = $.extend({
      id:           '#model_tree',
      width:        0,
      height:       0,
      items:        '',
      url:          '',
      dblClick:     function(node){
        $.ajax({
          url:      "/api.post/structure_panel.get_model?id="+node,
          dataType: 'json',
          success: function(data) {
            $.ajax({
              url: "/api.post/structure_panel.draw_sub?tpl=model/edit",
              success: function(tpl) {
                $('#model-content').html($('<div>'+tpl+'</div>').tmpl(data));
                $('#model-zone').css('height', $(window).height()-168);

                var $child_markup = '<tr data-id="${id}"><td>${title}</td><td>${name}</td><td class="width14"><i class="icon-trash control"></i></td></tr>',
                    $attr_markup  = '<tr data-id="${id}"><td>${title}</td><td>${name}</td><td class="info"><i class="icon-th"></i> ${value}</td><td class="width28 ta-right"><i class="icon-cog control"></i><i class="icon-trash control"></i></td></tr>';

                $.template('model-child', $child_markup);
                $.tmpl("model-child", data.childs).appendTo("#model-childs");
                $.template('model-attr', $attr_markup);
                $.tmpl("model-attr", data.attrs).appendTo("#model-attrs");

                $('#model-attrs tr').each(function() {
                  $text = $.trim($(this).find('td:eq(2)').text());
                  if ($text != 'TText' && $text != 'TSelect' && $text != 'THidden' && $text != 'TImage') {
                    $(this).find('.icon-cog').hide();
                  }
                });
              }
            });
          }
        });
      },
      endDrag:      function(node){},
      click:        function(node){},
      afterLoad:    function(){},
      addRoot:      function(){},
      exit:         function(){},
      contextMenu:  '',
      root:         0,
      selected:     ''
    }, options);


    /* Select node */
    $(options.id + ' a').die('click');
    $(options.id + ' a').live('click', function() {
      $(options.id + ' .active').removeClass('active');
      $(this).addClass('active');
      return false;
    });


    /* Open node for edit */
    $(options.id + ' a').die('dblclick');
    $(options.id + ' a').live('dblclick', function() {
      options.dblClick($(this).data('id'));
    });

    $(options.id + ' .icon-edit').die('click');
    $(options.id + ' .icon-edit').live('click', function() {
      options.dblClick($(this).closest('a').data('id'));
    });


    /* Remove node */
    $(options.id + ' .icon-trash').die('click');
    $(options.id + ' .icon-trash').live('click', function() {
      var self = $(this);
      $confirm(
        'Удаление элемента',
        'Вы действительно хотите удалить данный элемент и все дочерние к нему элементы?',
        'Да, удалить',
        'Нет, отменить',
        function() {
          $.ajax({
            type: "GET",
            url: "/api.post/structure_panel.model_remove?id="+self.closest('a').data('id'),
            success: function(msg){
              $alert('Удаление успешно завершено!', 'success');
              self.closest('li').remove();
            }
          });
        },
        function() {
          $alert('Удаление отменено', 'success');
        }
      );
    });

    /* Add node */
    $(options.id + ' .icon-plus').die('click');
    $(options.id + ' .icon-plus').live('click', function() {
      $createContent($(this).closest('a').data('id'));
    });

    /* Create first ul */
    $(options.id).append('<ul class="nav nav-list" data-parent="0"></ul>');

    load_nodes(0);
  }
};