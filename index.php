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

	$table['project_name'] = stripslashes($_POST['project_name']);
	$table['list_page'] = stripslashes($_POST['list_page']);
	$table['edit_page'] = stripslashes($_POST['edit_page']);
	$table['new_page'] = stripslashes($_POST['new_page']);
	$table['delete_page'] = stripslashes($_POST['delete_page']);
	$table['include'] = stripslashes($_POST['include']);
	$table['search_page'] = stripslashes($_POST['search_page']);
	$table['paging_page'] = stripslashes($_POST['paging_page']);

	$table['id_key'] = trim($_POST['id_key']);
	if ($table['id_key'] == '') $table['id_key'] = 'id';
	
	// get first table name
	if (eregi('CREATE TABLE `(.)+` \(', $data, $matches)) {
		$table['name'] = find_text($matches[0]);
		$max = count($data_lines);
		for ($i = 1; $i < $max; $i++) {
			if (strpos(trim($data_lines[$i]), '`') === 0) { // this line has a column
				$col = find_text(trim($data_lines[$i]));
				$bool = (stripos($data_lines[$i], 'INT(1)') ? 1 : 0);
				$blob = (stripos($data_lines[$i], 'TEXT') || stripos($data_lines[$i], 'BLOB') ? 1 : 0);
				$date = (stripos($data_lines[$i], 'DATE ') ? 1 : 0);
				$datetime = (stripos($data_lines[$i], 'DATETIME') ? 1 : 0);
				$table[$col] = array(
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
<? if ($show_form) echo '<a href="javascript:showNew();">Enter New Table</a> | <a href="javascript:showAll()">Show All</a> | <a href="javascript:hideAll()">Hide All</a>'; ?>
</div>

<div class="container">
<? if ($message != '') echo "<div class=\"message\">$message</div>"; ?>

<div <? if ($show_form) echo 'style="display:none"'; ?> id="create_crud">
<form action="" method="post">

<p>Welcome to <span style="color:#9D608C;font-weight:bold">phpscaffold.com</span>, where you can
quickly generate your CRUD scaffold pages for PHP and MySQL.</p>

<p>Enter an SQL table dump below to generate your pages. <a
href="javascript:showHint('sql_hint');">[Hint]</a></p>

<div id="sql_hint" style="display:none; background:#ffd; padding:5px; margin:10px 0">
Paste your database dump table for which you wish to generate CRUD files.
A sample text maybe:
<pre>
CREATE TABLE `users_test` (
  `id` int(10) NOT NULL auto_increment,
  `email` varchar(100) NOT NULL,
  `pass` varchar(32) NOT NULL,
  `curriculum` text NOT NULL,
  `is_admin` int(1) NOT NULL,
  `last_login` datetime NOT NULL,
  `created` date NOT NULL,
  PRIMARY KEY (`id`)
);
</pre>
</div>

<p><textarea name="sql" id="sql" cols="55" rows="10"><?= (isset($_REQUEST['sql']) ? stripslashes($_REQUEST['sql']) : '') ?></textarea></p>

<? $val = (isset($_REQUEST['project_name']) ? stripslashes($_REQUEST['project_name']) : 'project'); ?>
<p><label>Project folder name</label>
  <input name="project_name" type="text" id="project_name" value="<?= $val ?>" /></p>

<input type="hidden" name="id_key" id="id_key" value="id" />
<input type="hidden" name="list_page" id="list_page" value="index.php" />

<? $val = (isset($_REQUEST['new_page']) ? stripslashes($_REQUEST['new_page']) : 'new.php'); ?>
<p><label>New file name</label>
  <input type="text" name="new_page" value="<?= $val ?>" id="new_page" /></p>

<? $val = (isset($_REQUEST['edit_page']) ? stripslashes($_REQUEST['edit_page']) : 'edit.php'); ?>
<p><label>Edit file name</label>
  <input type="text" name="edit_page" value="<?= $val ?>" id="edit_page" /></p>

<? $val = (isset($_REQUEST['delete_page']) ? stripslashes($_REQUEST['delete_page']) : 'delete.php'); ?>
<p><label>Delete file name</label>
  <input type="text" name="delete_page" value="<?= $val ?>" id="delete_page" /></p>

<? $val = (isset($_REQUEST['search_page']) ? stripslashes($_REQUEST['search_page']) : 'inc.search.php'); ?>
<p><label>Search file name</label>
  <input type="text" name="search_page" value="<?= $val ?>" id="search_page" /></p>

<? $val = (isset($_REQUEST['paging_page']) ? stripslashes($_REQUEST['paging_page']) : 'inc.paging.php'); ?>
<p><label>Paging file name</label>
  <input type="text" name="paging_page" value="<?= $val ?>" id="paging_page" /></p>

<? $val = (isset($_REQUEST['include']) ? stripslashes($_REQUEST['include']) : 'inc.functions.php'); ?>
<p><label>Include file name</label>
  <input type="text" name="include" value="<?= $val ?>" id="include" /></p>

<p><input name="scaffold_info" type="hidden" value="1" />
  <input type="submit" value="Make pages" /></p>
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
	echo '<p>Files saved in <span style="font-family:Monaco,"Courier New",monospace">tmp/'.$table['project_name'].'</span> directory.</p>';
	echo files_textarea_head('list') . htmlspecialchars($s->listtable()) . "\n</textarea>";
	echo files_textarea_head('new') . htmlspecialchars($s->newrow()) . "\n</textarea>";
	echo files_textarea_head('edit') . htmlspecialchars($s->editrow()) . "\n</textarea>";
	echo files_textarea_head('delete') . htmlspecialchars($s->deleterow()) . "\n</textarea>";
	echo files_textarea_head('authentication') . htmlspecialchars($s->session_auth()) . "\n</textarea>";
	echo files_textarea_head('search') . htmlspecialchars($s->search_page()) . "\n</textarea>";
	echo files_textarea_head('paging') . htmlspecialchars($s->paging_page()) . "\n</textarea>";
	echo files_textarea_head('functions') . htmlspecialchars($s->get_functions()) . "\n</textarea>";

	// Save files in tmp folder
	$dir = "tmp/{$table['project_name']}/";
	$abm = "{$table['name']}/";
	if(!is_dir($dir)) mkdir($dir);
	if(!is_dir($dir.$abm)) mkdir($dir.$abm);
	file_put_contents($dir.$abm.$table['list_page'], $s->listtable());
	file_put_contents($dir.$abm.$table['search_page'], $s->search_page());
	file_put_contents($dir.$abm.$table['paging_page'], $s->paging_page());
	file_put_contents($dir.$abm.$table['new_page'], $s->newrow());
	file_put_contents($dir.$abm.$table['edit_page'], $s->editrow());
	file_put_contents($dir.$abm.$table['delete_page'], $s->deleterow());
	file_put_contents($dir.'inc.auth.php', $s->session_auth());
	file_put_contents($dir.$table['include'], $s->get_functions());
	file_put_contents($dir.'index.php', "<?\nheader('Location: {$table['name']}/')\n?>");
}
?>
</div>

</body>
</html>
