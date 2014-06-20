<?php

class FreeLunchLabs_MailGun_Block_Adminhtml_Customer_Email extends Mage_Adminhtml_Block_Widget_Grid_Container {

    protected function _construct() {
        parent::_construct();

        $this->_blockGroup = 'freelunchlabs_mailgun';
        $this->_controller = 'adminhtml_customer_email';
        $this->_headerText = false;
        
    }
}