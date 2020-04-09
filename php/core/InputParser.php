<?php
class InputParser {

	private const TIME_INPUT_PREG = '/^((\+?(\d+h)?(\d+m)?)|(\d+:\d+))$/';

	private const CATEGORY_INPUT_PREG = '/^[A-Za-z\-]+$/';

	private const NAME_INPUT_PREG = '/^[A-Za-z0-9\_\-]+$/';

	private const DEVICE_NAME_PREG = '/^[A-Za-z0-9\-]+$/';

	public static function checkTimeInput(string $t) : bool {
		return !empty($t) && preg_match( self::TIME_INPUT_PREG, $t) === 1;
	}

	public static function checkCategoryInput(string $c) : bool {
		return !empty($c) && preg_match( self::CATEGORY_INPUT_PREG, $c) === 1;
	}

	public static function checkNameInput(string $n) : bool {
		return !empty($n) && preg_match( self::NAME_INPUT_PREG, $n) === 1;
	}

	public static function checkDeviceName(string $n) : bool {
		return !empty($n) && preg_match( self::DEVICE_NAME_PREG, $n) === 1;
	}

	public static function getEndTimestamp(string $t) : int {
		preg_match( self::TIME_INPUT_PREG, $t, $matches);
		if(isset($matches[5])){ // Gruppe 5, d.h. bis Uhrzeit (12:30)
			$plusOneDay = false;

			$hs = intval(substr($matches[5], 0, strpos($matches[5], ':'))); // user input hh:mm
			$mins = intval(substr($matches[5], strpos($matches[5], ':')+1));
			$hnow = intval(date("H")); // current hh:mm
			$minnow = intval(date("i"));

			if( $hs < $hnow ){ // check if time today or next tomorrow?
				$plusOneDay = true;
			}
			else if ($hs == $hnow){
				if( $mins < $minnow ){
					$plusOneDay = true;
				}
				else if( $mins == $minnow ){
					return time();
				}
			}

			$timestamp = strtotime($plusOneDay ? "tomorrow" : "today") + 3600 * $hs + 60 * $mins;
		}
		else if( isset($matches[2]) ){ // Gruppe 2, d.h. Minuten- und/oder Stundenangabe
			$hs = 0;
			$mins = 0;
			if(isset($matches[3])){ // Gruppe 3, d.h. Stundenangabe
				$hs = intval(substr($matches[3], 0, -1));
			}
			if(isset($matches[4])){ // Gruppe 3, d.h. Minutenangabe
				$mins = intval(substr($matches[4], 0, -1));

			}
			$timestamp = time() + 3600 * $hs + 60 * $mins;
		}
		return $timestamp;
	}

}
?>