<div class="row">
  <div class="span3 tree-container">
    <div class="well tree" id="model_tree" style="padding: 8px 0; height: 500px; overflow: hidden; overflow-y: scroll;">
    </div>
    <script>$.model_tree();</script>
    <div class="searchContainer">
      <div class="search">
        <form class="form-horizontal">
          <fieldset>
              <div class="input-append">
                <input class="span2" id="appendedPrependedInput" size="16" type="text"><button class="btn" type="button"><i class="icon-search"></i></button>
              </div>
          </fieldset>
        </form>
      </div>
    </div>
    <div class="buttons">
      <button class="btn btn-success" id="create-root"><i class="icon-plus icon-white"></i> Добавить</button>
      <button class="btn" id="search-button"><i class="icon-search"></i> Поиск</button>
    </div>
  </div>
  <div class="span9" id="model-content">
  </div>
</div>

<!-- Modals -->
<div class="modal hide fade" id="attrModal">
  <div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h3>Добавить атрибут</h3>
  </div>
  <div class="modal-body">
    <form class="form-horizontal edit-form">
      <fieldset>
        <div class="form-content">
          <div class="control-group">
              <label class="control-label" for="model-add-title">
                <span class="fieldLabel">Название</span>
              </label>
            <div class="controls">
              <input type="text" id="model-add-title" />
            </div>
          </div>

          <div class="control-group">
              <label class="control-label" for="model-add-name">
                <span class="fieldLabel">Имя для вызова</span>
              </label>
            <div class="controls">
              <input type="text" id="model-add-name" />
            </div>
          </div>

          <div class="control-group">
              <label class="control-label" for="model-add-attr">
                <span class="fieldLabel">Компонент</span>
              </label>
            <div class="controls">
              <select id="model-add-attr">
                <option value="THidden">Hidden</option>
                <option value="TBoolev">Boolev</option>
                <option value="TImage">Image</option>
                <option value="TSelect">Select</option>
                <option value="TVarchar">Varchar</option>
                <option value="TInteger">Integer</option>
                <option value="TFloat">Float</option>
                <option value="TNFiles">NFiles</option>
                <option value="TMultiUpload">MultiUpload</option>
                <option value="TText">Text</option>
                <option value="TDate">Date</option>
                <option value="TFiles">Files</option>
              </select>
            </div>
          </div>
        </div>
      </fieldset>
    </form>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn" id="model-add-attr-cancel">Отменить</a>
    <a href="#" class="btn btn-primary" id="model-add-attr-submit">Добавить</a>
  </div>
</div>

<div class="modal hide fade" id="childModal">
  <div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h3>Добавить дочерний элемент</h3>
  </div>
  <div class="modal-body">
    <form class="form-horizontal edit-form">
      <fieldset>
        <div class="form-content">
          <div class="control-group">
              <label class="control-label" for="model-add-child">
                <span class="fieldLabel" rel="tooltip" data-original-title="%attr(active)%">Название</span>
              </label>
            <div class="controls">
              <select id="model-add-child">
              </select>
            </div>
          </div>
        </div>
      </fieldset>
    </form>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn" id="model-add-child-cancel">Отменить</a>
    <a href="#" class="btn btn-primary" id="model-add-child-submit">Добавить</a>
  </div>
</div>

<div class="modal hide fade" id="TTextSettings">
  <div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h3>Настройки компонента TText</h3>
  </div>
  <div class="modal-body">
    <form class="form-horizontal edit-form">
      <fieldset>
        <div class="form-content">
          <div class="control-group">
              <label class="control-label" for="model-ttext-type">
                <span class="fieldLabel">Вывод компонента</span>
              </label>
            <div class="controls">
              <select id="model-ttext-type">
                <option value="0">WYSIWYG редактор</option>
                <option value="1">Простой редактор текста</option>
                <option value="2">Редактор кода</option>
              </select>
            </div>
          </div>
        </div>
      </fieldset>
    </form>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn close-dialog" id="model-ttext-settings-cancel">Отменить</a>
    <a href="#" class="btn btn-primary" id="model-ttext-settings-submit">Сохранить</a>
  </div>
</div>

<div class="modal hide fade" id="TSelectSettings">
  <div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h3>Настройки компонента TSelect</h3>
  </div>
  <div class="modal-body">
    <table class="table table-striped">
      <thead>
        <th>Видимое значение</th>
        <th>Системное значение</th>
        <th></th>
      </thead>
      <tbody id="model-tselect-values">
      </tbody>
      <tfooter>
        <td><input type="text" id="model-tselect-visible" class="tselect-settings" /></td>
        <td><input type="text" id="model-tselect-system" class="tselect-settings" /></td>
        <td><i class="icon-plus control"></i></td>
      </tfooter>
    </table>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn close-dialog" id="model-tselect-settings-cancel">Закрыть</a>
  </div>
</div>

<div class="modal hide fade" id="THiddenSettings">
  <div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h3>Настройки компонента THidden</h3>
  </div>
  <div class="modal-body">
    <form class="form-horizontal edit-form">
      <fieldset>
        <div class="form-content">
          <div class="control-group">
            <label class="control-label" for="model-thidden-default" style="width: 150px;">
              <span class="fieldLabel">Значение по умолчанию</span>
            </label>
            <div class="controls">
              <input type="text" id="model-thidden-default">
            </div>
          </div>
        </div>
      </fieldset>
    </form>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn close-dialog" id="model-thidden-settings-cancel">Отменить</a>
    <a href="#" class="btn btn-primary" id="model-thidden-settings-submit">Сохранить</a>
  </div>
</div>

<div class="modal hide fade" id="TImageSettings">
  <div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h3>Настройки компонента TImage</h3>
  </div>
  <div class="modal-body">
    <form class="form-horizontal edit-form">
      <fieldset>
        <div class="form-content">
          <div class="control-group">
              <label class="control-label" for="model-ttext-type">
                <span class="fieldLabel">Вывод компонента</span>
              </label>
            <div class="controls">
              <select>
                <option value="0">WYSIWYG редактор</option>
                <option value="1">Простой редактор текста</option>
                <option value="2">Редактор кода</option>
              </select>
            </div>
          </div>
        </div>
      </fieldset>
    </form>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn close-dialog" id="model-timage-settings-cancel">Отменить</a>
    <a href="#" class="btn btn-primary" id="model-timage-settings-submit">Сохранить</a>
  </div>
</div>
<!-- /Modals -->

<script>
  $('#model_tree').css('height', $(window).height()-130);
  $('#model-zone').css('height', $(window).height()-168);
  $(window).bind('resize', function() {
    $('#model_tree').css('height', $(window).height()-130);
    $('#model-zone').css('height', $(window).height()-168);
  });
</script>