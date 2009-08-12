<?
include('scaffold.php');

$show_form = 0;
$message = '';

if (isset($_POST['scaffold_info'])) {
	$data = trim($_POST['sql']);
	$data_lines = explode("\n", $data);
	
	// strip all comments
	foreach ($data_lines  AS $key => $value) {
		$value = trim($value);
		if ($value[0] == '-' && $value[1] == '-') unset($data_lines[$key]);
		elseif (stripos($value, 'insert into')) unset($data_lines[$key]);
	}

	$table = array();

	// store into cookie
	foreach($_POST AS $key => $value) {
		$date = time() + 999999;
		if ($key == 'sql') $date = time() + 600;
		setcookie($key, $value, $date, '/');
	}

	$table['list_page'] = stripslashes($_POST['list_page']);
	$table['edit_page'] = stripslashes($_POST['edit_page']);
	$table['new_page'] = stripslashes($_POST['new_page']);
	$table['delete_page'] = stripslashes($_POST['delete_page']);
	$table['include'] = stripslashes($_POST['include']);

	$table['id_key'] = trim($_POST['id_key']);
	if ($table['id_key'] == '') $table['id_key'] = 'id';
	
	// get first table name
	if ( eregi('CREATE TABLE `(.)+` \(', $data, $matches) ) {
		$table['name'] = find_text($matches[0]);
		$max = count($data_lines);
		for ($i = 1; $i < $max; $i++ ) {
			if ( strpos( trim($data_lines[$i]), '`') === 0) { // this line has a column
				$col = find_text(trim($data_lines[$i]));
				$bool = (stripos($data_lines[$i], 'INT(1)') ? 1 : 0);
				$blob = (stripos($data_lines[$i], 'TEXT') || stripos($data_lines[$i], 'BLOB') ? 1 : 0);
				$datetime = (stripos($data_lines[$i], 'DATETIME') ? 1 : 0);
				eval( "\$table['$col'] = array(
					'bool' => $bool,
					'blob' => $blob,
					'datetime' => $datetime,
				);");
			}
		}

		$show_form = 1;
	}
	else {
		$message .= "Cannot find 'CREATE TABLE `table_name` ( '";
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<script type="text/javascript" src="js/prototype.js"></script>
<script type="text/javascript" src="js/scriptaculous.js"></script>
<script type="text/javascript" src="js/s.js"></script>

<title>PHP MySQL CRUD Scaffold</title>

<meta name="Keywords" content="php, mysql, crud, scaffold" />
<meta name="Description" content="Fast PHP CRUD Scaffold Maker" />

<link href="style.css" rel="stylesheet" type="text/css" />
</head>

<body>
<h1>php<span class="color">Scaffold</span></h1>

<div class="submenu">
<? if ($show_form) { ?>
<a href="javascript:showNew();">Enter New Table</a> | <a href="javascript:showAll()">Show All</a> | <a href="javascript:hideAll()">Hide All</a>
<? } ?>
</div>

<div class="container">
<? if ($message != '') echo "<div class=message>$message</div>"; ?>

<div <? if ($show_form) echo 'style="display:none"'; ?> id="new_table">
<form action="" method="post">

<p>Welcome to <span class="style1">phpscaffold.com</span>, where you can quickly generate your CRUD scaffold pages for PHP and MySQL.</p>

<p>Enter an SQL table dump below to generate your pages. <a
href="javascript:showHint('sql_hint');">[Hint]</a></p>

<div id="sql_hint" style="display:none">
  <div style="background:#ffd; padding:5px; margin:10px 0">
  Paste your database dump table for which you wish to generate CRUD files.
  A sample text maybe:
  <pre style="color:#888">
CREATE TABLE `users` (
  `id` int(10) NOT NULL auto_increment,
  `fn` varchar(30) NOT NULL,
  `mn` varchar(30) NOT NULL,
  `ln` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `pass` varchar(32) NOT NULL,
  `is_admin` int(1) NOT NULL,
  `created_on` datetime NOT NULL,
  PRIMARY KEY (`id`)
);
  </pre>
</div>

</div>
  <p><textarea name="sql" id="sql" cols="80" rows="10"><? if (isset($_REQUEST['sql'])) echo stripslashes($_REQUEST['sql']); else echo '' ?></textarea></p>

  <p>Include File Name <input name="include" type="text" id="include" value="<? if (isset($_REQUEST['include'])) echo stripslashes($_REQUEST['include']); else echo 'functions.php' ?>" /></p>

  <? $val = (isset($_REQUEST['id_key']) ? stripslashes($_REQUEST['id_key']) : 'id'); ?>
  <p>Primary Key Name <input name="id_key" type="text" id="id_key" value="<?= $val ?>" /></p>

  <? $val = (isset($_REQUEST['list_page']) ? stripslashes($_REQUEST['list_page']) : 'index.php'); ?>
  <p>File Name of List <input type="text" name="list_page" value="<?= $val ?>" id="list_page" /></p>

  <? $val = (isset($_REQUEST['new_page']) ? stripslashes($_REQUEST['new_page']) : 'new.php'); ?>
  <p>File Name of New <input type="text" name="new_page" value="<?= $val ?>" id="new_page" /></p>

  <? $val = (isset($_REQUEST['edit_page']) ? stripslashes($_REQUEST['edit_page']) : 'edit.php'); ?>
  <p>File Name of Edit <input type="text" name="edit_page" value="<?= $val ?>" id="edit_page" /></p>

  <? $val = (isset($_REQUEST['delete_page']) ? stripslashes($_REQUEST['delete_page']) : 'delete.php'); ?>
  <p>File Name of Delete <input type="text" name="delete_page" value="<?= $val ?>" id="delete_page" /></p>

  <p><input name="scaffold_info" type="hidden" value="1" />
  <input  type="submit" value="Make My Pages" /></p>
</form>
</div>

<?
if ($show_form) {
	function files_textarea_head($act) {
		$r = '<div class="options">
  <a href="javascript:toggle(\''.$act.'\');">Show/Hide</a> |
  <a href="javascript:selectAll(\''.$act.'\');">Select All</a>
</div>

<h2>'.ucwords($act).'</h2>
<textarea rows="30" cols="80" class="textarea" id="'.$act.'">';
		return $r;
	}
	$s = new Scaffold($table);
	echo '<p>Files saved in tmp/ directory.</p>';
	echo files_textarea_head('list') . $s->listtable() . "\n</textarea>";
	echo files_textarea_head('new') . $s->newrow() . "\n</textarea>";
	echo files_textarea_head('edit') . $s->editrow() . "\n</textarea>";
	echo files_textarea_head('delete') . $s->deleterow() . "\n</textarea>";
	echo files_textarea_head('authentication') . $s->session_auth() . "\n</textarea>";
	echo files_textarea_head('functions') . $s->get_functions() . "\n</textarea>";

	// Save files in tmp folder
	$dir = 'tmp/'.$table['name'].'/';
	if(!is_dir($dir)) mkdir($dir);
	file_put_contents($dir.$table['list_page'], html_entity_decode($s->listtable()));
	file_put_contents($dir.$table['new_page'], html_entity_decode($s->newrow()));
	file_put_contents($dir.$table['edit_page'], html_entity_decode($s->editrow()));
	file_put_contents($dir.$table['delete_page'], html_entity_decode($s->deleterow()));
	file_put_contents($dir.'auth.php', html_entity_decode($s->session_auth()));
	file_put_contents($dir.$table['include'], html_entity_decode($s->get_functions()));
}
?>
</div>

</body>
</html>
