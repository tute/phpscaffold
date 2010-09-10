<?php
$page = (isset($_GET['page']) ? $_GET['page'] : 1);
$start = ($page-1) * $per_page;

$num_results = mysql_result(mysql_query($count_sql), 0);
$num_pages = ceil($num_results / $per_page);

/* Mantain search and sorting parameters */
$pars = explode('&', $_SERVER['QUERY_STRING']);
$res = array();
foreach($pars as $n => $par) {
	$p = explode('=', $par);
	if ($p[0] != 'page')
		array_push($res, join('=', $p));
}
$pars = join("&amp;", $res);

echo '<p>Pages: ';
echo ($page-1 > 0 ? '<a href="?'.$pars.'&amp;page='.($page-1).'">Previous</a>' : 'Previous') . ' | ';
if ($num_pages <= 25) {
	options_range(1, $num_pages);
} else {
	if ($page <= 5 or ($page >= $num_pages-4 and !($page > $num_pages))) {
		options_range(1,5);
		echo "... |\n";
		options_range($num_pages-4, $num_pages);
	} elseif (5 < $page and $page <= $num_pages-4) {
		options_range(1,5);
		echo "... |\n";
		options_range(max(6,$page-3), min($page+3, $num_pages-5));
		echo "... |\n";
		options_range($num_pages-4, $num_pages);
	}
}
echo ($page+1 <= $num_pages ? '<a href="?'.$pars.'&amp;page='.($page+1).'">Next</a>' : 'Next');
echo "</p>

<p style=\"text-align:center;font-size:.9em\">(Showing entries $start to "
  . min($start+$per_page, $num_results) . " out of $num_results.)</p>\n\n";

function options_range($start, $end) {
	global $pars;
	for ($i=$start; $i <= $end; $i++)
		echo ((isset($_GET['page']) and $i == $_GET['page']) ? "<strong>$i</strong>" : "<a href=\"?$pars&amp;page=$i\">$i</a>") . " |\n";
}
?>
