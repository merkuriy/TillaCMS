<?php header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500); ?>
<h1>This site is temporarily not working</h1>
<p>index.php</p>
<?php
if (isset($_GET['info'])) phpinfo();
die('php work');