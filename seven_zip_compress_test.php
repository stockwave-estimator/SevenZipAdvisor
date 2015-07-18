<?php
// use ;



error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", 10);



try{
	include "Core/SevenZipAdvisor.php";
	
	$options =array(
	    "filePath" => dirname( __FILE__ )."/test/",
	    "file_to" => "file_to.7z",  /* this option is must if you compress a folder */
	    "archivePath" => dirname( __FILE__ )."/test/seven-zip-with-password-pass",  /* this option is must if you compress a folder */
	    "action" => "compress",  /* this option is must if you compress a folder */
	    "password" => "password",  /* this optional */
	);

	print  \Core\SevenZipAdvisor::get() 
				-> init_options($options) ->compress() . " file(s) in archive\n";

	
}catch(Exception $e){
			var_dump($e->getMessage());
}

