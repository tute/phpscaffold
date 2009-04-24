<?
include("scaffold.php");


if (isset($_COOKIE['scaffold_info'])) {
	$data = trim($_COOKIE['sql']);
	$data_lines = explode("\n", $data);
	$table = array();
	
	


	
	$table['list_page'] = stripslashes($_COOKIE['list_page']);
	$table['edit_page'] = stripslashes($_COOKIE['edit_page']);
	$table['new_page'] = stripslashes($_COOKIE['new_page']);
	$table['delete_page'] = stripslashes($_COOKIE['delete_page']);
    $table['include'] = stripslashes($_COOKIE['include']);


	
	$table['id_key'] = trim($_COOKIE['id_key']);
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
		
		
		//print_r($table);
	
	}
	else {
		$message .= "Cannot find 'CREATE TABLE `table_name` ( '";
	}

}



if ($show_form) {

    $base = md5(rand(0,99999) + time());
    

    $s = new Scaffold($table);
    $s->download = true;
    
    /*file_put_contents( "temp/$base/{$table['list_page']}", $s->listtable() );
    file_put_contents( "temp/$base/{$table['new_page']}", $s->newrow() );
    file_put_contents( "temp/$base/{$table['edit_page']}", $s->editrow() );
    file_put_contents( "temp/$base/{$table['delete_page']}", $s->deleterow() );
    */
    
    
    $createZip = new createZip;
    $createZip -> addFile($s->listtable(), $table['list_page'] ); 
    $createZip -> addFile($s->newrow(), $table['new_page'] );
    $createZip -> addFile($s->editrow(), $table['edit_page'] );
    $createZip -> addFile($s->deleterow(), $table['delete_page'] );  
    
    $fileName = "temp/$base.zip"; 
    
    $fd = fopen ($fileName, "wb"); 
    $out = fwrite ($fd, $createZip -> getZippedfile() ); 
    fclose ($fd); 
    $createZip -> forceDownload($fileName); 
    
    //@unlink($fileName); 
}

else {

	header("Location: index.php");
}









?>