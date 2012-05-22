<form class   = "form-horizontal edit-form"
      enctype = "multipart/form-data"
      method  = "post"
      action  = "/panel/structure?action=editElementSCR&amp;author=admin">
  <fieldset>
    <input name="id" type="hidden" value="${id}" />
    <legend>
      ${title}
      <div class="info">
        <strong>URI:</strong> <a href="#">${name}</a>,
        <strong>ID:</strong> ${id},
        <strong>MODEL:</strong> <a href="#">${base_class.title}</a>
      </div>
    </legend>
    <div class="form-content" id="content-zone">
      <div class="row">
        <div class="control-group span4-5">
          <label class="control-label" for="title">
            <span class="fieldLabel" rel="tooltip" data-original-title="%attr(title)%">Название</span>
          </label>
          <div class="controls">
            <input type="text" class="span2-5" id="title" value="${title}">
          </div>
        </div>
        <div class="control-group span4-5">
          <label class="control-label" for="name">
            <span class="fieldLabel" rel="tooltip" data-original-title="%attr(_url)%">Адрес</span>
          </label>
          <div class="controls">
            <input type="text" class="span2-5" id="name" value="${name}">
          </div>
        </div>
      </div>
      <div id="components">
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" id="content-save" class="btn btn-primary">Сохранить</button>
      <button class="btn" id="content-cancel">Отменить</button>
    </div>
  </fieldset>
</form>