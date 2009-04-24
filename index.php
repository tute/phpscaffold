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
				$blob = ( stripos($data_lines[$i], 'TEXT') || stripos($data_lines[$i], 'BLOB') ) ? 1 : 0;
				$datetime = ( stripos($data_lines[$i], 'DATETIME') ) ? 1 : 0;
				eval( "\$table['$col'] = array('blob' => $blob, 'datetime' => $datetime );");
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

<div <? if ($show_form) echo 'style=display:none'; ?> id=new_table>
<form action="" method="post">
Welcome to <span class="style1">phpscaffold.com</span>, where you can quickly generate your CRUD scaffold pages for PHP and MySQL.

<p>Enter an SQL table dump below to generate your pages. <a
href="javascript:showHint('sql_hint');">[Hint]</a></p>

<div id=sql_hint style="display:none; ">
  <div style="background: #FFFFDD;padding: 5px; margin: 10px 0;">
  Paste your phpMyAdmin export SQL queries for the table your which to generate list, edit, new, and delete pages in the box below. A sample text maybe:
  <pre style="color:#888">
-- 
-- Table structure for table `users`
-- 

CREATE TABLE `users` (
  `id` int(10) NOT NULL auto_increment,
  `fn` varchar(30) collate latin1_general_ci NOT NULL,
  `mn` varchar(30) collate latin1_general_ci NOT NULL,
  `ln` varchar(30) collate latin1_general_ci NOT NULL,
  `email` varchar(100) collate latin1_general_ci NOT NULL,
  `pass` varchar(32) collate latin1_general_ci NOT NULL,
  `display_name` varchar(30) collate latin1_general_ci NOT NULL,
  `created_on` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=5 ;  
  </pre>
</div>

</div>
    <textarea name="sql" id="sql" cols="80" rows="10"><? if (isset($_REQUEST['sql'])) echo stripslashes($_REQUEST['sql']); else echo '' ?></textarea>

  <p>Include File Name. You create this file. <a href="javascript:showHint('include_hint');">[Example]</a><br /> 
    <input name="include" type="text" id="include" value="<? if (isset($_REQUEST['include'])) echo stripslashes($_REQUEST['include']); else echo 'config.php' ?>" />
    </p>

<div id="include_hint" style="display:none; ">
<pre style="background: #FFFFDD;padding: 5px; margin: 10px 0; ">

// connect to db
$link = mysql_connect('localhost', 'mysql_user', 'mysql_password');
if (!$link) {
    die('Not connected : ' . mysql_error());
}

if (! mysql_select_db('foo') ) {
    die ('Can\'t use foo : ' . mysql_error());
}
 
</pre>
</div>

  <p>Primary Key Name<br /> 
    <input name="id_key" type="text" id="id_key" value="<? if (isset($_REQUEST['id_key'])) echo stripslashes($_REQUEST['id_key']); else echo 'id' ?>" />
    </p>
  <p>
    File Name of List<br /> 
    <input type="text" name="list_page" value="<? if (isset($_REQUEST['list_page'])) echo stripslashes($_REQUEST['list_page']); else echo 'list.php' ?>" id="list_page" />
    </p>
  <p>File Name of New<br /> 
    <input type="text" name="new_page" value="<? if (isset($_REQUEST['new_page'])) echo stripslashes($_REQUEST['new_page']); else echo 'new.php' ?>" id="new_page" />
    </p>
  <p>File Name of Edit<br />
    <input type="text" name="edit_page" value="<? if (isset($_REQUEST['edit_page'])) echo stripslashes($_REQUEST['edit_page']); else echo 'edit.php' ?>" id="edit_page" />
</p>
  <p>File Name of Delete<br />
    <input type="text" name="delete_page" value="<? if (isset($_REQUEST['delete_page'])) echo stripslashes($_REQUEST['delete_page']); else echo 'delete.php' ?>" id="delete_page" />
  </p> 
    <input name="scaffold_info" type="hidden" value="1" />
    <input  type="submit" value="Make My Pages" />
</form>
</div>

<?
if ($show_form) {
		$s = new Scaffold($table);
		
		echo "<div class=options><a href=javascript:toggle('list');>Show/Hide</a> | <a href=javascript:selectAll('list');>Select All";
		if (stripos($_SERVER['HTTP_USER_AGENT'], 'msie') !== false) echo " &amp; Copy";
		echo "</a> | <a href=download.php>Download All Files</a></div>";
		echo "<h2>List</h2>";
		echo "<textarea rows=30 cols=80 wrap=off class=textarea id=list>";
		echo $s->listtable();
		echo "</textarea>";

		echo "<div class=options><a href=javascript:toggle('new');>Show/Hide</a> | <a href=javascript:selectAll('new');>Select All";
		if (stripos($_SERVER['HTTP_USER_AGENT'], 'msie') !== false) echo " &amp; Copy";
		echo "</a> | <a href=download.php>Download All Files</a></div>";
		echo "<h2>New</h2>";
		echo "<textarea rows=30 cols=80 wrap=off class=textarea id=new>";
		echo $s->newrow();
		echo "</textarea>";

		echo "<div class=options><a href=javascript:toggle('edit');>Show/Hide</a> | <a href=javascript:selectAll('edit');>Select All";
		if (stripos($_SERVER['HTTP_USER_AGENT'], 'msie') !== false) echo " &amp; Copy";
		echo "</a> | <a href=download.php>Download All Files</a></div>";
		echo "<h2>Edit</h2>";
		echo "<textarea rows=30 cols=80 wrap=off class=textarea id=edit>";
		echo $s->editrow();
		echo "</textarea>";
		
		echo "<div class=options><a href=javascript:toggle('delete');>Show/Hide</a> | <a href=javascript:selectAll('delete');>Select All";
		if (stripos($_SERVER['HTTP_USER_AGENT'], 'msie') !== false) echo " &amp; Copy";
		echo "</a> | <a href=download.php>Download All Files</a></div>";
		echo "<h2>Delete</h2>";
		echo "<textarea rows=10 cols=80 wrap=off class=textarea id=delete>";
		echo $s->deleterow();
		echo "</textarea>";
}
?>
</div>

</body>
</html>