<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BtcRelax\Model;

/**
 * Description of region
 *
 * @author god
 */
class Region {
	// private members 
	private $m_RegionId;
	private $m_ParentRegionId;
	private $m_RegionTitle;


	/**
	* Constructor
	* 
	* Example: 
	* $myRegion = new Region();
	*/
	public function __construct() {
		//--
	}

	/**
	* Constructor
	* 
	* Example: 
	* $myRegion = Region::WithParams( val1, val2,.. );
	*/
	public static function WithParams($RegionId, $ParentRegionId, $RegionTitle) {
		$instance = new self();
		$instance->m_RegionId = $RegionId;
		$instance->m_ParentRegionId = $ParentRegionId;
		$instance->m_RegionTitle = $RegionTitle;
		return $instance;
	}


	/**
	* Getters and Setters
	*/

	public function getRegionId() {
		return $this->m_RegionId;
	}

	public function setRegionId($RegionId) {
		$this->m_RegionId = $RegionId;
	}

	public function getParentRegionId() {
		return $this->m_ParentRegionId;
	}

	public function setParentRegionId($ParentRegionId) {
		$this->m_ParentRegionId = $ParentRegionId;
	}

	public function getRegionTitle() {
		return $this->m_RegionTitle;
	}

	public function setRegionTitle($RegionTitle) {
		$this->m_RegionTitle = $RegionTitle;
	}



	/**
	* Methods
	*/

	public function __toString() {
		return "";
	}
    
}
