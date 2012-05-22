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
				<b>Шаг 1: Создание файла конфигурации</b><br />
				<input type="hidden" name="parametr" value="step1" />
				DB_name: <input type="text" name="db_name" /><br />
				DB_host: <input type="text" name="db_host" /><br />
				DB_login: <input type="text" name="db_login" /><br />
				DB_password: <input type="text" name="db_password" /><br />
				DB_prefix: <input type="text" name="db_prefix" /><br />
				<input type="submit" value="Далее" />
			</div>
		</form>
	</div>
</body>
</html>