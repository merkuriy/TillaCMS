/*
 * Module: Structure
 */

window.StructureTree || (StructureTree = (function() {

  var derevo;

  return {
    init: init,
    addNode: addNode,
    removeNode: removeNode
  };


  function init() {
    derevo = new $.tree({
      id: 'derevo',
      stype: 'tree',
      tplDraggedCloneNode: function(node) {
        return '<div class="label tree-dragged-clone-node"><i class="icon-file"></i>'
          + node.label + ' (id:' + node.id + ')</div>';
      },
      addRoot: function() {
        console.log('d>addRoot');
      },
      exit: function() {
        console.log('d>exit');
      },
      dblClick:	showEditSectionPage,
      endDrag: function($liNode) {
        $alert('Элемент перемещён', 'success');

        idNode = $liNode.attr('id').replace('derevoItem', '');
        newPosition = derevo.getNodePosition()-1;
        newParent = derevo.getNodeParent().replace('derevoItem', '');
        if (newParent == 'root') newParent = derevo.getRoot();
        $.ajax({
          type: 'GET',
          url: '../core/admin.php',
          data: 'module=structure&action=updatePosition&id=' + idNode + '&parent=' + newParent + '&pos=' + newPosition,
          error: function(msg) {
            // TODO: Нужно как то обрбатывать ошибку
          }
        });
      },
      contextMenu:
        [{
          clas:	'addCM',
          name:	'Добавить',
          action:	function(parentNode) {
            $createContent(parentNode.id);
          }
        },{
          clas:	'editCM',
          name:	'Редактировать',
          action:	function(node) {
            Structure.showSection(node.id);
          }
        },{
          clas:	'deleteCM',
          name:	'Удалить',
          action:	function(node) {
            Structure.removeSection(node);
          }
        }],
      url: '?module=structure&action=findChild&id=0&author=admin'
    });

    var $derevo = $('#tree');
    $derevo.on('removeTreeNode', function() {
      var $liNode = derevo.getSelected();
      Structure.removeSection({
        label: $liNode.text(),
        id: $liNode.attr('id').replace('derevoItem', '')
      })
    });
    derevo.draw($derevo);
  }

  function showEditSectionPage() {
    Structure.showSection(derevo.getSelected().attr('id').replace('derevoItem', ''));
  }

  function addNode($liParentNode, id, label) {
    derevo.addNode($liParentNode, id, label);
  }

  function removeNode($liNode) {
    derevo.delNode($liNode);
  }

})());