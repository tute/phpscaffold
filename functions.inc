<?php
/* Common layout for showing files */
function files_textarea_head($file) {
	return "\n\n<h3><a href=\"#\" onclick=\"$('#{$file}').slideToggle()\">" . ucwords($file) . '</a></h3>
<textarea rows="30" cols="80" class="textarea" style="display:none" id="'.$file.'">';
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
	return $key;
}

function pr($arr) {
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}
?>
