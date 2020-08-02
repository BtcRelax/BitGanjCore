<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BtcRelax\Model;

/**
 * Description of product
 *
 * @author god
 */
class Product {
    
	// private members 
	private $m_ProductId;
	private $m_ProductName;
	private $m_DescriptionUrl;
        private $m_CreateDate;

	/**
	* Constructor
	* 
	* Example: 
	* $myProduct = new Product();
	*/
	public function __construct() {
		//--
	}

	public static function WithParams($vParams) {
		$instance = new self();
                if (!is_null($vParams))
                {
                    $vCreateParams = \array_pop($vParams);
                    if (\array_key_exists('ProductName', $vCreateParams))
                    { $instance->m_ProductName = $vCreateParams['ProductName']; }

            if (\array_key_exists('ProductId', $vCreateParams))
                    { $instance->m_ProductId = $vCreateParams['ProductId']; }

            if (\array_key_exists('ProductURL', $vCreateParams))
                    { $instance->m_DescriptionUrl = $vCreateParams['ProductURL']; }
        }
                return $instance;
	}
        
        
        public function getCreateDate() {
            return $this->m_CreateDate;
        }

        public function setCreateDate($m_CreateDate) {
            $this->m_CreateDate = $m_CreateDate;
            return $this;
        }

        
	/**
	* Getters and Setters
	*/
	public function getProductId() {
		return $this->m_ProductId;
	}

	public function setProductId($ProductId) {
		$this->m_ProductId = $ProductId;
	}

	public function getProductName() {
		return $this->m_ProductName;
	}

	public function setProductName($ProductName) {
		$this->m_ProductName = $ProductName;
	}

	public function getDescriptionUrl() {
            if (!empty($this->m_DescriptionUrl)) {
                if (\BtcRelax\Utils::is_url($this->m_DescriptionUrl))
                { return $this->m_DescriptionUrl; } else 
                {return \sprintf("%s/%s", INFO_URL,  $this->m_DescriptionUrl);}
            } 
	}

	public function setDescriptionUrl($DescriptionUrl) {
            $this->m_DescriptionUrl = $DescriptionUrl;
	}

	/**
	* Methods
	*/
	public function __toString() {
		return "";
	}
    
}
