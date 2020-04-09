<?php
class CLIOutput {

	const BEGINEND = "============================================================";
	const MIDDLE =   "------------------------------------------------------------";
	const MOIN = "Welcome to TTT -- TaskTimeTerminate by KIMB-technologies ";

	const PAD_SPACING = 2;

	const RED = "\e[0;31m";
	const BLACK = "\e[0;30m";
	const GREEN = "\e[0;32m";
	const YELLOW = "\e[0;33m";
	const BLUE = "\e[0;34m";
	const WHITE = "\e[0;37m";
	const RESET = "\e[0;0m";

	public static function colorString($s, $color) : string {
		return $color . $s . self::RESET;
	}

	public function __construct() {
		$this->hello();
	}

	public function hello(){
		$this->print(array(
			self::BEGINEND,
			self::MOIN,
			self::MIDDLE
		));
	}

	public function print( array $s, ?string $color = null, int $ind = 0 ) : void {
		foreach( $s as $data ){
			if( is_array( $data ) ){
				$this->print($data, $color, $ind+1);
			}
			else{
				$this->echo($data, $color, $ind);
			}
		}
	}

	public function table(array $data) : void {
		$colsize = array();
		foreach( $data as $row ){
			foreach( $row as $cid => $col ){
				if( !isset($colsize[$cid])){
					$colsize[$cid] = strlen($cid);
				}
				$colsize[$cid] = max( $colsize[$cid], strlen($col) );
			}
		}

		echo PHP_EOL . str_repeat('-', array_sum($colsize) + count($colsize) * 5) . PHP_EOL;
		$firstrow = true;
		$lastfirstcell = '';
		foreach( $data as $row ){
			if($firstrow){
				echo '| ';
				foreach( $row as $cid => $col ){
					echo self::BLUE . str_pad($cid, $colsize[$cid] + self::PAD_SPACING ) . self::RESET . '|  ';
				}
				echo PHP_EOL;
				echo str_repeat('-', array_sum($colsize) + count($colsize) * 5) . PHP_EOL;
				$firstrow = false;
			}
			echo '| ';
			$firstcell = true;
			foreach( $row as $cid => $col ){
				if($firstcell){
					if( $lastfirstcell == $col  ){
						$col = '';
					}
					else {
						$lastfirstcell = $col;
					}
					$firstcell = false;
				}
				echo str_pad($col, $colsize[$cid] + self::PAD_SPACING ) . '|  ';
			}
			echo PHP_EOL;
		}
		echo str_repeat('-', array_sum($colsize) + count($colsize) * 5) . PHP_EOL . PHP_EOL;
	}

	public function __destruct(){
		$this->print([self::BEGINEND]);
	}

	private function echo( string $s, ?string $color = null, int $ind = 0) : void {
		echo str_repeat("\t", $ind) . ($color === null ? '' : $color ) . $s . ($color === null ? '' : self::RESET ) . PHP_EOL;
	}

	public function readline(string $question, ?string $color = null, int $ind = 0) : string {
		echo str_repeat("\t", $ind) . ($color === null ? '' : $color );
		$r = readline($question . ' ');
		echo self::RESET;
		return $r;
	}
}
?>