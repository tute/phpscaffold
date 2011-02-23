<?php
/* List directory $dir */
function list_dir($dir) {
	echo '<ul>';
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if (substr($file, 0, 1) != '.')
				echo "  <li><a href=\"tmp/$file/\">$file</a></li>\n";
		}
		closedir($handle);
	}
	echo '</ul>';
}

/* Get rid of comillas around text */
function find_text($text, $delimit_start = '`', $delimit_end = '`') {
	$start = strpos($text, $delimit_start);
	if ($start === false) return false;

	$end = strpos(substr($text, $start + 1), $delimit_end);
	if ($end === false) return false;

	return substr($text, $start + 1, $end);
}

/* Given a table SQL definition, search it's primary key */
function get_primary_key($text) {
	$key  = '';
	$text = explode("\n", $text);
	foreach ($text as $line) {
		if (preg_match('/PRIMARY KEY/', $line)) {
			/* Remove parenthesis */
			$key = explode('(', $line);
			$key = explode(')', $key[1]);
			$key = substr($key[0], 0, -1);
			/* Remove surrounding comillas? */
			if ($key[0] == '`') $key = substr($key,1);
		}
	}
	return ($key != '' ? $key : 'id');
}

function pr($arr) {
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}
