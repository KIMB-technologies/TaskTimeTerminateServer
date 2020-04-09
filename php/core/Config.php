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

class Config {

	private static bool $dateTimeSet = false;

	private const DEFAULT_TIMEZONE = 'Europe/Berlin';
	private const DEFAULT_DOMAIN = 'http://localhost:8080/';

	public function __construct() {
		if( !Config::$dateTimeSet ){
			date_default_timezone_set( empty($_ENV['CONF_TIMEZONE']) ? self::DEFAULT_TIMEZONE : $_ENV['CONF_TIMEZONE'] );
			Config::$dateTimeSet = true;
		}
	}

	public static function getBaseUrl() : string {
		return empty($_ENV['CONF_DOMAIN']) ? self::DEFAULT_DOMAIN : $_ENV['CONF_DOMAIN'];
	}
}
?>