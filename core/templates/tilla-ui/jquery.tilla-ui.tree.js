/**
 * Tilla UI - набор компонентов для создания пользовательских интерфейсов
 * Компонент - Дерево
 *
 * Depends on: ?
 *
 * @author Andrew Yudin Pasarian <pasarian@gmail.com>
 * @author Uriy MerkUriy Efremochkin <efremochkin@gmail.com>
 * @version 0.3.0b
 * @date 10.05.2013
 *
 * Copyright (c) 2007-2013 Tilla UI contributors
 * Licensed under the MIT License
 * http://www.opensource.org/licenses/mit-license.php
 */
(function( $, undefined ) {

  $.tree = function(options) {

    var
      $tree,
      $linkNodeSelected,
      // внутренние настройки
      isShowedCM = false,
      isDragged = false, // Если в данный момент происходит перетаскивание
      $dragNode = '', // перемещаемый узел
      $dragLinkNode = '',
      $liTargetDrag = '', // целевой узел, в который будет перемещен перетаскиваемый элемент
      startDrag = false,
      status = '', // статус, равный заголовку узла
      inFocus = false,
      $draggedCloneNode = $();

    var options = $.extend({
      id:     'tree',
      items:  '',
      url:    '',
      root:   0,
      contextMenu: false,
      selected: '',
      dblClick:   function(node){},
      endDrag:    function(node){},
      click:      function(node){},
      afterLoad:  function(){},
      addRoot:    function(){},
      exit:       function(){},
      tplDraggedCloneNode: function(node) {
        return '<div style="position: absolute;">' + node.label + ' (id:' + node.id + ')</div>';
      }
    }, options);


    // Просто раскрыть узел
    function openNode($arrowNode) {
      var $liNode = $arrowNode.parent().parent();
      // Меняем класс folder (закрытая папка)
      $liNode.removeClass('folder').addClass('folder-open');
      // Парсим элементы открываемого элемента для их правильной отрисовки
      parseTree($arrowNode.parent().parent('li'));
      // Открываем блок с дочерними элементами
      $liNode.children('ul').hide().slideDown();
    }

    // Раскрыть узел с проверкой загруженные дочерних узлов
    function openNodeAndPreload($linkNode) {
      var
        $liNode = $linkNode.parent(),
        $arrowNode = $linkNode.children('.quiArrowTree'),
        $childrensNodeUl = $liNode.children('ul');

      if ($childrensNodeUl.hasClass('ajax')) {
        // TODO: переделать на подстановку класса прелоадера
        $liNode.children('.quiIconTree')
          .css("background-position", "0px 0px")
          .css("background-image", "url(/css_js/resources/tree/ajax-loader.gif)");

        // Если дочерние элементы получаются через AJAX запускаем механизм получения
        $.getJSON($childrensNodeUl.attr('url'), function(JSON) {
          // Парсим полученный JSON и подключаем полученный код к открываемому элементу
          $childrensNodeUl.empty().append(parseItems(JSON));
          // Удаляем метку AJAX указывающую на источник получения дочерних элементов
          $childrensNodeUl.removeClass('ajax');
          // Вызываем функцию открытия папки
          openNode($arrowNode);
        });

      } else {
        // Если элементы загружены изначально или получены через AJAX ранее
        // Вызываем функцию открытия папки
        openNode($arrowNode);
      }
    }

    // Свернуть узел
    function closeNode($linkNode) {
      var
        $liNode = $linkNode.parent(),
        $childrensNodeUl = $liNode.children('ul');

      $liNode.removeClass('folder-open').addClass('folder');
      $childrensNodeUl.slideUp();
    }

    // Функция парсинга дерева для определения типа иконок и стрелок
    function parseTree(elem) {
      // Обходим все дочерние элементы указанного (elem) узла
      options.afterLoad();
    }

    // Проверить наличие соседних узлов, если их нет, то изменить тип родительского узла на "файл"
    function hasAdjacentNodes($liNode) {
      var
        $ul = $liNode.parent(),
        lengthAdjacentNoded = $ul.children('li:not(.quiLineTree)').length - 1;
      if (lengthAdjacentNoded === 0) {
        $ul.parent().removeClass('folder-open').addClass('file');
        $ul.remove();
      }
      return lengthAdjacentNoded;
    }

    // Установка нового класса
    function treeSetClass(elem,clas){
      elem.children('ul').children('li').each(function(i){
        $(this).addClass(clas+'Child');
      });
    }

    // Удаление класса
    function treeDelClass(elem,clas){
      elem.children('ul').children('li').each(function(i){
        $(this).removeClass(clas+'Child');
      });
    }

    // Выделить узел дерева
    function selectNode($node) {
      // Запоминаем новый выделенный узел
      $linkNodeSelected = $node.parent().parent();
      // Вызываем действие Click описанное при инициализации компонента
      options.click($linkNodeSelected.parent());
      // Убираем выделение активности у всех элементов
      $('.quiTreeNode').removeClass('active');
      // Выставляем выделение активности на нажатый элемент
      $linkNodeSelected.addClass('active');
    }

    // Выделить вышележащий узел
    function selectNodeUp() {
      var
        $linkNodesVisible = $('.quiTreeNode:visible'),
        index = $linkNodesVisible.index($linkNodeSelected);
      if (index > 0) {
        selectNode($linkNodesVisible.eq(--index).find('.quiLabelTree:first'));
      }
    }

    // Выделить нижележащий узел
    function selectNodeDown() {
      var
        $linkNodesVisible = $('.quiTreeNode:visible'),
        index = $linkNodesVisible.index($linkNodeSelected);
      if (index < $linkNodesVisible.length - 1) {
        selectNode($linkNodesVisible.eq(++index).find('.quiLabelTree:first'));
      }
    }

    // Выделить нижележащий узел
    function selectNodeParent() {
      var $liParentNode = $linkNodeSelected.parent().parentsUntil('#'+options.id+'Container', 'li:first');
      if ($liParentNode.length > 0) selectNode($liParentNode.find('.quiLabelTree:first'));
    }

    // Парсинг элементов дерева
    function parseItems(items) {
      var outPut = '';
      $.each(items, function(i,item){
        var child = '';
        if (item.items == 'ajax'){
          child = '<ul class="ajax" url="'+item.url+'"></ul>';
          clasT = 'folder';
        }else if(item.items != undefined){
          child = '<ul>'+parseItems(item.items)+'<li class="quiLineTree"></li></ul>';
          clasT = 'folder';
        }else{
          clasT = 'file';
          child = '';
        }
        outPut +=  '<li class="quiLineTree"></li>';
        outPut += '<li id="'+options.id+'Item'+item.id+'" class="'+clasT+'">';
        outPut += '<div class="quiTreeNode"><div class="quiArrowTree"></div><div class="quiIconTree"><span class="quiLabelTree">'+item.label+'</span></div></div>';
        outPut += child;
        outPut += '</li>';
      });
      outPut += '<li class="quiLineTree"></li>';
      return outPut;
    }

    // Конец перемещения узла
    function endDraggingNode(isSuccessDragging) {
      isDragged = false;
      var $liNode = $dragLinkNode.parent();

      // Убираем классы отмечающие "неактивность" перетаскиваемого элемента
      $liNode.next('.quiLineTree').removeClass('quiDisabled');
      $liNode.prev('.quiLineTree').removeClass('quiDisabled');

      $draggedCloneNode.remove();
      $draggedCloneNode = $();

      // Убираем прозрачность у перетаскиваемого элемента
      $liNode.removeClass('quiDisabled');
      treeDelClass($liNode, 'quiDisabled'); // TODO: что это за функция?

      // Если перетаскивание закончилось на элементе дерева, то производим перемещение
      // В противном случае ничего не делаем
      if (status) {
        if ($liTargetDrag.hasClass('quiLineTree')) {
          // Если целевой узел - "разделитель", убераем с него подсветку
          $liTargetDrag.removeClass('quiLineTreehover');
        }

        if (isSuccessDragging === false) return false;

        // TODO: если узел назначения - "закрытая папка", нужно поверить и загрузить дочерние узлы

        // Скрываем перетаскиваемый элемент
        $liNode.slideUp('fast', function() {
          var
            $targetUl,
            $separatorNode = $liNode.next('.quiLineTree'); // разделитель следующий за перемещамым узлом тоже надо переместить

          // Если прошлый родительский узел - открытая папка
          if ($liNode.parent().parent().hasClass('folder-open')){
            // проверяем наличие других дочерних узлов
            hasAdjacentNodes($liNode);
          }

          if ($liTargetDrag.hasClass('file')) {
            // Если целевой узел - "Файл", делаем его открытой папкой
            $liTargetDrag.removeClass('file').addClass('folder-open').append($targetUl = $('<ul></ul>'));
            $targetUl.append($liNode).append($separatorNode);

          } else if ($liTargetDrag.hasClass('folder')) {
            // Если целевой узел - "закрытая папка", делаем его открытой папкой
            $targetUl = $liTargetDrag.removeClass('folder').addClass('folder-open').children('ul').slideDown();
            $targetUl.append($liNode).append($separatorNode);

          } else if ($liTargetDrag.hasClass('folder-open')) {
            // Если целевой узел - "открытая папка"
            $targetUl = $liTargetDrag.children('ul');
            $targetUl.append($liNode).append($separatorNode);

          } else if ($liTargetDrag.hasClass('quiLineTree')) {
            // Если целевой узел - "разделитель"
            $liTargetDrag.after($separatorNode).after($liNode);
          }

          options.endDrag($liNode.slideDown('fast'));
          parseTree($liTargetDrag);
        });
      } else {
        status = '';
      }
    }




    // Отрисовка дерева
    this.draw = function(elem) {
      $tree = $(elem);
      $tree.append(
        '<a tabindex="1" id="'+options.id+'Container" class="quiTreeContainer">' +
          '<header class="quiTreeHead"><div id="'+options.id+'RootLabel" class="quiTreeRL">' +
          '<div class="quiTreenodeName">Корень</div>' +
          '<div class="quiTreeBTN"><div class="quiTreeExitF"></div><div class="quiTreeAddN"></div></div></div>' +
          '</header><section id="'+options.id+'" class="quiTree"></section></a>'
      );

      if (options.contextMenu) {
        var
          cmItems = {},
          cmItemKey = options.contextMenu.length - 1;

        for (; cmItemKey >= 0; cmItemKey--) {
          cmItems[cmItemKey] = {
            name: options.contextMenu[cmItemKey].name
          }
        }
        delete cmItemKey;

        $.contextMenu({
          selector: '.quiTreeNode',
          items: cmItems,
          callback: function(cmItemKey, opt) {
            var $liNode = opt.$trigger.parent();
            options.contextMenu[cmItemKey].action({
              text: $liNode.text(),
              id: $liNode.attr('id').replace('derevoItem', '')
            });
          },
          events: {
            show: function(opt) {
              opt.$trigger.addClass('hover');
              isShowedCM = true;
            },
            hide: function(opt) {
              opt.$trigger.removeClass('hover');
              isShowedCM = false;
            }
          }
        });

        $('.quiTreeContainer').on('contextmenu', function(e) {
//          console.log(e);
//          if (!isShowedCM) {
//            isShowedCM = true;
//            e.preventDefault();
//            $linkNodeSelected.contextMenu();
//            return false;
//          }
        });
      }

      /* Блок описания реакций НАЧАЛО */

      // Фокус дерева
      $('.quiTreeContainer')
        .bind('focus', function(e) {
          e.preventDefault();
          $(this).addClass('focus');
          inFocus = true;
        })
        .bind('blur', function() {
          inFocus = false;
          setTimeout(function() {
            if (inFocus === false) $('.quiTreeContainer').removeClass('focus');
          }, 90);
        });

      $tree.on('click', '.quiTreeContainer *', function() {
        if (inFocus === false) $('.quiTreeContainer').focus();
      });

      // Реакция на нажатие кнопок
      $('.quiTreeContainer').bind('keydown', function(e) {
//        console.log('tree > keydown');

        if (isShowedCM) {
          if (e.keyCode == 93) { // Select key
            isShowedCM = false;
          }
          return true;
        }

        if (e.keyCode == 38) { // Up
          selectNodeUp();
        }

        if (e.keyCode == 40) { // Down
          selectNodeDown();
        }

        if (e.keyCode == 39) { // Right
          var $liNodeSelected = $linkNodeSelected.parent();

          if ($liNodeSelected.hasClass('folder-open')) {
            // Если узел - открытая папка, то выделяем следующий узел
            // TODO: возможно лушче виделять только первый дочерний, если он есть, а если нет, то ничего не делать
            selectNodeDown();

          } else {
            // Если узел - закрытая папка, то откраваем её
            openNodeAndPreload($linkNodeSelected);
          }
        }

        if (e.keyCode == 37) { // Left
          if ($linkNodeSelected.parent().hasClass('folder-open')) {
            // Если узел - открытая папка, то закрываем её
            closeNode($linkNodeSelected);
          } else {
            // Если узел - закрытая папка, то выделяем родельский узел
            selectNodeParent();
          }
        }

        if (e.keyCode == 13 || e.keyCode == 32) { // Enter || Space
          // Открытие контента узла
          options.dblClick($linkNodeSelected.parent());
        }

        if (e.keyCode == 46) { // Delete
          // Удаление узла
          $linkNodeSelected.trigger('removeTreeNode');
        }

        if (e.keyCode == 27) { // Escape
          // Если происходит перемещение узла, останавливаем этот процесс
          if (isDragged) {
            endDraggingNode(false);
          }
        }
      });


      $tree
        // arrow node
        .on('click', '.quiArrowTree', function() {
          // Нажатие на стрелку
          var $linkNode = $(this).parent();
          if($linkNode.parent('li').hasClass('folder-open')){
            // Если узел - открытая папка, то закрываем её
            closeNode($linkNode);
          } else {
            // Если узел - закрытая папка, то откраваем её
            openNodeAndPreload($linkNode);
          }
          return false;
        })

        // icon node
        .on('dblclick', '.quiIconTree', function() {
          // Реакция на двойной клик по элементу дерева
          options.dblClick($(this).parent().parent());
          selectNode($(this).children('.quiLabelTree'));
        })
        .on('mouseover', '.quiIconTree', function() {
          // Реакция на наведение на имя узла
          var
            $node = $(this).children('.quiLabelTree').addClass('hover'),
            $linkNode = $node.parent().parent(),
            $liNode = $linkNode.parent();

          // Если в данный момент происходит перетаскивание
          if (isDragged && !$liNode.hasClass('quiDisabled') && !$liNode.hasClass('quiDisabledChild')) {
            // TODO: как используются quiDisabled и quiDisabledChild ?
            status = $node.text();
            $liTargetDrag = $liNode;

            if ($liNode.hasClass('folder')) {
              // Если целевой узел - закрытая папка, то при удержании, раскрыть узел
              $node.oneTime(600, 'openNode', function() {
                openNodeAndPreload($linkNode);
              });
            }
          }
        })
        .on('mouseout', '.quiIconTree', function() {
          // Отведение мыши от имени узла
          var
            $node = $(this).children('.quiLabelTree').removeClass('hover'),
            $linkNode = $node.parent().parent(),
            $liNode = $linkNode.parent();

          if (isDragged) {
            status = '';
            $liTargetDrag = '';
            // Если узел - "закрытая папка", останавливаем таймер открытия
            if ($liNode.hasClass('folder')) $node.stopTime('openNode');
          } else {
            // если увели перетаскивание ещё не началось, останавливаем таймер старта перетаскивания
            $(document).stopTime('startDrag');
          }
        })
        .on('click', '.quiIconTree', function() {
          // Нажатие на имя узла
          selectNode($(this).children('.quiLabelTree'));
        })
        .on('mousedown', '.quiIconTree', function(e) {
          selectNode($(this).children('.quiLabelTree'));

          if (e.button != '2') {
            // Если нажата не правая кнопка мыши

            $dragNode = $(this).children('.quiLabelTree');
            $dragLinkNode = $dragNode.parent().parent();
            var $liNode = $dragLinkNode.parent();

            // После 300 милесекунд начинаем перетаскивание
            $(document).oneTime(300, 'startDrag', function() {
              // Старт перетаскивания
              isDragged = true;

              // Определяем класс перетаскиваемого элемента
              id = $liNode.attr('id');
              clas = $liNode.attr('class');
              // Затеняем перетаскиваемый элемент
              $liNode.addClass('quiDisabled');
              treeSetClass($liNode, 'quiDisabled');
              $liNode.next('.quiLineTree').addClass('quiDisabled');
              $liNode.prev('.quiLineTree').addClass('quiDisabled');
              // Если класс Открытая папка - ставим закрытая папка
              if (clas == 'folder-open') clas = "folder";

              // Создаём таскаемый аналог перетаскиваемого элемента
              // $dragLinkNode
              $draggedCloneNode = $(options.tplDraggedCloneNode({id: 1000000, label: '123'}));
              $draggedCloneNode
                .css({
                  top: e.pageY + 20,
                  left: e.pageX + 20
                })
                .appendTo(document.body);
            });
          }
        })

        // node separator
        .on('mouseover', '.quiLineTree', function() {
          // Реакция на наведение мыши на Линию разделителя
          var $line = $(this);
          // Если активно перетаскивание
          if (isDragged && !$line.hasClass('quiDisabled') && !$line.hasClass('quiDisabledChild')) {
            status = 'ReplaceLine';
            $liTargetDrag = $line;
            // Подсвечиваем линию
            $line.addClass('quiLineTreehover');
          }
        })
        .on('mouseout', '.quiLineTree', function() {
          // Реакция на отведение мыши от Линии Разделителя
          // Убираем подсветку с линии
          $(this).removeClass('quiLineTreehover');
          // Очищаем статус
          status = '';
        });


      $(document)
        .mousemove(function(e) {
          if (isDragged) {
            // Реализация перетаскивания узла
            // Подводим таскаемый клон узла под мышку
            $draggedCloneNode.css({
              top: e.pageY + 20,
              left: e.pageX + 20
            });
          }
        })
        .mouseup(function() {
          if (isDragged) {
            endDraggingNode();
          } else {
            // Останавливаем таймер "Перетаскивание"
            $(document).stopTime('startDrag');
          }
        });

      // TODO: пересмотреть эти функции
//      $('.quiTreeExitF').live('click',function(){
//        options.exit();
//      });
//      $('.quiTreeAddN').live('click',function(){
//        options.addRoot();
//      });

      // отменяем браузерное выделение (текста и др. объектов)
      $tree.bind('selectstart', function(e) {
        e.preventDefault();
        return false;
      });
      $(document).bind('selectstart', function(e) {
        if (isDragged) {
          e.preventDefault();
          return false;
        }
      });

      this.loadContent();
    }


    // Загрузка узлов дерева
    this.loadContent = function() {
      var content = '<ul>';
      // Определяем источник контента и запускаем парсинг
      if (options.items != '') {
        var items = options.items;
        content += parseItems(items);
        //			content += '<li class="quiLineTree"></li></ul>';
        $('#' + options.id).append(content);
        parseTree($('#' + options.id));
        selectNode($('.quiLabelTree:eq(0)'));

      } else {
        $.getJSON(options.url, function(json) {
          var items = json;
          content += parseItems(items);
          $('#' + options.id).append(content);
          parseTree($('#' + options.id));
          selectNode($('.quiLabelTree:eq(0)'));
        });
      }
    };

    // Функция определения позиции элемента
    this.getNodePosition = function() {
      node=this.getSelected();
      var id=node.attr('id');
      var counter=0;
      var position=0;
      $('#'+id).parent('ul').children('li').each(function(){
        if (!$(this).hasClass('quiLineTree')){
          counter += 1;
          if (id == $(this).attr('id')){
            position = counter;
          }
        };
      });
      return position;
    };

    // Определение родителя выделенного элемента
    this.getNodeParent = function() {
      node = this.getSelected();
      id = node.attr('id');
      parentNode = $('#'+id).parent().parent().attr('id');
      if (parentNode != undefined){
        return parentNode;
      }else{
        return 'root';
      }
    };

    // Функция добавления узла дерева
    this.addNode = function($liParentNode, id, label) {
      // TODO: Нужно добавить учитывание позиций и возможность добавлять несколько дочерних элементов
      // TODO: Если дочерние элементы не были загружены, возможно стоит их загружать
      // TODO: Анимация  раскрытия нового узла должна быть такой же как при перемещении
      if ($.type($liParentNode) !== 'object') {
        $liParentNode = $('#' + options.id + ($liParentNode == 'root'? '': 'Item' + $liParentNode));
      }

      var tplNode = '<li id="' + options.id + 'Item' + id + '" class="file">' +
        '<div class="quiTreeNode"><div class="quiArrowTree"></div><div class="quiIconTree">' +
        '<span class="quiLabelTree">' + label + '</span></div></div></li><li class="quiLineTree"></li>';

      if ($liParentNode.hasClass('file')) {
        $liParentNode
          .removeClass('file').addClass('folder-open')
          .append('<ul><li class="quiLineTree"></li>' + tplNode + '</ul>');
      } else {
        $liParentNode.children('ul').append(tplNode);
      }
      parseTree($liParentNode);
    };

    /*
     * Удалить узел дерева
     * $liNode - $liNode or Node Id
     */
    this.delNode = function($liNode) {
      if ($.type($liNode) !== 'object') $liNode = $('#' + options.id + 'Item' + $liNode);

      var $linkNode = $liNode.children('.quiTreeNode');
      if ($linkNode[0] == $linkNodeSelected[0]) {
        // если данный узел выделенный, выделяем следующий узел
        // TODO: надо улучшить схему выделения другого узла
        // TODO: если удалены все елементы не должно возникать ошибок
        var
          nodesVisible = $('.quiTreeNode:visible'),
          index = nodesVisible.index($linkNodeSelected);
        if (index < nodesVisible.length - 1) {
          index++;
        } else {
          index--;
        }
        selectNode(nodesVisible.eq(index).find('.quiLabelTree'));
      }

      hasAdjacentNodes($liNode);
      $liNode.next('.quiLineTree').remove();
      $liNode.remove();
    };

    // Функция обновления названия узла дерева
    this.updateNode = function(id,label) {
      $('#'+options.id+'Item'+id).children('.quiTreeNode').children('.quiIconTree').children('.quiLabelTree').text(label);
    };

    // Получить выделенный узел дерева
    this.getSelected = function() {
      return $linkNodeSelected.parent();
    };

    this.setNewRoot = function(url,id,name){
      $('#'+options.id+'RootLabel').children('.quiTreenodeName').html(name);
      options.root = id;
      options.url = url;
      $('#'+options.id).html('');
      this.loadContent();
    };

    this.getRoot = function(){
      return options.root;
    }

  }

})(jQuery);