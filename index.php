<?php
error_reporting(E_ALL);
include 'lib/scaffold.php';
include 'lib/functions.inc';

$did_scaffold = 0;

if (isset($_POST['scaffold_info'])) {
	$project['project_name'] = stripslashes($_POST['project_name']);
	$project['list_page']    = stripslashes($_POST['list_page']);
	$project['crud_page']    = stripslashes($_POST['crud_page']);
	$project['search_page']  = stripslashes($_POST['search_page']);
	$project['paging_page']  = stripslashes($_POST['paging_page']);
	$project['tables']       = array();

	$tables = explode('CREATE ', $_POST['sql']);
	foreach($tables as $sql_data) {
		$data_lines = explode("\n", $sql_data);

		/* Strip non table info (comments, inserts, etc) */
		foreach ($data_lines as $key => $value) {
			$value = trim($value);
			if ((substr($value, 0, 2) == '--')
			  || stripos($value, 'DROP')
			  || stripos($value, 'INSERT INTO'))
				unset($data_lines[$key]);
		}

		$table = array();
		$table['id_key'] = get_primary_key($sql_data);

		// Add table structure
		if (preg_match('/TABLE .+/', $sql_data, $matches)) {
			$table_name = find_text($matches[0]);
			$max = count($data_lines);
			for ($i = 1; $i < $max; $i++) {
				if (strpos(trim($data_lines[$i]), '`') === 0) { // this line has a column
					$col = find_text(trim($data_lines[$i]));
					$bool = stripos($data_lines[$i], 'INT(1)');
					$blob = (stripos($data_lines[$i], 'TEXT') || stripos($data_lines[$i], 'BLOB'));
					$datetime = stripos($data_lines[$i], 'DATETIME');
					$date = (!$datetime && stripos($data_lines[$i], 'DATE'));
					$table['columns'][$col] = array(
						'bool' => $bool,
						'blob' => $blob,
						'date' => $date,
						'datetime' => $datetime,
					);
				}
			}
			$project['tables'][$table_name] = $table;
			$did_scaffold = 1;
		}
	} // foreach table

	if ($did_scaffold) {
		/* Start CRUD generation */

		/* Create directory layout if not exists */
		$dir = "tmp/{$project['project_name']}/";
		$css = 'css/';
		$statics = 'lib/statics/';
		if(!is_dir($dir)) mkdir($dir);
		if(!is_dir($dir.$css)) mkdir($dir.$css);

		/* Copy common files */
		file_put_contents($dir.'index.php', "<?\nheader('Location: $table_name/')\n?>");
		copy($statics.'inc.functions.php',  $dir . 'inc.functions.php');
		copy($statics.'inc.layout.php',     $dir . 'inc.layout.php');
		copy($statics.'inc.auth.php',       $dir . 'inc.auth.php');
		copy($statics.'inc.paging.php',     $dir . $project['paging_page']);
		copy($statics.'css/stylesheet.css', $dir . $css . 'stylesheet.css');
		/* Don't override configuration file */
		if (!file_exists($dir . 'inc.config.inc'))
			copy($statics.'inc.config.inc',     $dir . 'inc.config.inc');

		/* Create each CRUD folder and files */
		foreach($project['tables'] as $table_name => $table_info) {
			$s = new Scaffold($project, $table_name, $table_info);

			$abm = "$table_name/";
			if(!is_dir($dir.$abm)) mkdir($dir.$abm);

			file_put_contents($dir.$abm.$project['list_page'], $s->list_page());
			file_put_contents($dir.$abm.$project['search_page'], $s->search_page());
			file_put_contents($dir.$abm.$project['crud_page'], $s->crud_page());
		}

		/* Log table schema definition */
		file_put_contents($dir.'schema.sql', $_POST['sql']."\n\n", FILE_APPEND);
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script type="text/javascript" src="assets/functions.js"></script>
<title>PHP MySQL CRUD Scaffold</title>
<link href="assets/style.css" rel="stylesheet" type="text/css" />
</head>

<body>
<h1><a href="index.php" style="color:#fff;text-decoration:none">php<span class="color">Scaffold</span></a></h1>

<div class="submenu">
<? if ($did_scaffold) echo 'Files saved in <strong>tmp/'.$project['project_name'].'</strong> directory.'; ?>
</div>

<div class="container">

<div <? if ($did_scaffold) echo 'style="display:none"'; ?> id="create_crud">
<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post">

<p>Welcome to <span style="color:#9D608C;font-weight:bold">phpscaffold.com</span>, where you can
quickly generate your CRUD scaffold pages for PHP and MySQL.</p>

<p>Enter an SQL table dump to generate your pages. <a
href="javascript:show_hint()">[Hint]</a></p>

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
if ($did_scaffold) {
	echo '<h2><a href="tmp/">Created projects</a>:</h2>';
	echo list_dir('tmp');
}
?>

</div>

</body>
</html>
