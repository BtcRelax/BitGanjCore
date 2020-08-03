<?php

namespace BtcRelax\Layout;

final class LayoutHeader {

    //const HEADER_BEGIN = '<div id="toolbarObj"  class="collapse navbar-collapse">
    //		  <ul class="nav navbar-nav">';
    //<a href="javascript:void(0)" class="closebtn" onclick="JApp.closeNav()">×</a>
    const HEADER_BEGIN = '<div id="myNav" class="overlay" style="height: 0%;"><div id="navList" class="overlay-content">';
    const HEADER_END = '</div></div>';
    const I_PREFIX = '/img/';

    private $SiteMap;
    private $_core;
    
    
    public function __construct() {
        global  $core;
        $this->_core = $core;
        $jsonPath = __DIR__ . '/menu.json';
        if (file_exists($jsonPath)) {
            $jsonData = \file_get_contents($jsonPath);
            $decode_data = json_decode($jsonData, true);
            $this->SiteMap = $decode_data['menu_items'];
        } else {
            throw new \BtcRelax\Exception\NotFoundException('File for menu structure ' . $jsonPath . ' not found!');
        }
    }

    public function actionGetHeaderHtml() {
        $result = null;
        $vAM = \BtcRelax\Core::createAM();
        $cUser = $vAM->getUser();
        foreach ($this->SiteMap as $menuItem) {
            if ($vAM->isUserHasRight($menuItem['rights'], $cUser))
                { $result .= $this->renderMenuItem($menuItem); }
            }
        $vMenuItem = \BtcRelax\LHCApi::getMenuItemForUser($cUser);
        if (\FALSE !== $vMenuItem) { $result = $vMenuItem . $result;  }            
        return $result;        
    }
    
    public function renderMenuItem($pMenuItem)
    {
        if (\array_key_exists('icon', $pMenuItem)) { $iconHtml = \sprintf('<img src="%s" />', $pMenuItem['icon']) ; }
        return  \sprintf('<a href="%s">%s %s</a>', $pMenuItem['cmd'], isset($iconHtml)?$iconHtml:" ", $pMenuItem['title'] );  
    }

    public function renderGetHeaderHtml() {
        $vHtml = $this->actionGetHeaderHtml();
        if (!is_null($vHtml))
            {
                $result = Self::HEADER_BEGIN . $vHtml . Self::HEADER_END;
                return $result;
            }
    }

}


