<?

function find_text($text, $delimit_start = '`', $delimit_end = '`') {
	$start = strpos($text, $delimit_start);
	if ($start === false) return false;
	
	$end = strpos( substr($text, $start + 1), $delimit_end); 
	if ($end === false) return false;
	
	return substr ( $text, $start + 1, $end);
}


class Scaffold {

    public $table = array();
    public $download = false;

    function Scaffold($table){
        $this->table = $table;
	}
 


    function listtable(){
	
		$return_string = '';
		$column_array = array();
		$return_string .= "<? \n";
		
		if ($this->table['include'] != '')  $return_string .= "include('{$this->table['include']}'); \n";
		
        $return_string .= "echo \"<table border=1 >\"; \n";
        $return_string .= "echo \"<tr>\"; \n";
        foreach($this->table AS $key => $value) {
            if (is_array($value)) {
				$column = $key ;
				$column_array[] = $key;
            	$return_string .= "echo \"<td><b>". $this->title($column) ."</b></td>\"; \n";
			}
        }
        $return_string .= "echo \"</tr>\"; \n";
		
		$return_string .= "\$result = mysql_query(\"SELECT * FROM `{$this->table['name']}`\") or trigger_error(mysql_error()); \n";

        $return_string .= "while(\$row = mysql_fetch_array(\$result)){ \n";
		
			$return_string .= "foreach(\$row AS \$key => \$value) { \$row[\$key] = stripslashes(\$value); } \n";
		
        	$return_string .= "echo \"<tr>\";  \n";
		
            foreach($column_array as $value){
                    $return_string .= "echo \"<td valign='top'>\" . nl2br( \$row['" . $value . "']) . \"</td>\";  \n";
            }
            $return_string .= "echo \"<td valign='top'><a href={$this->table['edit_page']}?{$this->table['id_key']}={\$row['{$this->table['id_key']}']}>Edit</a></td><td><a href={$this->table['delete_page']}?{$this->table['id_key']}={\$row['{$this->table['id_key']}']}>Delete</a></td> \"; \n";
            
            $return_string .= "echo \"</tr>\"; \n";
        
		$return_string .= "} \n";

        $return_string .= "echo \"</table>\"; \n";
        $return_string .= "echo \"<a href={$this->table['new_page']}>New Row</a>\"; \n";
		
		$return_string .= "?>";
		
		return $return_string;
    }

    function newrow(){
	
		$return_string = '';
		$return_string .= "<? \n";
		if ($this->table['include'] != '')  $return_string .= "include('{$this->table['include']}'); \n";

		$column_array = array();
		$text = '';
		
        foreach($this->table AS $key => $value) {
            if (is_array($value)) {
				$column = $key ;
				if($column != $this->table['id_key'] ){
					$column_array[] = $key;
					if($value['blob'] == 1){
						$text .= $this->html_chars("<p><b>" . $this->title($column) . ":</b><br /><textarea name='$column'></textarea> \n");
					}
					else {
						$text .= "<p><b>" . $this->title($column) . ":</b><br /><input type='text' name='$column'/> \n";
					}
				}
			}
        }
		
						
		$return_string .= "if (isset(\$_POST['submitted'])) { \n";
		$return_string .= "foreach(\$_POST AS \$key => \$value) { \$_POST[\$key] = mysql_real_escape_string(\$value); } \n";
        $insert = "INSERT INTO `{$this->table['name']}` (";
		$counter = 0;
        foreach($column_array as $value){
            $insert .= " `$value` " ;
			if ($counter < count($column_array) -1 ) $insert .= ", ";
			
			$counter++;
        }
		$insert .= " ) VALUES( ";

		$counter = 0;
        foreach($column_array as $value){
            $insert .= " '{\$_POST['$value']}' " ;
			if ($counter < count($column_array) -1 ) $insert .= ", ";
			
			$counter++;
        }
		$insert .= " ) ";
		
		
		$return_string .= "\$sql = \"$insert\"; \n";
        $return_string .= "mysql_query(\$sql) or die(mysql_error()); \n";
        $return_string .= "echo \"Added row.<br />\"; \n";
        $return_string .= "echo \"<a href='{$this->table['list_page']}'>Back To Listing</a>\"; \n";
		$return_string .= "} \n";
		
		
        $return_string .= "?>\n\n";
		$return_string .= "<form action='' method='POST'> \n";
		$return_string .= $text;
        $return_string .= "<p><input type='submit' value='Add Row' /><input type='hidden' value='1' name='submitted' /> \n";
        $return_string .= "</form> \n";
		
		return $return_string;
    }



    function editrow(){
		$return_string = '';
		$return_string .= "<? \n";
		if ($this->table['include'] != '')  $return_string .= "include('{$this->table['include']}'); \n";

		$column_array = array();
		$text = '';
		
		$return_string .= "if (isset(\$_GET['{$this->table['id_key']}']) ) { \n";
		
			$return_string .= "\${$this->table['id_key']} = (int) \$_GET['{$this->table['id_key']}']; \n";
			
			
			foreach($this->table AS $key => $value) {
				if (is_array($value)) {
					$column = $key;
					if($column != $this->table['id_key'] ){
						$column_array[] = $column; 
						if($value['blob'] == 1){
							$text .= $this->html_chars("<p><b>" . $this->title($column) . ":</b><br /><textarea name='$column'><?= stripslashes(\$row['$column']) ?></textarea> \n");
						}
						else {
							$text .= "<p><b>" . $this->title($column) . ":</b><br /><input type='text' name='$column' value='<?= stripslashes(\$row['$column']) ?>' /> \n";
						}
					}
				}
			}
			

					
			$return_string .= "if (isset(\$_POST['submitted'])) { \n";
			$return_string .= "foreach(\$_POST AS \$key => \$value) { \$_POST[\$key] = mysql_real_escape_string(\$value); } \n";
			$insert = "UPDATE `{$this->table['name']}` SET ";
			$counter = 0;
			foreach($column_array as $value){
				$insert .= " `$value` =  '{\$_POST['$value']}' " ;
				if ($counter < count($column_array) -1 ) $insert .= ", ";
				
				$counter++;
			}
			$insert .= "  WHERE `{$this->table['id_key']}` = '\${$this->table['id_key']}' ";
	
	
			$return_string .= "\$sql = \"$insert\"; \n";
			$return_string .= "mysql_query(\$sql) or die(mysql_error()); \n";
			$return_string .= "echo (mysql_affected_rows()) ? \"Edited row.<br />\" : \"Nothing changed. <br />\"; \n";
			$return_string .= "echo \"<a href='{$this->table['list_page']}'>Back To Listing</a>\"; \n";
			
			// get the new updated row
		$return_string .= "} \n";
		$return_string .= "\$row = mysql_fetch_array ( mysql_query(\"SELECT * FROM `{$this->table['name']}` WHERE `{$this->table['id_key']}` = '\${$this->table['id_key']}' \")); \n";

			
		$return_string .= "?>\n\n";
		$return_string .= "<form action='' method='POST'> \n";
		$return_string .= $text;
		$return_string .= "<p><input type='submit' value='Edit Row' /><input type='hidden' value='1' name='submitted' /> \n";
		$return_string .= "</form> \n";
		
		$return_string .= "<? } ?> \n";
		
		return $return_string;
    }

 

    function deleterow(){
		$return_string = '';
		$return_string .= "<? \n";
		if ($this->table['include'] != '')  $return_string .= "include('{$this->table['include']}'); \n";

		$return_string .= "\${$this->table['id_key']} = (int) \$_GET['{$this->table['id_key']}']; \n";
        $return_string .= "mysql_query(\"DELETE FROM `{$this->table['name']}` WHERE `{$this->table['id_key']}` = '\${$this->table['id_key']}' \") ; \n";
		$return_string .= "echo (mysql_affected_rows()) ? \"Row deleted.<br /> \" : \"Nothing deleted.<br /> \"; \n" ;
		$return_string .= "?> \n\n";
        $return_string .= "<a href='{$this->table['list_page']}'>Back To Listing</a>";
		
		return $return_string;
    }
	
	function title($name) {
		return ucwords(str_replace("_", " ", trim($name)));
	}
	
	
	function html_chars ($var) {
		return ($this->download) ? $var : htmlspecialchars($var);
	}
	
}















class createZip  {  

    public $compressedData = array();
    public $centralDirectory = array(); // central directory   
    public $endOfCentralDirectory = "\x50\x4b\x05\x06\x00\x00\x00\x00"; //end of Central directory record
    public $oldOffset = 0;

    /**
     * Function to create the directory where the file(s) will be unzipped
     *
     * @param $directoryName string
     *
     */
    
    public function addDirectory($directoryName) {
        $directoryName = str_replace("\\", "/", $directoryName);  

        $feedArrayRow = "\x50\x4b\x03\x04";
        $feedArrayRow .= "\x0a\x00";    
        $feedArrayRow .= "\x00\x00";    
        $feedArrayRow .= "\x00\x00";    
        $feedArrayRow .= "\x00\x00\x00\x00";

        $feedArrayRow .= pack("V",0);
        $feedArrayRow .= pack("V",0);
        $feedArrayRow .= pack("V",0);
        $feedArrayRow .= pack("v", strlen($directoryName) );
        $feedArrayRow .= pack("v", 0 );
        $feedArrayRow .= $directoryName;  

        $feedArrayRow .= pack("V",0);
        $feedArrayRow .= pack("V",0);
        $feedArrayRow .= pack("V",0);

        $this -> compressedData[] = $feedArrayRow;
        
        $newOffset = strlen(implode("", $this->compressedData));

        $addCentralRecord = "\x50\x4b\x01\x02";
        $addCentralRecord .="\x00\x00";    
        $addCentralRecord .="\x0a\x00";    
        $addCentralRecord .="\x00\x00";    
        $addCentralRecord .="\x00\x00";    
        $addCentralRecord .="\x00\x00\x00\x00";
        $addCentralRecord .= pack("V",0);
        $addCentralRecord .= pack("V",0);
        $addCentralRecord .= pack("V",0);
        $addCentralRecord .= pack("v", strlen($directoryName) );
        $addCentralRecord .= pack("v", 0 );
        $addCentralRecord .= pack("v", 0 );
        $addCentralRecord .= pack("v", 0 );
        $addCentralRecord .= pack("v", 0 );
        $ext = "\x00\x00\x10\x00";
        $ext = "\xff\xff\xff\xff";  
        $addCentralRecord .= pack("V", 16 );

        $addCentralRecord .= pack("V", $this -> oldOffset );
        $this -> oldOffset = $newOffset;

        $addCentralRecord .= $directoryName;  

        $this -> centralDirectory[] = $addCentralRecord;  
    }    
    
    /**
     * Function to add file(s) to the specified directory in the archive
     *
     * @param $directoryName string
     *
     */
    
    public function addFile($data, $directoryName)   {

        $directoryName = str_replace("\\", "/", $directoryName);  
    
        $feedArrayRow = "\x50\x4b\x03\x04";
        $feedArrayRow .= "\x14\x00";    
        $feedArrayRow .= "\x00\x00";    
        $feedArrayRow .= "\x08\x00";    
        $feedArrayRow .= "\x00\x00\x00\x00";

        $uncompressedLength = strlen($data);  
        $compression = crc32($data);  
        $gzCompressedData = gzcompress($data);  
        $gzCompressedData = substr( substr($gzCompressedData, 0, strlen($gzCompressedData) - 4), 2);
        $compressedLength = strlen($gzCompressedData);  
        $feedArrayRow .= pack("V",$compression);
        $feedArrayRow .= pack("V",$compressedLength);
        $feedArrayRow .= pack("V",$uncompressedLength);
        $feedArrayRow .= pack("v", strlen($directoryName) );
        $feedArrayRow .= pack("v", 0 );
        $feedArrayRow .= $directoryName;  

        $feedArrayRow .= $gzCompressedData;  

        $feedArrayRow .= pack("V",$compression);
        $feedArrayRow .= pack("V",$compressedLength);
        $feedArrayRow .= pack("V",$uncompressedLength);

        $this -> compressedData[] = $feedArrayRow;

        $newOffset = strlen(implode("", $this->compressedData));

        $addCentralRecord = "\x50\x4b\x01\x02";
        $addCentralRecord .="\x00\x00";    
        $addCentralRecord .="\x14\x00";    
        $addCentralRecord .="\x00\x00";    
        $addCentralRecord .="\x08\x00";    
        $addCentralRecord .="\x00\x00\x00\x00";
        $addCentralRecord .= pack("V",$compression);
        $addCentralRecord .= pack("V",$compressedLength);
        $addCentralRecord .= pack("V",$uncompressedLength);
        $addCentralRecord .= pack("v", strlen($directoryName) );
        $addCentralRecord .= pack("v", 0 );
        $addCentralRecord .= pack("v", 0 );
        $addCentralRecord .= pack("v", 0 );
        $addCentralRecord .= pack("v", 0 );
        $addCentralRecord .= pack("V", 32 );

        $addCentralRecord .= pack("V", $this -> oldOffset );
        $this -> oldOffset = $newOffset;

        $addCentralRecord .= $directoryName;  

        $this -> centralDirectory[] = $addCentralRecord;  
    }

    /**
     * Fucntion to return the zip file
     *
     * @return zipfile (archive)
     */

    public function getZippedfile() {

        $data = implode("", $this -> compressedData);  
        $controlDirectory = implode("", $this -> centralDirectory);  

        return   
            $data.  
            $controlDirectory.  
            $this -> endOfCentralDirectory.  
            pack("v", sizeof($this -> centralDirectory)).     
            pack("v", sizeof($this -> centralDirectory)).     
            pack("V", strlen($controlDirectory)).             
            pack("V", strlen($data)).                
            "\x00\x00";                             
    }

    /**
     *
     * Function to force the download of the archive as soon as it is created
     *
     * @param archiveName string - name of the created archive file
     */

    public function forceDownload($archiveName) {
        $headerInfo = '';
        
        if(ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }

        // Security checks
        if( $archiveName == "" ) {
            echo "<html><title>Public Photo Directory - Download </title><body><BR><B>ERROR:</B> The download file was NOT SPECIFIED.</body></html>";
            exit;
        }
        elseif ( ! file_exists( $archiveName ) ) {
            echo "<html><title>Public Photo Directory - Download </title><body><BR><B>ERROR:</B> File not found.</body></html>";
            exit;
        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=".basename($archiveName).";" );
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".filesize($archiveName));
        readfile("$archiveName");
        
     }

}






?>