<?php

class FreeLunchLabs_MailGun_Block_Adminhtml_Event_Container extends Mage_Adminhtml_Block_Widget_Grid_Container {

    protected function _construct() {
        parent::_construct();

        $this->_blockGroup = 'freelunchlabs_mailgun';
        $this->_controller = 'adminhtml_event';
        $this->_headerText = false;
        
    }
}