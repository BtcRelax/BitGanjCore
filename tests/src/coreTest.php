<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of coreTest
 *
 * @author god
 */
//require_once '../core.php';
class coreTest extends PHPUnit_Framework_TestCase {

    /**
     * @var \RemoteWebDriver
     */
    protected $webDriver;

    public function setUp() {
        $this->object = \BtcRelax\Core::getIstance();
    }

    public function tearDown() {
 
    }


    public function testSimple() {
        $this->assertEquals(true, $this->object->init());
    }

}
