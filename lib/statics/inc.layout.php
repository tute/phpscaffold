<?php
function print_header($title) {
	$login = preg_match('/inc.auth.php/', $_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?= $title ?></title>
<link rel="stylesheet" type="text/css" href="<?= $login ? '' : '../' ?>css/stylesheet.css" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
</head>

<body>
<h1><a href="../"><?= $title ?></a>
  <? if (isset($_SESSION) && $_SESSION['user_logged_in'] == true) echo '<span style="font-size:12px"><a href="../inc.auth.php?action=logout&amp;msg=You have been logged out.">[Logout]</a></p>'; ?></h1>
<?
}

function print_footer() {
	$index = preg_match('/index.php/', $_SERVER['PHP_SELF']);
	$login = preg_match('/inc.auth.php/', $_SERVER['PHP_SELF']);
	echo list_cruds();
	if (!$index and !$login)
		echo '<p><a href="index.php">Back to Listing</a></p>';
	echo "</body>\n</html>";
}
?>
