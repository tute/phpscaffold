<?
class Scaffold {
	public $table = array();

	function Scaffold($table) {
		$columns = array();
		foreach($table as $key => $value)
			if (is_array($value))
				$columns[] = array('tipo' => $value, 'nombre' => $key);
		$this->table = $table;
		$this->columns = $columns;
	}

	function list_page() {
		$column_array = array();
		$return_string = "<?php
include('../inc.functions.php');\n";

		$return_string .= "\nprint_header('" . ucwords($this->table['name']) . "');

if (isset(\$_GET['msg'])) echo '<p id=\"msg\">'.\$_GET['msg'].'</p>';

/* Default search criteria (may be overriden by search form) */
\$conds = 'TRUE';
include('{$this->table['search_page']}');

/* Default paging criteria (may be overriden by paging functions) */
\$start     = 0;
\$per_page  = 100;
\$count_sql = 'SELECT COUNT({$this->table['id_key']}) AS tot FROM `{$this->table['name']}` WHERE ' . \$conds;
include('../{$this->table['paging_page']}');

/* Get selected entries! */
\$sql = \"SELECT * FROM `{$this->table['name']}` WHERE \$conds \" . get_order('{$this->table['name']}') . \" LIMIT \$start,\$per_page\";

echo '<table>\n";
		$return_string .= "  <tr>\n";
		foreach($this->columns as $v) {
			$return_string .= '    <th>'. $this->_title($v['nombre']) . ' \' . put_order(\''.$v['nombre']."') . '</th>\n";
		}
		$return_string .= "  </tr>';

\$r = mysql_query(\$sql) or trigger_error(mysql_error());
while(\$row = mysql_fetch_array(\$r)) {\n";
		$return_string .= "	echo '  <tr>\n";

		foreach($this->columns as $v) {
			if($v['tipo']['blob'])
				$val = "limit_chars(nl2br(\$row['".$v['nombre']."']))";
			elseif($v['tipo']['date'] or $v['tipo']['datetime'])
				$val = "humanize(\$row['".$v['nombre']."'])";
			elseif($v['tipo']['bool'])
				$val = "(\$row['".$v['nombre']."'] ? 'Yes' : 'No')";
			else
				$val = "\$row['".$v['nombre']."']";

			$return_string .= "    <td>' . $val . '</td>\n";
		}
		$return_string .= "    <td><a href=\"{$this->table['crud_page']}?{$this->table['id_key']}=' . \$row['{$this->table['id_key']}'] . '\">Edit</a></td>
    <td><a href=\"{$this->table['crud_page']}?delete=1&amp;{$this->table['id_key']}=' . \$row['{$this->table['id_key']}'] . '\" onclick=\"return confirm(\'Are you sure?\')\">Delete</a></td>
  </tr>';\n";
		$return_string .= "}\n\n";
		$return_string .= "echo '</table>

<p><a href=\"{$this->table['crud_page']}\">New entry</a></p>';

include('../inc.paging.php');

print_footer();
?>";

		return $return_string;
	}

	function crud_page() {
		$return_string = "<?php
include('../inc.functions.php');\n\n";

		$return_string .= "if (isset(\$_GET['delete'])) {
	mysql_query(\"DELETE FROM `{$this->table['name']}` WHERE `{$this->table['id_key']}` = '\$_GET[{$this->table['id_key']}]}'\");
	\$msg = (mysql_affected_rows() ? 'Row deleted.' : 'Nothing deleted.');
	header('Location: {$this->table['list_page']}?msg='.\$msg);
}

\${$this->table['id_key']} = (isset(\$_GET['{$this->table['id_key']}']) ? \$_GET['{$this->table['id_key']}'] : 0);
\$action = (\${$this->table['id_key']} ? 'Edit' : 'Add new');\n\n";

		$column_array = array();

		$return_string .= "if (isset(\$_POST['submitted'])) {
	foreach(\$_POST AS \$key => \$value) { \$_POST[\$key] = mysql_real_escape_string(\$value); }\n";
		$insert = "REPLACE INTO `{$this->table['name']}` (";
		$counter = 0;
		foreach($this->columns as $v) {
			$insert .= "`$v[nombre]`" ;
			if ($counter < count($this->columns) - 1)
				$insert .= ", ";
			$counter++;
		}
		$insert .= ") VALUES (\${$this->table['id_key']}, ";

		$counter = 0;
		foreach ($this->columns as $v) {
			if ($v['nombre'] != $this->table['id_key']) {
				$field = $v['nombre'];
				$val = $this->_parse($field, $v['tipo']);
				$insert .= "'$val'";
				if ($counter < count($this->columns) - 2)
					$insert .= ", ";
				$counter++;
			}
		}
		$insert .= ');';

		$return_string .= "	\$sql = \"$insert\";
	mysql_query(\$sql) or die(mysql_error());
	\$msg = (mysql_affected_rows()) ? 'Edited row.' : 'Nothing changed.';
	header('Location: {$this->table['list_page']}?msg='.\$msg);
}

print_header(\"\$action {$this->table['name']}\");

\$row = mysql_fetch_array ( mysql_query(\"SELECT * FROM `{$this->table['name']}` WHERE `{$this->table['id_key']}` = '\${$this->table['id_key']}' \"));
?>\n";

$return_string .= $this->_build_form($this->columns, 'Add / Edit') . '
<?
print_footer();
?>';

		return $return_string;
	}

	function session_auth() {
		$return_string = "<?php
include('inc.functions.php');
\$msg = \$_GET['msg'];

if (isset(\$_POST['user']) && isset(\$_POST['pass'])) {
	if ((strlen(\$_POST['user']) > 0) and (strlen(\$_POST['pass']) > 0)
	  and (\$login[\$_POST['user']] == \$_POST['pass'])) {
		\$_SESSION['user_logged_in'] = true;
		header('Location: {$this->table['list_page']}?msg=Logged in.');
		exit;
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
$return_string .= '<form action="" method="post">
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

	function search_page() {
		$return_string = $this->_build_form($this->columns, 'Search', 'get', '_GET');
		$return_string .= "\n\n<?php\n";

		$return_string .= '$opts = array(';
		$cols = '';
		foreach($this->columns as $col) {
			$cols .= "'{$col['nombre']}_opts', ";
		}
		$return_string .= substr($cols, 0, -2) . ");\n"
. '/* Sorround "contains" search term between %% */
foreach ($opts as $o) {
	if (isset($_GET[$o]) && $_GET[$o] == \'like\') {
		$v = substr($o, 0, -5);
		$_GET[$v] = \'%\' . $_GET[$v] . \'%\';
	}
}'."\n\n";
		foreach($this->columns as $col) {
			$return_string .= "if (search_by('{$col['nombre']}'))
	\$conds .= \" AND {$col['nombre']} {\$_GET['{$col['nombre']}_opts']} '{\$_GET['{$col['nombre']}']}'\";\n";
		}

		return $return_string . "?>";
	}

	function _build_form($cols, $submit, $method = 'post', $value = 'row') {
		$is_search = ($submit == 'Search');

		$legend = $submit;
		if ($is_search)
			$legend = "<a href=\"#\" onclick=\"$('#search-form').slideToggle()\">$legend</a>";

		$res = '<form action="" method="'.$method.'">
<fieldset>
<legend>' . $legend . '</legend>
<div' . ($is_search ? ' id="search-form" style="display:none"' : '') . '>
<ul>
';
		foreach ($cols as $col)
			$res .= $this->_form_input($col, $value, $is_search);

		$res .= '</ul>
<p><input type="hidden" value="1" name="submitted" />
  <input type="submit" value="'.$submit.'" /></p>
</div>
</fieldset>
</form>';
		return $res;
	}

	function _form_input($col, $value, $is_search = false) {
		if ($col['nombre'] != $this->table['id_key']) {

		$text = '  <li><label><span>' . $this->_title($col['nombre']) . ":</span>\n";
		if ($is_search)
			$text .= "    <?= search_options('".$col['nombre']."', \$_GET['".$col['nombre']."_opts']) ?></label>\n";
		$text .= '    ';

		/* Takes value either from $_GET['id'] or from $row['id'] */
		$val = '$'.$value.'[\''.$col['nombre'].'\']';

		if ($col['tipo']['bool'])
			$text .= '<input type="checkbox" name="'.$col['nombre'].'" value="1" <?= ('.$val.' == 1 ? \'checked="checked"\' : \'\') ?> />';
		elseif ($col['tipo']['date'])
			$text .= '<?= input_date(\''.$col['nombre'].'\', '.$val.') ?>';
		elseif ($col['tipo']['datetime'])
			$text .= '<?= input_datetime(\''.$col['nombre'].'\', '.$val.') ?>';
		elseif ($col['tipo']['blob'])
			$text .= '<textarea name="'.$col['nombre'].'" cols="40" rows="10"><?= (isset('.$val.') ? stripslashes('.$val.') : \'\') ?></textarea>';
		else
			$text .= '<input type="text" name="'.$col['nombre'].'" value="<?= (isset('.$val.') ? stripslashes('.$val.') : \'\') ?>" />';

		if (!$is_search) $text .= '</label>'; /* Could be closed after search_options */
		return $text . "</li>\n";
		} /* If not id column */
	}

	/* Merge split form data into single (SQL) data */
	function _parse($field, $type) {
		if ($type['date']) {
			$day  = $field . '_day';
			$mth  = $field . '_mth';
			$year = $field . '_year';
			$val = "\$_POST[$year]-\$_POST[$mth]-\$_POST[$day]";
		} elseif ($type['datetime']) {
			$seg  = $field . '_seg';
			$min  = $field . '_min';
			$hour = $field . '_hour';
			$day  = $field . '_day';
			$mth  = $field . '_mth';
			$year = $field . '_year';
			$val = "\$_POST[$year]-\$_POST[$mth]-\$_POST[$day] \$_POST[$hour]:\$_POST[$min]:\$_POST[$seg]";
		} else {
			$val = "\$_POST[$field]";
		}
		return $val;
	}

	function _title($name) {
		return ucwords(str_replace('_', ' ', trim($name)));
	}
}
?>
