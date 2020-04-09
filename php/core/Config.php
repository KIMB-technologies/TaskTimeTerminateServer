<?php
class Config {

	private const DEFAULT_CONF = '{"sleep":60,"savedir":"~/.tasktimeterminate","timezone":"Europe\/Berlin"}';
	private const DEFAULT_SLEEP = 60;
	private const DEFAULT_SAVEDIR = '~/.tasktimeterminate';
	private const DEFAULT_TIMEZONE = 'Europe/Berlin';

	private static ?Config $instance = null;
	private static bool $statusSetup = false;

	private int $sleeptime;
	private string $savedir;

	public function __construct() {
		// create default config.json, if non exists
		if( !is_file( __DIR__ . '/../config.json' ) ){
			file_put_contents(__DIR__ . '/../config.json', self::DEFAULT_CONF);
		}
		// load config json
		$json = new JSONReader( 'config', false, __DIR__ . '/../');

		// check for storage dir
		if( !$json->isValue(['savedir'])){
			$json->setValue(['savedir'], self::DEFAULT_SAVEDIR);
		}
		$this->savedir = self::parsePath($json->getValue(['savedir']));
		if( !is_dir($this->savedir) ){
			if( !mkdir($this->savedir, 0740 , true) ){
				die('Unable to create storage directory at "'. $this->savedir .'"');
			}
		}
		if( !is_writable($this->savedir) ){
			die('Storage directory at "'. $this->savedir .'" is not writeable');
		}

		// load sleeptime
		$this->sleeptime = $json->getValue(['sleep']);
		if( !is_numeric($this->sleeptime) ){
			$this->sleeptime = self::DEFAULT_SLEEP;
		}

		//date timezone
		if( !$json->isValue(['timezone'])){
			$json->setValue(['timezone'], self::DEFAULT_TIMEZONE);
		}
		date_default_timezone_set( $json->getValue(['timezone']) );

		//INI Setup
		ini_set('memory_limit','256M');

		// close JSONReader
		unset($json);
	}

	public static function init(){
		if(self::$instance == null){
			self::$instance = new Config();
		}
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * */

	public static function getSleepTime() : int {
		self::init();
		return self::$instance->sleeptime;
	}

	public static function getStorageReader(string $name) : JSONReader {
		self::init();
		return new JSONReader($name, false, self::$instance->savedir);
	}

	public static function getStorageDir() : string {
		self::init();
		return self::$instance->savedir;
	}

	public static function getRecordStatus(bool $useManager = true) : bool {
		$c = self::getStorageReader('config');
		if( $useManager ){
			ReaderManager::addReader($c);
		}
		if( !self::$statusSetup && !$c->isValue(['status']) ){
			$c->setValue(['status'], true);
			self::$statusSetup = true;
		}
		return $c->getValue(['status']);
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * */

	private static function parsePath(string $path) : string {
		$path = ltrim($path);
		if( Utilities::getOS() === Utilities::OS_WIN){
			if( $path[0] === '~' ){ // home shortcut
				$home = getenv('USERPROFILE');
				return $home . '/AppData/Roaming/' . substr($path, 1);
			}
			else if( $path[0] !== '/' && preg_match( '/^[A-Z]:/', $path[0]) !== 1 ){ // relative path
				return __DIR__ . '/../' . $path;
			}
			else { // absolute path
				return $path;
			}
		}
		else{
			return self::parseUnixPath($path);
		}
	}

	private static function parseUnixPath(string $path) : string {
		if( $path[0] === '~' ){ // home shortcut
			$home = posix_getpwuid(posix_getuid())['dir'];
			return $home . '/' . substr($path, 1);
		}
		else if( $path[0] !== '/' ){ // relative path (as relative to project root)
			return __DIR__ . '/../' . $path;
		}
		else { // absolute path
			return $path;
		}
	}
}
?>