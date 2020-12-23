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

class Share {

	private JSONReader $shares;
	private Login $login;
	private String $error = "";

	public function __construct( Login $login ) {
		$this->shares = new JSONReader('shares');
		$this->login = $login;

		$this->checkInitGroup($this->login->getGroup());
	}

	private function checkInitGroup(string $group) : void {
		if( !$this->shares->isValue([$group]) ){
			$this->shares->setValue([$group], array(
				'byme' => array(), // shared by me
				'withme' => array() // shared with me
			));
		}
	}

	/**
	 * Get the list of shares this user has created
	 */
	public function getShares() : array {
		return $this->shares->getValue([$this->login->getGroup(), 'byme']);
	}

	/**
	 * Add a share for this user (share a category with other group)
	 */
	public function addShare( string $category, string $group ) : void {
		if( $group !== $this->login->getGroup()
			&& $this->login->getGroupList()->isValue([$group])
		){
			if( in_array( $category, $this->getAllCategories() ) ){
				$this->checkInitGroup($group);

				$byme = array(
					'category' => $category,
					'group' => $group
				);
				if( $this->shares->searchValue([$this->login->getGroup(), 'byme'], $byme) === false ){ 
					$this->shares->setValue([$this->login->getGroup(), 'byme', null], $byme);
					$this->shares->setValue([$group, 'withme', null], array(
						'category' => $category,
						'group' => $this->login->getGroup()
					));
				}
				else {
					$this->error = "Share already exists.";
				}
			}
			else {
				$this->error = "Unknown category to share";
			}
		}
		else {
			$this->error = "Invalid group to share with";
		}
	}

	/**
	 * Remove a share for this user (share a category with other group)
	 */
	public function removeShare( string $category, string $group ) : void {
		if( $group !== $this->login->getGroup()
			&& $this->login->getGroupList()->isValue([$group])
		){
			$this->checkInitGroup($group);

			$byme = array(
				'category' => $category,
				'group' => $group
			);
			$pos = $this->shares->searchValue([$this->login->getGroup(), 'byme'], $byme);
			if( $pos !== false ){
				$this->shares->setValue([$this->login->getGroup(), 'byme', $pos], null);
			}

			$withme = array(
				'category' => $category,
				'group' => $this->login->getGroup()
			);
			$pos = $this->shares->searchValue([$group, 'withme'], $withme);
			if( $pos !== false ){
				$this->shares->setValue([$group, 'withme', $pos], null);
			}
		}
		else {
			$this->error = "Invalid group to delete share for";
		}
	}

	private function getAllCategories(){
		$stats = new TTTStats(['all'], API::getStorageDir($this->login->getGroup()));
		$combi = $stats->getAllResults()['combi'];
		return array_values(array_unique(array_column($combi, 'category')));
	}

	public function getCategoriesAndGroups() : array {
		return array(
			'categories' => $this->getAllCategories(),
			'groups' => array_keys($this->login->getGroupList()->getArray())
		);
	}

	public function getErrorMessage() : string {
		return $this->error;
	}

	public function hasError() : bool {
		return !empty($this->error);
	}

	/**
	 * Get the list of shares shared with this user
	 */
	public function getSharedWithMe() : array {
		return $this->shares->getValue([$this->login->getGroup(), 'withme']);
	}
}
?>