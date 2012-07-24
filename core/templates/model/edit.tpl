<form class   = "form-horizontal edit-form"
      enctype = "multipart/form-data"
      method  = "post"
      action  = "/panel/structure?action=editElementSCR&amp;author=admin">
  <fieldset>
    <input name="id" type="hidden" value="${id}" id="model-id" />
    <legend>
      ${title}
      <div class="info">
        <strong>ID:</strong> ${id}
      </div>
    </legend>
    <div class="form-content" id="model-zone">
      <div class="row">
        <div class="control-group span4-5">
          <label class="control-label" for="title">
            <span class="fieldLabel">Название</span>
          </label>
          <div class="controls">
            <input type="text" class="span2-5" id="title" name="title" value="${title}">
          </div>
        </div>
        <div class="control-group span4-5">
          <label class="control-label" for="name">
            <span class="fieldLabel">Имя для вызова</span>
          </label>
          <div class="controls">
            <input type="text" class="span2-5" id="name" value="${name}" name="name">
          </div>
        </div>
      </div>

      <div class="row">
        <div class="span4-5">
          <table class="table table-striped table380px">
            <thead>
              <tr>
                <th colspan="3">
                  Дочерние элементы
                  <button class="btn btn-mini float-right btn-success" data-toggle="modal" href="#childModal" id="model-child-modal">
                    <i class="icon-plus icon-white"></i>
                  </button>
                </th>
              </tr>
            </thead>
            <tbody id="model-childs">
            </tbody>
          </table>
        </div>
        <div class="span4-5">
          <table class="table table-striped table380px">
            <thead>
              <tr>
                <th colspan="4">
                  Аттрибуты
                  <button class="btn btn-mini float-right btn-success" data-toggle="modal" href="#attrModal" id="model-attr-modal">
                    <i class="icon-plus icon-white"></i>
                  </button>
                </th>
              </tr>
            </thead>
            <tbody id="model-attrs">
            </tbody>
          </table>
        </div>
      </div>

    </div>
    <div class="form-actions">
      <button type="submit" id="model-save" class="btn btn-primary">Сохранить</button>
      <button class="btn" id="model-cancel">Отменить</button>
    </div>
  </fieldset>
</form>