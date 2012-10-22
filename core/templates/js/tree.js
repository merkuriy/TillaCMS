$.tree = function(options){
  var $ = jQuery;

  var load_nodes = function(node) {
    if (!node) {
      node = 0;
    }

    $.ajax({
      url: "/api.post/structure_panel.get_tree?parent=" + node,
      dataType: 'json',
      success: function(data) {
        draw_nodes(node, data);
      }
    });
  }


  var load_node_for_edit = function(node) {
    $.ajax({
      url:      "/api.post/structure_panel.get_node?id="+node,
      dataType: 'json',
      success: function(data) {
        $.ajax({
          url: "/api.post/structure_panel.draw_sub?tpl=content/edit",
          success: function(tpl) {
            $('#node-content').html($('<div>'+tpl+'</div>').tmpl(data));
            $('#content-zone').css('height', $(window).height()-168);
            $('.fieldLabel').tooltip();

            $.each(data.attrs, function() {
              var url  = this.component,
                  data = this;

              if (this.type) {
                url += this.type;
              }

              $('#components').append($('#' + url).tmpl(data));
              $('.fieldLabel').tooltip();

              if (this.component == 'TSelect') {
                $('#components select').each(function() {
                  $(this).find('option[value="'+$(this).data('value')+'"]').attr('selected', 'selected');
                });
              }
              if (this.component == 'TBoolev') {
                $('#field-'+this.name).find('[data-value="'+this.value+'"]').addClass('active');
              }
              if (this.component == 'TDate') {
                $('#field-'+this.name).datetimepicker({
                  showSecond: true,
                  timeFormat: 'hh:mm:ss',
                  dateFormat: 'dd.mm.yy'
                });
                $('#field-'+this.name).datetimepicker('setDate', new Date(this.value));
              }
              if (this.component == 'TMultiUpLoad') {
                $('#field-'+this.name).pluploadQueue({
                    // General settings
                    runtimes : 'gears,flash,silverlight,browserplus,html5',
                    url : '/panel/structure?action=multiSave&pageid='+node+'&name='+this.name,
                    max_file_size : '10mb',
                    chunk_size : '1mb',
                    unique_names : true,
                    // Resize images on clientside if we can
                    // resize : {width : 320, height : 240, quality : 90},
                    // Specify what files to browse for
                    filters : [
                      {title : "Image files", extensions : "jpg,png"},
                    ],
                    // Flash settings
                    flash_swf_url : '/core/templates/js/plupload/plupload.flash.swf',
                    // Silverlight settings
                    silverlight_xap_url : '/core/templates/js/plupload/plupload.silverlight.xap'
                });
              }
              if (this.component == 'TText') {
                if (this.type == '0') {
                  $('#field-'+this.name).redactor({
                    lang:         'ru',
                    autoresize:   false,
                    imageGetJson: '/api.post/structure_panel.get_images_list',
                    imageUpload:  '/api.post/structure_panel.image_upload',
                    fileUpload:   '/api.post/structure_panel.file_upload',
                    plugins:      ['fullscreen']
                  });
                }
                if (this.type == '3') {
                  $('#field-'+this.name).tinymce({
                    // Location of TinyMCE script
                    script_url : '/core/templates/js/tiny_mce/tiny_mce.js',

                    // General options
                    theme : "advanced",
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

                    setup : function(ed){        
                      if (ed.editorId != 'mce_fullscreen') {
                        tinyMCE.myActiveEditor = {};
                        tinyMCE.myActiveEditor = ed;
                      }
                      ed.onActivate.add( function(ed){
                        if( ed.editorId != 'mce_fullscreen' ){
                          tinyMCE.myActiveEditor = {};
                          tinyMCE.myActiveEditor = ed;
                        }
                      });
                      ed.onLoadContent.add( function(ed){
                        ed.addCommand('mceSave', function(){
                          $('#content-save').click();
                          return false;
                        });
                      }); 
                    }
                  });
                }
              }
            });
            window.location.hash = node;
          }
        });
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
      var icon = 'folder-close';
      if (this.child == 0) {
        icon = 'file';
      }
      $('[data-parent="'+node+'"]').append(
        '<li><a href="#" data-id="' + this.id + '" data-no-ajax="true">' +
        '<div class="title"><i class="icon-' + icon + '"></i> ' +
        '<span>' + this.title + '</span>' +
        '</div><div class="control"><i class="icon-plus"></i><i class="icon-edit"></i><i class="icon-trash"></i></div></a></li>'
      );
    });

    if (window.location.hash != '') {
      id = window.location.hash.replace('#','');
      load_node_for_edit(id);
    }
  }



  if (options) {
    if (options.action == 'load') {
      load_nodes(options.id);
    }
  } else {
    var options = $.extend({
      id:           '#tree',
      width:        0,
      height:       0,
      items:        '',
      url:          '',
      dblClick:     function(node){
        load_node_for_edit(node);
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


    /* Open folder event */
    $(options.id + ' .icon-folder-close').die('click');
    $(options.id + ' .icon-folder-close').live('click', function() {

      var node = $(this).closest('a').data('id');

      if ($(this).closest('li').find('ul').size() == 0) {
        load_nodes(node);
      } else {
        $('ul[data-parent="' + node + '"]').show();
      }

      $(this).removeClass('icon-folder-close');
      $(this).addClass('icon-folder-open');

      return false;
    });


    /* Close folder event */
    $(options.id + ' .icon-folder-open').die('click');
    $(options.id + ' .icon-folder-open').live('click', function() {

      var node = $(this).closest('a').data('id');

      $('ul[data-parent="' + node + '"]').hide();
      $(this).removeClass('icon-folder-open');
      $(this).addClass('icon-folder-close');

      return false;
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
            url: "../core/admin.php",
            data: "module=structure&author=admin&action=deleteElement&id="+self.closest('a').data('id'),
            success: function(msg){
              $alert('Удаление успешно завершено!', 'success');
              if (self.closest('ul').find('li').size() == 1) {
                self.closest('ul').parent('li').children('a').children('i').removeClass('icon-folder-open').addClass('icon-file');
              }
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

    /* Create first ul */
    $(options.id).append('<ul class="nav nav-list" data-parent="0"></ul>');
    $(options.id+' ul').nestedSortable({
      items: 'li',
      placeholder: 'ui-sortable-highlight',
      listType: 'ul',
      stop: function(event, ui) {
        var $node = $(ui.item[0]).parent().parent().children('ul');
            $icon = $(ui.item[0]).parent().parent().children('a').children('.icon-folder-close');

        if ($icon.size() > 0) {
          $alert('Предварительно откройте элемент для перетаскивания!');
          $node.remove();
          return false;
        } else {
          var $pos    = $(ui.item[0]).index(),
              $parent = 0,
              $nodeID = $(ui.item[0]).children('a').data('id');

          if ($(ui.item[0]).parent().parent().attr('id') != 'tree') {
            $parent = $(ui.item[0]).parent().parent().children('a').data('id');
          }

          $.ajax({
            type: "GET",
            url: "../core/admin.php",
            data: "module=structure&action=updatePosition&id="+$nodeID+"&parent="+$parent+"&pos="+$pos
          });

        }
      }
    });

    load_nodes(0);
  }
};