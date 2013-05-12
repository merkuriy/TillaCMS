/*
 * Module: Structure
 */

window.Structure || (Structure = (function() {

  window.$window || ($window = $(window));
  $window.bind('resize', resized);

  return {
    init: init,
    showSection: showSectionEditPage,
    removeSection: removeSection
  }

  function init() {
    resized();
    StructureTree.init();

    if (window.location.hash != '') {
      showSectionEditPage(window.location.hash.replace('#', ''));
    }
  }

  function resized() {
    $('#tree').css('height', $(window).height()-130);
    $('#content-zone').css('height', $(window).height()-168);
  }

  function removeSection(section) {

    $confirm(
      'Удаление элемента',
      'Вы действительно хотите удалить данный элемент "' + section.text + '" (id:' + section.id + ') и все дочерние к нему элементы?',
      'Да, удалить',
      'Нет, отменить',
      function() {
        $alert('Удаление успешно завершено!', 'success');
        StructureTree.removeNode(section.id);

        $.ajax({
          type: 'GET',
          url: '../core/admin.php',
          data: 'module=structure&author=admin&action=deleteElement&id='+section.id,
          error: function() {
            // TODO: нужно обработать ошибку
          }
        });
      },
      function() {
        $alert('Удаление отменено', 'success');
      }
    );
  }

  function showSectionEditPage(sectionId) {
    $.ajax({
      url:      "/api.post/structure_panel.get_node?id="+sectionId,
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
                  url : '/panel/structure?action=multiSave&pageid='+sectionId+'&name='+this.name,
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
            window.location.hash = sectionId;
          }
        });
      }
    });
  }

})());

Structure.init();
