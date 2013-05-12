<div class="row">
  <div class="span3 tree-container">
    <div class="well tree" id="tree"></div>
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
  <div class="span9" id="node-content">
  </div>
</div>

<div id="components-template">

  <div id="TText0">
    <div class="control-group">
      <label class="control-label" for="field-${name}">
        <span class="fieldLabel" rel="tooltip" data-original-title="%attr(${name})%">${title}</span>
      </label>
      <div class="controls">
        <textarea class="span7 tinymce" id="field-${name}" name="${name}" rows="6">${value}</textarea>
      </div>
    </div>
  </div>

  <div id="TText1">
    <div class="control-group">
      <label class="control-label" for="field-${name}">
        <span class="fieldLabel" rel="tooltip" data-original-title="%attr(${name})%">${title}</span>
      </label>
      <div class="controls">
        <textarea class="span7" id="field-${name}" name="${name}" rows="3">${value}</textarea>
      </div>
    </div>
  </div>

  <div id="TText2">
    <div class="control-group">
      <label class="control-label" for="field-${name}">
        <span class="fieldLabel" rel="tooltip" data-original-title="%attr(${name})%">${title}</span>
      </label>
      <div class="controls">
        <textarea class="span7 code" id="field-${name}" name="${name}" rows="3">${value}</textarea>
      </div>
    </div>
  </div>

  <div id="TText3">
    <div class="control-group">
      <label class="control-label" for="field-${name}">
        <span class="fieldLabel" rel="tooltip" data-original-title="%attr(${name})%">${title}</span>
      </label>
      <div class="controls">
        <textarea class="span7 tinymce" id="field-${name}" name="${name}" rows="6">${value}</textarea>
      </div>
    </div>
  </div>

  <div id="TVarchar">
    <div class="control-group">
      <label class="control-label" for="field-${name}">
        <span class="fieldLabel" rel="tooltip" data-original-title="%attr(${name})%">${title}</span>
      </label>
      <div class="controls">
        <input class="span7" id="field-${name}" name="${name}" value="${value}" />
      </div>
    </div>
  </div>

  <div id="TSelect">
    <div class="control-group">
      <label class="control-label" for="field-${name}">
        <span class="fieldLabel" rel="tooltip" data-original-title="%attr(${name})%">${title}</span>
      </label>
      <div class="controls">
        <select class="span7" id="field-${name}" name="${name}" data-value="${value}">
          {{each options}}
            <option value="${name}">${title}</option>
          {{/each}}
        </select>
      </div>
    </div>
  </div>

  <div id="TBoolev">
    <div class="control-group">
      <label class="control-label" for="field-${name}">
        <span class="fieldLabel" rel="tooltip" data-original-title="%attr(${name})%">${title}</span>
      </label>
      <div class="controls tboolev">
        <div class="btn-group" data-toggle="buttons-radio" id="field-${name}" data-name="${name}">
          <button class="btn" data-value="true">Включено</button>
          <button class="btn" data-value="false">Выключено</button>
        </div>
        <input type="hidden" name="${name}" value="${value}" />
      </div>
    </div>
  </div>

  <div id="TImage">
    <div class="control-group">
      <label class="control-label" for="field-${name}">
        <span class="fieldLabel" rel="tooltip" data-original-title="%attr(${name})%">${title}</span>
      </label>
      <div class="controls">
        <img src="${value}?v=1" class="image-preview" /> <i class="icon-remove remove-image" title="Удалить изображение"></i>
        <input type="file" class="span6 image-input" id="field-${name}" name="${name}" />
      </div>
    </div>
  </div>

  <div id="TFiles">
    <div class="control-group">
      <label class="control-label" for="field-${name}">
        <span class="fieldLabel" rel="tooltip" data-original-title="%attr(${name})%">${title}</span>
      </label>
      <div class="controls">
        <input type="file" class="span7" id="field-${name}" name="${name}" />
      </div>
    </div>
  </div>

  <div id="TFloat">
    <div class="control-group">
      <label class="control-label" for="field-${name}">
        <span class="fieldLabel" rel="tooltip" data-original-title="%attr(${name})%">${title}</span>
      </label>
      <div class="controls">
        <input class="span7" id="field-${name}" name="${name}" value="${value}" />
      </div>
    </div>
  </div>

  <div id="TInteger">
    <div class="control-group">
      <label class="control-label" for="field-${name}">
        <span class="fieldLabel" rel="tooltip" data-original-title="%attr(${name})%">${title}</span>
      </label>
      <div class="controls">
        <input class="span7" id="field-${name}" name="${name}" value="${value}" />
      </div>
    </div>
  </div>

  <div id="THidden">
    <div class="control-group">
      <label class="control-label" for="field-${name}">
        <span class="fieldLabel" rel="tooltip" data-original-title="%attr(${name})%">${title}</span>
      </label>
      <div class="controls">
        <input class="span7" id="field-${name}" name="${name}" value="${value}" />
      </div>
    </div>
  </div>

  <div id="TDate">
    <div class="control-group">
      <label class="control-label" for="field-${name}">
        <span class="fieldLabel" rel="tooltip" data-original-title="%attr(${name})%">${title}</span>
      </label>
      <div class="controls">
        <input class="span7 datepicker" id="field-${name}" name="${name}" value="${value}" />
      </div>
    </div>
  </div>

  <div id="TMultiUpLoad">
    <div class="control-group">
      <label class="control-label" for="field-${name}">
        <span class="fieldLabel" rel="tooltip" data-original-title="%attr(${name})%">${title}</span>
      </label>
      <div class="controls plupload-container">
        <div id="field-${name}">
          <p>You browser doesn't have Flash, Silverlight, Gears, BrowserPlus or HTML5 support.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="content-create-modal">
  <div class="modal-header">
    <button class="close" data-dismiss="modal">×</button>
    <h3>Добавить элемент</h3>
  </div>
  <div class="modal-body">
    <form class = "form-horizontal edit-form">
      <input id="content-id" type="hidden" />
      <div class="control-group">
        <label class="control-label" for="content-type">
          Виберите модель
        </label>
        <div class="controls">
          <select id="content-type">
          </select>
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="content-name">
          Введите название
        </label>
        <div class="controls">
          <input id="content-name" />
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="content-uri">
          Введите адрес
        </label>
        <div class="controls">
          <input id="content-uri">
        </div>
      </div>
    </form>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn" id="cancel-content-btn">Отменить</a>
    <a href="#" class="btn btn-primary" id="create-content-btn">Создать</a>
  </div>
</div>

<script src="/core/templates/js/structure-tree.js"></script>
<script src="/core/templates/js/structure.js"></script>