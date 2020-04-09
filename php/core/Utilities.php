<?php
/**
 * Class with useful functions.
 */
class Utilities {

	const VERSION = 'v0.9.5 beta';

	/**
	 * OS Consts
	 */
	const OS_MAC = "mac";
	const OS_WIN = "win";
	const OS_LINUX = "lin";
	const OS_OTHER = "oth";

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

	public static function getOS() : string {
		$os = php_uname('s');
		if( stripos($os, 'darwin') !== false ){
			return self::OS_MAC;
		}
		else if( stripos($os, 'linux') !== false ){
			return self::OS_LINUX;
		}
		else if( stripos($os, 'windows') !== false ){
			return self::OS_WIN;
		}
		else{
			return self::OS_OTHER;
		}
	}
}

?>
