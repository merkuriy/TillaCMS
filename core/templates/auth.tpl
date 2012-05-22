<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8">
    <title>TillaCMS - Авторизация</title>

    <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le styles -->
    <link href="/core/templates/css/bootstrap.css" rel="stylesheet">
    <link href="/core/templates/css/bootstrap-responsive.css" rel="stylesheet">
    <link href="/core/templates/css/style.css" rel="stylesheet">

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">
  </head>
<body>
  
  <div class="container">
    <div class="row">
      <div class="span4 offset4">
        <form class="form-horizontal" method="post" action="/api.post/users_sys.auth" id="auth-form">
          <fieldset>
            <legend>
              TillaCMS
            </legend>
            <input type="text" name="user_login" placeholder="Логин" />
            <input type="password" name="user_password" placeholder="Пароль" />
            <button class="btn btn-primary" type="submit">Войти</button>
            <button class="btn" type="reset">Отмена</button>
            <hr />
          </fieldset>
        </form>
      </div>
    </div>
  </div>
  
</body>
</html>