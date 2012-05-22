<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" dir="ltr">
<head>  
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />  
	<title>QPanel CMS - установка</title>  
</head>
<body>
	<div>
		<form method="post" action="/panel">
			<div>
				<b>Шаг 2: Создание учетной записи администратора</b><br />
				<input type="hidden" name="parametr" value="step4" />
				<input type="hidden" name="action" value="createCONF" />
				<input type="hidden" name="parent_id" value="%parent_id%" />
				<input type="hidden" name="baseclass" value="%baseclass%" />
				Login: <input type="text" name="login" /><br />
				Password: <input type="text" name="password" /><br />
				<input type="submit" value="Далее" />
			</div>
		</form>
	</div>
</body>
</html>