<?php
/**
 *
 * @author Varun Jose
 * @author Varun Jose, WEB Developer, Flixmedia
 * @copyright stockwave-estimator
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

/*
 I  How to use this for unzip
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

II  How to use this for ZIP
    // step 1. include class path
    require_once "Core/SevenZipAdvisor.php";
   
    // step 2. add options 
    $options =array(
        "filePath" => dirname( __FILE__ )."/test/",
        "file_to" => "file_to.7z",  /* this option is must if you compress a folder 
        "archivePath" => dirname( __FILE__ )."/test/seven-zip-with-password-pass",  // this option is must if you compress a folder
        "action" => "compress",  // this option is must if you compress a folder 
        "password" => "password",  // this optional 
    );
    // step 3. pass parameters ; Shows files the file activity:
    print  \Core\SevenZipAdvisor::get() 
                -> init_options($options) ->compress() . " file(s) in archive\n";


*/

namespace Core;
use \Exception;

class SevenZipAdvisor
{
    public static $instance;
    protected $_action ="decompress";
    protected $_file_from;
    protected $_file_to;
    protected $_executablePath = NULL;

    protected $_os_processor;
    protected $_os_using;
    protected $_this_file_path;

    protected $_archivePath;
    protected $_filePath;
    protected $_password;
    protected $_allowed_options =array(
        'executablePath', 
        'action', 
        'file_from', 
        'file_to', 
        'executablePath', 
        'archivePath',
        'filePath',
        'password',
    );


    public static function get() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

	public function init_options(array $options = null ) {


        if (!is_array($options)) {
            $options = array();
        }
        // Get the options.
        if ($options) {
            foreach ($options as $key => $value) {
                if (in_array($key, $this -> _allowed_options)) {
                    if (!(is_string($value) && strlen($value))) {
                        throw new Exception("The '$key' option must be a non-empty string");
                    }
                    $this_key = "_$key";
                    $this-> $this_key = $value;
                }
                else {
                    throw new Exception("Unknown option '$key'");
                }
            }
        }else{
                    throw new Exception("Please add options");

        }

        if (!is_string($this->_file_from) && $this -> _action !='compress') {
            throw new Exception(gettype($file) . ' is not a legal file argument type');
        }
        if (!strlen($this->_file_from) && $this -> _action !='compress') {
            throw new Exception('Missing file argument');
        }
        
        if (!is_file( trim($this -> _getDeCompressingFromFilePath()) ) && $this -> _action !='compress') {
            throw new Exception("File '".$this->_getDeCompressingFromFilePath()."' not found"); // TODO: support create mode
        }
        $this -> _this_file_path = dirname( __FILE__ );

        // settings for running the script
        $this-> _setExecutingPath();

        //checking functionality availability 
        $this-> checkExecutable();

        return self::get();
    }

    public function compress()
    {
        $command = $this->_getExecutingPath()
                 . ' a'
                 . ' -r'. $this->_getPasswordParam()
                 . ' ' . $this->_getCompressingIntoFilePath()
                 . ' ' . $this->_getCompressingFromFolder() ;

        $ret = shell_exec($command);

        if (strpos($ret, 'Everything is Ok')!==false) { 
            $files = scandir($this->_getCompressingFromFolder());
            return count($files) - 2;;
        } else {
            throw new Exception("Something went wrong see:: $ret ($command) ");
        }
    }


    public function decompress()
    {
        $save_path = $this->_getDeCompressingToFolder();
        $command = $this->_getExecutingPath()
                 . ' x'
                 . ' -y'
                 . $this->_getPasswordParam()
                 . ' -o' . $save_path
                 . $this->_getDeCompressingFromFilePath();

        $ret = shell_exec($command);

        if (strpos($ret, 'Everything is Ok')!==false) { 
            $files = scandir($save_path);
            return count($files) - 2;;
        } else {  
			throw new Exception("Something went wrong see:: $ret \n $command");
        }
    }

    protected function _getPasswordParam()
    {
        return !empty($this->_password) ? ' -p' . escapeshellarg($this->_password) : "";
    }

    protected function _setExecutingPath()
    {
        $this -> _os_processor = PHP_INT_SIZE==8 ? 'x64' : 'x32' ;
        $this -> _os_using = ( (strtolower(substr(PHP_OS, 0, 3)) === 'win') ? 'windows' : 
                        (strtolower(substr(PHP_OS, 0, 3)) === 'mac') ? 'mac' : 'linux' );

        $this -> _executablePath = $this -> _os_using == 'windows'
               ? $this -> _this_file_path .DS. '..' .DS. 'bin' .DS. $this-> _os_processor .DS. '7zip.exe'
               : "7za" ;

    }

    public function _getExecutingPath()
    {
        return $this -> _executablePath;
    }


    protected function _getCompressingIntoFilePath()
    {
    	$path = '';
    	$path .= !empty($this->_filePath) && !empty($this->_file_to) ? ' ' . $this->_filePath.$this->_file_to : "";
        if(!$path)
            throw new Exception("Please provide a valid file path..", 1);
        
        //delete previous file with same name
        @unlink(trim($path)); 
        
        return $path;
    }

    protected function _getDeCompressingFromFilePath()
    {
    	$path = '';
    	$path .= !empty($this->_filePath) && !empty($this->_file_from) ? ' ' . $this->_filePath.$this->_file_from : "";
        if(!$path && $this -> _action !='compress')
            throw new Exception("Please provide a valid file path..", 1);
            
        return $path;
    }

    protected function _getCompressingFromFolder()
    {
    	$path = !empty($this->_archivePath) ? $this->_archivePath : "";
        if(!$path)
            throw new Exception("Please provide a valid archive path..", 1);
            
        return $path;
    }

    protected function _getDeCompressingToFolder()
    {
    	$path = !empty($this->_archivePath) ? $this->_archivePath : $this -> _createTempArchiveFolder();
        if(!$path)
            throw new Exception("Please provide a valid archive path..", 1);
            
        return $path;
    }

    protected function _createTempArchiveFolder(){
        $this_folder =  $this->_filePath . basename($this->_file_from, '.7z');
        if ( ! is_dir($this_folder))
        {
            if ( ! @mkdir($this_folder, 0777))
            {
               throw new Exception("Cannot create the folder..");
            }

        }
        else
        {
            $dir = opendir($this_folder);
            while (false !== ($file = readdir($dir)))
            {
                @unlink($this_folder . DIRECTORY_SEPARATOR . $file);
            }
        }
        return $this_folder;
    }

    protected function checkExecutable()
    {
        if (!function_exists('shell_exec')) {
			throw new Exception(" shell_exec function is not available");
        }
        $ret = shell_exec($this->_getExecutingPath());
        if (strpos($ret, "7-Zip") === false) {
			throw new Exception(" Please install 7-Zip service and make it available under $this->_getExecutingPath()");
        } else {
            return true;
        }
    }

}