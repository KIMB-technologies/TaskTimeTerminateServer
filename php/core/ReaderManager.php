<?php

class ReaderManager {

	private static ?ReaderManager $instance = null;

	private array $readerlist = array();

	private function __construct(){
		if(!is_null(self::$instance)){
			$this->readerlist = self::$instance->readerlist;
		}
		else{
			$this->readerlist = array();
		}
	}

	public function __destruct(){
		if(!is_null(self::$instance)){
			self::$instance->clear();
		}
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * */

	private function add(Reader $r){
		if( !in_array($r, $this->readerlist) ){
			$readerlist[] = $r;
		}
	}

	private function clear(){
		foreach($this->readerlist as &$r){
			$r->__destruct();
			unset($r);
		}
		unset($this->readerlist);
		self::$instance = null;
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * */

	private static function init(){
		if(is_null(self::$instance)){
			self::$instance = new ReaderManager();
		}
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * */

	public static function addReader(Reader $r) : void {
		self::init();
		self::$instance->add($r);
	}

	public static function clearAll() : void {
		self::init();
		self::$instance->clear();
	}
	
}

?>