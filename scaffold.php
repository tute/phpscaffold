<?
function find_text($text, $delimit_start = '`', $delimit_end = '`') {
	$start = strpos($text, $delimit_start);
	if ($start === false) return false;

	$end = strpos( substr($text, $start + 1), $delimit_end);
	if ($end === false) return false;

	return substr( $text, $start + 1, $end);
}


class Scaffold {

	public $table = array();
	public $download = false;

	function Scaffold($table) {
		$this->table = $table;
	}

	function listtable() {
		$column_array = array();
		$return_string = "<?\n";

		if ($this->table['include'] != '')
			$return_string .= "include('{$this->table['include']}');\n";

		$return_string .= "\nprint_header('" . ucwords($this->table['name']) . "');

if (isset(\$_GET['msg'])) echo '<p id=\"msg\">'.\$_GET['msg'].'</p>';

echo '<table>\n";
		$return_string .= "  <tr>\n";
		foreach($this->table AS $key => $value) {
			if (is_array($value)) {
				$column = $key;
				$column_array[] = array( 'tipo' => $value, 'nombre' => $key );
				$return_string .= "    <th>". $this->title($column) ."</th>\n";
			}
		}
		$return_string .= "  </tr>';

\$r = mysql_query(\"SELECT * FROM `{$this->table['name']}`\") or trigger_error(mysql_error());
while(\$row = mysql_fetch_array(\$r)) {\n";
		$return_string .= "	echo '  <tr>\n";
		
		foreach($column_array as $value) {
			if($value[tipo][blob])
				$val = 'nl2br($row['.$value[nombre].'])';
			elseif($value[tipo][datetime])
				$val = 'humanize($row['.$value[nombre].'])';
			else
				$val = '$row['.$value[nombre].']';

			$return_string .= "    <td>' . $val . '</td>\n";
		}
		$return_string .= "    <td><a href=\"{$this->table['edit_page']}?{$this->table['id_key']}=' . \$row['{$this->table[id_key]}'] . '\">Edit</a></td>
    <td><a href=\"{$this->table['delete_page']}?{$this->table['id_key']}=' . \$row['{$this->table[id_key]}'] . '\" onclick=\"return confirm(\'Are you sure?\')\">Delete</a></td>
  </tr>';\n";
		$return_string .= "}\n\n";
		$return_string .= "echo '</table>

<p><a href=\"{$this->table['new_page']}\">New entry</a></p>';

print_footer();
?>";

		return $return_string;
	}

	function newrow() {
		$return_string = "<?\n";
		if ($this->table['include'] != '')
			$return_string .= "include('{$this->table['include']}');\n";

		$column_array = array();
		$text = "<ul>\n";
		foreach($this->table as $key => $value) {
			if (is_array($value)) {
				$column = $key;
				if($column != $this->table['id_key'] ) {
					$column_array[] = array( 'tipo' => $value, 'nombre' => $key );
					if($value['blob']) {
						$text .= $this->html_chars('  <li><label>'.$this->title($column).': <textarea name="'.$column.'" cols="40" rows="10"></textarea></label></li>' . "\n");
					} elseif($value['bool']) {
						$text .= $this->html_chars('  <li><label>'.$this->title($column).': <input type="checkbox" name="'.$column.'" value="1" /></label></li>');
					} elseif($value['datetime']) {
						$text .= $this->html_chars('  <li><label>'.$this->title($column).": <?=input_datetime('".strtolower($this->title($column))."', NULL)?></label></li>\n");
					} else {
						$text .= '  <li><label>' . $this->title($column) . ': <input type="text" name="'.$column.'" /></label></li>' . "\n";
					}
				}
			}
		}
		$text .= '</ul>';

		$return_string .= "
if (isset(\$_POST['submitted'])) {
	foreach(\$_POST AS \$key => \$value) { \$_POST[\$key] = mysql_real_escape_string(\$value); }\n";
		$insert = "INSERT INTO `{$this->table['name']}` (";
		$counter = 0;
		foreach($column_array as $value) {
			$insert .= "`$value[nombre]`" ;
			if ($counter < count($column_array) - 1)
				$insert .= ", ";
			$counter++;
		}
		$insert .= ') VALUES (';

		$counter = 0;
		foreach($column_array as $value) {
			$val = parse($value[nombre], $value[tipo]);
			$insert .= "'$val'" ;
			if ($counter < count($column_array) - 1 )
				$insert .= ", ";
			$counter++;
		}
		$insert .= ")";

		$return_string .= "	\$sql = \"$insert\";
	mysql_query(\$sql) or die(mysql_error());
	\$msg = (mysql_affected_rows()) ? 'Added row.' : 'Nothing changed.';
	header('Location: index.php?msg='.\$msg);
}

print_header('Add {$this->table['name']}');
?>\n\n";
		$return_string .= '<form action="" method="post">' . "
$text
" . '<p><input type="hidden" value="1" name="submitted" />
<input type="submit" value="Create" /></p>
</form>
<?
print_footer();
?>';

		return $return_string;
	}


	function editrow() {
		$return_string = "<?\n";
		if ($this->table['include'] != '')
			$return_string .= "include('{$this->table['include']}');\n\n";

		$return_string .= "if (isset(\$_GET['{$this->table['id_key']}']) ) {
	\${$this->table['id_key']} = \$_GET['{$this->table['id_key']}'];\n\n";

		$column_array = array();
		$text = "<ul>\n";
		foreach($this->table as $key => $value) {
			if (is_array($value)) {
				$column = $key;
				if($column != $this->table['id_key'] ) {
					$column_array[] = array( 'tipo' => $value, 'nombre' => $key );
					if($value['blob']) {
						$text .= $this->html_chars('  <li><label>' . $this->title($column) . ": <textarea name=\"$column\" cols=\"40\" rows=\"10\"><?= stripslashes(\$row[$column]) ?></textarea></label></li>\n");
					} elseif($value['bool']) {
						$text .= $this->html_chars('  <li><label>'.$this->title($column).': <input type="checkbox" name="'.$column.'" value="1" <?= ($row['.$column.'] == 1 ? \'checked="checked"\' : \'\') ?> /></label></li>');
					} elseif($value['datetime']) {
						$text .= $this->html_chars('  <li><label>' . $this->title($column) . ": <?=input_datetime('".strtolower($this->title($column))."', \$row[$column])?></label></li>\n");
					} else {
						$text .= '  <li><label>' . $this->title($column) . ': <input type="text" name="'.$column.'" value="<?= stripslashes($row['.$column.']) ?>" /></label></li>' . "\n";
					}
				}
			}
		}
		$text .= '</ul>';

		$return_string .= "if (isset(\$_POST['submitted'])) {
	foreach(\$_POST AS \$key => \$value) { \$_POST[\$key] = mysql_real_escape_string(\$value); }\n";
		$insert = "UPDATE `{$this->table['name']}` SET ";
		$counter = 0;
		foreach($column_array as $value) {
			$field = $value[nombre];
			$val = parse($field, $value[tipo]);
			$insert .= " `$field` =  '$val'" ;
			if ($counter < count($column_array) - 1 )
				$insert .= ", ";
			$counter++;
		}
		$insert .= "  WHERE `{$this->table['id_key']}` = '\${$this->table['id_key']}' ";

		$return_string .= "	\$sql = \"$insert\";
	mysql_query(\$sql) or die(mysql_error());
	\$msg = (mysql_affected_rows()) ? 'Edited row.' : 'Nothing changed.';
	header('Location: index.php?msg='.\$msg);
}

print_header('Edit {$this->table['name']}');

\$row = mysql_fetch_array ( mysql_query(\"SELECT * FROM `{$this->table['name']}` WHERE `{$this->table['id_key']}` = '\${$this->table['id_key']}' \"));
?>\n\n";

$return_string .= '<form action="" method="post">
'.$text.'
<p><input type="hidden" value="1" name="submitted" />
  <input type="submit" value="Edit" /></p>
</form>

<?
}
print_footer();
?>';

		return $return_string;
	}

	function deleterow() {
		$return_string = "<?\n";
		if ($this->table['include'] != '')
			$return_string .= 'include(\''.$this->table['include'].'\');';

		$return_string .= "
mysql_query(\"DELETE FROM `{$this->table['name']}` WHERE `{$this->table['id_key']}` = '\$_GET[{$this->table['id_key']}]}'\");
\$msg = (mysql_affected_rows()) ? 'Row deleted.' : 'Nothing deleted.';
header('Location: index.php?msg='.\$msg);
?>";
		return $return_string;
	}

	function session_auth() {
		$return_string = "<?php
include('functions.php');
\$msg = \$_GET['msg'];

if (isset(\$_POST['user']) && isset(\$_POST['pass'])) {
	if ((strlen(\$_POST['user']) > 0) and (strlen(\$_POST['pass']) > 0)
	  and (\$login[\$_POST['user']] == \$_POST['pass'])) {
		\$_SESSION['user_logged_in'] = true;
		header('Location: index.php?msg=Logged in.');
	} else {
		unset(\$_SESSION['user_logged_in']);
		\$msg = 'Sorry, wrong user id or password.';
	}
}

print_header('Login - ".ucwords($this->table['name'])."');

if (\$_GET['action'] == 'logout') {
	unset(\$_SESSION['user_logged_in']);
	session_destroy();
}

if (strlen(\$msg) > 0) echo '<p id=\"msg\">'.\$msg.'</p>';
if (\$_SESSION['user_logged_in'] != true) {
?>\n";
$return_string .= '<form action="auth.php" method="post">
<p>You need to log in to edit this database.</p>
<ul>
  <li><label>User: <input type="text" name="user" value="<?= stripslashes($_POST[user]) ?>" /></label></li>
  <li><label>Pass: <input type="password" name="pass" /></label></li>
</ul>
<p><input type="submit" value="Login" /></p>
</form>
<?
} else {
	echo \'<p><a href="'.$this->table['list_page'].'">Go to Listing</a></p>\';
}

print_footer();
?>';
		return $return_string;
	}

	function get_functions() {
		$return_string = '<?
/* General configuration */
/* MySQL */
$mysql_host = \'localhost\';
$mysql_user = \'root\';
$mysql_pass = \'mysql_pass\';
$dbname = \'database\';

/* Allowed users  */
$login = array(
	\'admin\' => \'pass\'
);


/* phpscaffold code - you may leave this untouched */

/* Session based or basic HTTP authentication. */
$sess_auth = true;

if ($sess_auth == true) {
	session_start();
	if ((!ereg(\'auth.php\', $_SERVER[\'PHP_SELF\']))
	  and (!isset($_SESSION[\'user_logged_in\'])
	  or $_SESSION[\'user_logged_in\'] !== true)) {
		header(\'Location: auth.php\');
		exit;
	}
} else {
	function doAuth() {
		header(\'WWW-Authenticate: Basic realm="Protected Area"\');
		header(\'HTTP/1.0 401 Unauthorized\');
		echo \'Valid username / password required.\';
		exit;
	}

	function checkUser() {
		global $login;
		if($_SERVER[\'PHP_AUTH_USER\']!=\'\' && $_SERVER[\'PHP_AUTH_PW\']!=\'\') {
			return ($login[$_SERVER[\'PHP_AUTH_USER\']] == $_SERVER[\'PHP_AUTH_PW\']);
		}
		return false;
	}

	if (!isset($_SERVER[\'PHP_AUTH_USER\']) or !checkUser()) {
		doAuth();
	}
}


// DB connect
$link = mysql_connect($mysql_host, $mysql_user, $mysql_pass);
if (!$link)
	die(\'Not connected : \' . mysql_error());
if (!mysql_select_db($dbname))
	die ("Can\'t use $dbname: " . mysql_error());

function print_header($title) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?= $title ?></title>
<style type="text/css" media="screen">
body {
  font: .9em "Trebuchet MS", Trebuchet, Verdana, Sans-Serif;
}
#msg {
  padding: 5px 10px;
  border: 1px solid #3a3;
  background: #dfd;
  font-weight: bold;
}
</style>
</head>

<body>
<h1><?= $title ?></h1>
<?
}

function print_footer() {
	$index = ereg(\''.$this->table['list_page'].'\', $_SERVER[\'PHP_SELF\']);
	$login = ereg(\'auth.php\', $_SERVER[\'PHP_SELF\']);
	if (!$index and !$login)
		echo \'<p><a href="'.$this->table['list_page'].'">Back to Listing</a></p>\';
	if ($_SESSION[\'user_logged_in\'] = true)
		echo \'<p><a href="auth.php?action=logout&amp;msg=You have been logged out.">[Logout]</a></p>\';
	echo "</body>\n</html>";
}'."

function input_datetime(\$field, \$value) {
	\$seg  = \$field . '_seg';
	\$min  = \$field . '_min';
	\$hour = \$field . '_hour';
	\$day  = \$field . '_day';
	\$mth  = \$field . '_mth';
	\$year = \$field . '_year';

	\$sel_seg  = (substr(\$value,17,2) ? substr(\$value,17,2) : date(s));
	\$sel_min  = (substr(\$value,14,2) ? substr(\$value,14,2) : date(i));
	\$sel_hour = (substr(\$value,11,2) ? substr(\$value,11,2) : date(h));
	\$sel_day  = (substr(\$value,8,2) ? substr(\$value,8,2) : date(d));
	\$sel_mth  = (substr(\$value,5,2) ? substr(\$value,5,2) : date(m));
	\$sel_year = (substr(\$value,0,4) ? substr(\$value,0,4) : date(Y));

	\$ret = select_range(\$hour, \$sel_hour, 0, 23, 1) . ':';
	\$ret .= select_range(\$min, \$sel_min, 0, 59, 5) . ':';
	\$ret .= select_range(\$seg, \$sel_seg, 0, 59, 1) . ' @ ';
	\$ret .= select_range(\$day, \$sel_day, 1, 31, 1) . '/';
	\$ret .= select_range(\$mth, \$sel_mth, 1, 12, 1) . '/';
	\$ret .= select_range(\$year, \$sel_year, 2009, 2020, 1);

	return \$ret;
}

function humanize(\$mysql_datetime) {
	return date('d/m/Y @ h:i:s', strtotime(\$mysql_datetime));
}

function select_range(\$name, \$selected, \$start, \$finish, \$range) {
	\$ret = '<select name=\"'.\$name.'\">';
	for(\$i=\$start; \$i <= \$finish; \$i += \$range) {
		(\$selected == \$i) ? \$sel = ' selected=\"selected\"' : \$sel = '';
		\$ret .= \"<option\$sel>\$i</option>\\n\";
	}
	\$ret .= '</select>';
	return \$ret;
}

function pr(\$arr) {
	echo '<pre>';
	print_r(\$arr);
	echo '</pre>';
}
?>";
		return $return_string;
	}

	function title($name) {
		return ucwords(str_replace("_", " ", trim($name)));
	}

	function html_chars($var) {
		return ($this->download) ? $var : htmlspecialchars($var);
	}
}

function parse($field, $type) {
	if($type[datetime]) {
		$seg  = $field . '_seg';
		$min  = $field . '_min';
		$hour = $field . '_hour';
		$day  = $field . '_day';
		$mth  = $field . '_mth';
		$year = $field . '_year';
		$val = "\$_POST[$year]-\$_POST[$mth]-\$_POST[$day] \$_POST[$hour]:\$_POST[$min]:\$_POST[$seg]";
	} else
		$val = "\$_POST[$field]";
	return $val;
}
?>
