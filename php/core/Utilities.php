<?php
/** 
 * TaskTimeTerminate Sync-Server
 * https://github.com/KIMB-technologies/TaskTimeTerminate
 * 
 * (c) 2020 KIMB-technologies 
 * https://github.com/KIMB-technologies/
 * 
 * released under the terms of GNU Public License Version 3
 * https://www.gnu.org/licenses/gpl-3.0.txt
 */
defined( 'TaskTimeTerminate' ) or die('Invalid Endpoint!');

/**
 * Class with useful functions.
 */
class Utilities {

	/**
	 * Possible chars for:
	 */
	const ID = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890';
	const CODE = 'abcdefghijklmnopqrstuvwxyz01234567890';
	const CAPTCHA = 'abcdefghjkmnpqrstuvwxyz23456789';

	/**
	 * Checks if a String is a valid file-name (file only, no dirs)
	 * @param $name the filename
	 */
	public static function checkFileName($name){
		return is_string($name) && preg_match( '/^[A-Za-z0-9]+$/', $name ) === 1;
	}

	/**
	 * Does some optimizing on the give string to output it for html display
	 * 	nl2br and htmlentities
	 * @param $cont the string to optimized
	 */
	public static function optimizeOutputString($cont){
		return nl2br( htmlentities( $cont, ENT_COMPAT | ENT_HTML401, 'UTF-8' ));
	}

	/**
	 * Validates a string by the given rex and cuts lenght
	 * 	**no boolean return**
	 * @param $s the string to check
	 * @param $reg the regular expressions (/[^a-z]/ to allow only small latin letters)
	 * @param $len the maximum lenght
	 * @return the clean string (empty, if other input than string or only dirty characters)
	 */
	public static function validateInput($s, $reg, $len){
		if( !is_string($s) ){
			return '';
		}
		return substr(trim(preg_replace( $reg, '' , $s )), 0, $len);
	}

	/**
	 * Generates a random code
	 * @param $len the code lenght
	 * @param $chars the chars to choose of (string)
	 * 	e.g. consts POLL_ID, ADMIN_CODE
	 */
	public static function randomCode( $len, $chars ){
		$r = '';
		$charAnz = strlen( $chars );
		for($i = 0; $i < $len; $i++){
			$r .= $chars[random_int(0, $charAnz-1)];
		}
		return $r;
	}

	public static function deleteDirRecursive(string $dir) : bool {
		$ok = true;
		$files = scandir($dir);
		foreach ($files as $file) {
			if($file !== '.' && $file !== '..'){
				if(is_dir($dir.'/'.$file)){
					$ok &= self::deleteDirRecursive($dir.'/'.$file);
				}
				else{
					$ok &= unlink($dir.'/'.$file);
				}
			}
		}
		return $ok && rmdir($dir);
	}

	public static function getBrowserOS() : string {
		$b = get_browser();
		return $b->browser . ' ' . $b->version . ' on ' . $b->platform;
	}

}

?>
