<?php
error_reporting(E_ALL);
include('scaffold.php');
include('functions.inc');

$show_form = 0;
$message = '';

if (isset($_POST['scaffold_info'])) {
	$data = trim($_POST['sql']);
	$data_lines = explode("\n", $data);
	
	/* Strip SQL comments */
	foreach ($data_lines as $key => $value) {
		$value = trim($value);
		if ($value[0] == '-' && $value[1] == '-') unset($data_lines[$key]);
		elseif (stripos($value, 'insert into')) unset($data_lines[$key]);
	}

	// store into cookie
	foreach($_POST AS $key => $value) {
		$date = time() + 999999;
		if ($key == 'sql') $date = time() + 600;
		setcookie($key, $value, $date, '/');
	}

	$table = array();
	$table['project_name'] = stripslashes($_POST['project_name']);
	$table['list_page']    = stripslashes($_POST['list_page']);
	$table['crud_page']    = stripslashes($_POST['crud_page']);
	$table['search_page']  = stripslashes($_POST['search_page']);
	$table['paging_page']  = stripslashes($_POST['paging_page']);
	$table['id_key'] = get_primary_key($_POST['sql']);
	if ($table['id_key'] == '') $table['id_key'] = 'id';
	
	// get first table name
	if (preg_match('/CREATE TABLE .+/', $data, $matches)) {
		$table['table_name'] = find_text($matches[0]);
		$max = count($data_lines);
		for ($i = 1; $i < $max; $i++) {
			if (strpos(trim($data_lines[$i]), '`') === 0) { // this line has a column
				$col = find_text(trim($data_lines[$i]));
				$bool = (stripos($data_lines[$i], 'INT(1)') ? 1 : 0);
				$blob = (stripos($data_lines[$i], 'TEXT') || stripos($data_lines[$i], 'BLOB') ? 1 : 0);
				$datetime = (stripos($data_lines[$i], 'DATETIME') ? 1 : 0);
				$date = (!$datetime && stripos($data_lines[$i], 'DATE') ? 1 : 0);
				$table['columns'][$col] = array(
					'bool' => $bool,
					'blob' => $blob,
					'date' => $date,
					'datetime' => $datetime,
				);
			}
		}
		$show_form = 1;
	} else {
		$message .= "Cannot find 'CREATE TABLE `table_name` ( '";
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script type="text/javascript" src="js/functions.js"></script>
<title>PHP MySQL CRUD Scaffold</title>
<link href="style.css" rel="stylesheet" type="text/css" />
</head>

<body>
<h1><a href="index.php" style="color:#fff;text-decoration:none">php<span class="color">Scaffold</span></a></h1>

<div class="submenu">
<? if ($show_form) echo 'Files saved in <strong>tmp/'.$table['project_name'].'</strong> directory.'; ?>
</div>

<div class="container">
<? if ($message != '') echo "<div class=\"message\">$message</div>"; ?>

<div <? if ($show_form) echo 'style="display:none"'; ?> id="create_crud">
<form action="" method="post">

<p>Welcome to <span style="color:#9D608C;font-weight:bold">phpscaffold.com</span>, where you can
quickly generate your CRUD scaffold pages for PHP and MySQL.</p>

<p>Enter an SQL table dump below to generate your pages. <a
href="javascript:show_hint();">[Hint]</a></p>

<p><textarea id="sql" name="sql" cols="55" rows="10"><?= (isset($_REQUEST['sql']) ? stripslashes($_REQUEST['sql']) : '') ?></textarea></p>

<? $val = (isset($_REQUEST['project_name']) ? stripslashes($_REQUEST['project_name']) : 'project'); ?>
<p><label>Project folder name</label>
  <input name="project_name" type="text" id="project_name" value="<?= $val ?>" /></p>

<? $val = (isset($_REQUEST['crud_page']) ? stripslashes($_REQUEST['crud_page']) : 'crud.php'); ?>
<p><label>CRUD file name</label>
  <input type="text" name="crud_page" value="<?= $val ?>" id="crud_page" /></p>

<? $val = (isset($_REQUEST['search_page']) ? stripslashes($_REQUEST['search_page']) : 'inc.search.php'); ?>
<p><label>Search file name</label>
  <input type="text" name="search_page" value="<?= $val ?>" id="search_page" /></p>

<? $val = (isset($_REQUEST['paging_page']) ? stripslashes($_REQUEST['paging_page']) : 'inc.paging.php'); ?>
<p><label>Paging file name</label>
  <input type="text" name="paging_page" value="<?= $val ?>" id="paging_page" /></p>

<p><input type="hidden" name="id_key" id="id_key" value="id" />
  <input type="hidden" name="list_page" id="list_page" value="index.php" />
  <input name="scaffold_info" type="hidden" value="1" />
  <input type="submit" value="Make pages" /></p>
</form>
</div>

<?
if ($show_form) {
	$s = new Scaffold($table);

	echo '<h2><a href="tmp/">Created projects</a>:</h2>';
	echo list_dir('tmp');

	/* Directories */
	$dir = "tmp/{$table['project_name']}/";
	$abm = "{$table['table_name']}/";
	$css = 'css/';
	$statics = 'lib/statics/';

	/* Create directory layout if not exists */
	if(!is_dir($dir)) mkdir($dir);
	if(!is_dir($dir.$abm)) mkdir($dir.$abm);
	if(!is_dir($dir.$css)) mkdir($dir.$css);

	/* Create generated files */
	file_put_contents($dir.$abm.$table['list_page'], $s->list_page());
	file_put_contents($dir.$abm.$table['search_page'], $s->search_page());
	file_put_contents($dir.$abm.$table['crud_page'], $s->crud_page());
	file_put_contents($dir.'inc.auth.php', $s->session_auth());
	file_put_contents($dir.'index.php', "<?\nheader('Location: {$table['table_name']}/')\n?>");

	/* Copy static files */
	copy($statics.'inc.paging.php', $dir.'inc.paging.php');
	copy($statics.'inc.functions.php', $dir.'inc.functions.php');
	copy($statics.'inc.layout.php', $dir.'inc.layout.php');
	copy($statics.'css/stylesheet.css', $dir.$css.'stylesheet.css');

	/* Log table schema definition */
	file_put_contents($dir.'schema.sql', $_POST['sql']."\n\n", FILE_APPEND);
}
?>

</div>

</body>
</html>
