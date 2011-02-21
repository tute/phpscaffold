<?php
/* General configuration */
/* MySQL */
$mysql_host = 'localhost';
$mysql_user = 'root';
$mysql_pass = '';
$dbname = 'phpsc_db';

/* Allowed users  */
$login = array(
	'admin' => 'pass'
);


/* phpscaffold code - you may leave this untouched */
include('inc.layout.php');

/* We're in admin (FIXME) */
if (preg_match('/admin/', $_SERVER['PHP_SELF'])) {
	session_start();
	if ((!preg_match('/inc.auth.php/', $_SERVER['PHP_SELF']))
	  and (!isset($_SESSION['user_logged_in'])
	  or $_SESSION['user_logged_in'] !== true)) {
		header('Location: ../inc.auth.php');
		exit;
	}
}

// DB connect
$link = @mysql_connect($mysql_host, $mysql_user, $mysql_pass);
if (!$link)
	die('Not connected: ' . mysql_error());
if (!mysql_select_db($dbname))
	die ("Can't use $dbname: " . mysql_error());
mysql_query('SET NAMES "utf8"');

function input_date($field, $value) {
	$day  = $field . '_day';
	$mth  = $field . '_mth';
	$year = $field . '_year';

	$sel_day  = (substr($value,8,2) ? substr($value,8,2) : date('d'));
	$sel_mth  = (substr($value,5,2) ? substr($value,5,2) : date('m'));
	$sel_year = (substr($value,0,4) > 0 ? substr($value,0,4) : date('Y'));

	$ret = select_range($day, $sel_day, 1, 31) . '/';
	$ret .= select_range($mth, $sel_mth, 1, 12) . '/';
	$ret .= select_range($year, $sel_year, 2009, 2020);

	return $ret;
}

function input_datetime($field, $value) {
	$seg  = $field . '_seg';
	$min  = $field . '_min';
	$hour = $field . '_hour';

	$sel_seg  = (substr($value,17,2) ? substr($value,17,2) : date('s'));
	$sel_min  = (substr($value,14,2) ? substr($value,14,2) : date('i'));
	$sel_hour = (substr($value,11,2) ? substr($value,11,2) : date('h'));

	$ret = input_date($field, $value) . ' @ ';
	$ret .= select_range($hour, $sel_hour, 0, 23) . ':';
	$ret .= select_range($min, $sel_min, 0, 59, 5) . ':';
	$ret .= select_range($seg, $sel_seg, 0, 59, 5);

	return $ret;
}

function search_options($field, $selected = 'is', $type = 'int') {
	$ret = "<select name=\"{$field}_opts\">\n";
	if ($type == 'int') {
		$selected = ($selected == 'is') ? '=' : $selected;
		$options = array('=' => '=',
			'<' => '&lt;',
			'<=' => '≤',
			'>' => '&gt;',
			'>=' => '≥');
	} else { /* is string */
		$options = array('=' => 'is', 'like' => 'contains');
	}
	foreach ($options as $k => $v) {
		$sel = ($selected == $k ? ' selected="selected"' : '');
		$ret .= "  <option value=\"$k\"$sel>$v</option>\n";
	}
	return $ret .= "</select>\n";
}

function select_range($name, $selected, $start, $finish, $range = 1) {
	$ret = '<select name="'.$name.'">';
	for($i=$start; $i <= $finish; $i += $range) {
		$sel = ($selected == $i ? ' selected="selected"' : '');
		$ret .= "<option$sel>$i</option>\n";
	}
	$ret .= '</select>';
	return $ret;
}

/*
*	Given a table and an id, return it's name.
*/
function get_data($table_name, $name_col, $id) {
	$sql = "SELECT $name_col FROM $table_name WHERE id = $id";
	$r = mysql_query($sql) or trigger_error(mysql_error());
	$row = mysql_fetch_array($r);
	return $row[$name_col];
}

/*
*	Build select menu with data from a model.
*/
function build_options($table_name, $name_col, $fk_col_name, $selected = null, $id_col = 'id') {
	$sql = "SELECT $id_col, $name_col FROM $table_name";
	$r = mysql_query($sql) or trigger_error(mysql_error());
	$ret = '<select name="'.$fk_col_name.'">';
	while($row = mysql_fetch_array($r)) {
		$sel = ($selected == $row[$id_col] ? ' selected="selected"' : '');
		$ret .= "<option value=\"$row[$id_col]\"$sel>$row[$name_col]</option>\n";
	}
	return $ret . '</select>';
}

/*
*	From separate _GET variables (YYYY, (M)M, (D)D)
*	 to MySQL string (YYYY-MM-DD)
*/
function parse_date_vars($date_field) {
	$year = $_GET[$date_field.'_year'];
	$mth  = $_GET[$date_field.'_mth'];
	$day  = $_GET[$date_field.'_day'];

	if(strlen($mth) == 1) $mth = '0'.$mth;
	if(strlen($day) == 1) $day = '0'.$day;

	return $year.'-'.$mth.'-'.$day;
}

/*
*	_GET variable set with non-trivial value?
*/
function search_by($var) {
	return (isset($_GET[$var]) and strlen($_GET[$var]) > 0);
}

function put_order($col) {
	if (isset($_SERVER['argv'])) {
	$pars = explode("[&]", $_SERVER['argv'][0]);
	$res = array();
	foreach($pars as $n => $par) {
		$p = explode("[=]", $par);
		if ($p[0] != 'order' and $p[0] != 'col')
			array_push($res, join('=', $p));
	}
	array_push($res, "col=$col");
	$pars = join("&amp;", $res);
	return "<a href=\"$_SERVER[PHP_SELF]?$pars&amp;order=ASC\">↑</a>
	<a href=\"$_SERVER[PHP_SELF]?$pars&amp;order=DESC\">↓</a>";
	}
}

function get_order($table, $default = 'id ASC') {
	if (isset($_GET['order']) and isset($_GET['col']))
		return "ORDER BY $table.{$_GET['col']} {$_GET['order']}";
	else
		return "ORDER BY $default";
}

function limit_chars($str, $lim = 150) {
	$words = explode(' ', substr($str, 0, $lim));
	$cut = (strlen($str) > $lim);
	return implode(' ', array_slice($words, 0, count($words)-$cut)) . ($cut ? '...' : '');
}

/* List crud directories */
function list_cruds() {
	$filter = array('.', '..', 'css');
	echo '<ul>';
	if ($handle = opendir('..')) {
		while (false !== ($file = readdir($handle))) {
			if (is_dir("../$file") && !in_array($file, $filter))
				echo "  <li><a href=\"../$file/\">$file</a></li>\n";
		}
		closedir($handle);
	}
	echo '</ul>';
}

function humanize($date) {
	$pattern = (strlen($date) == 10 ? 'd/m/Y' : 'd/m/Y @ h:i:s');
	return date($pattern, strtotime($date));
}

function pr($arr) {
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}
?>
