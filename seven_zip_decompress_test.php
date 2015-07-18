<?php
// use ;



error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", 10);



try{
	
    // step 1. include class path
    require_once "Core/SevenZipAdvisor.php";
   
    // step 2. add options 
    $options =array(
        "filePath" => dirname( __FILE__ )."/test/",
        "file_from" => 'seven-zip-with-password.7z', 
        "password" => "password",  // this optional 
    );
    // step 3. pass parameters ; Shows files the file activity:
    print \Core\SevenZipAdvisor::get() 
            -> init_options($options) ->decompress() . " file(s) in archive\n";

	
}catch(Exception $e){
			var_dump($e->getMessage());
}

