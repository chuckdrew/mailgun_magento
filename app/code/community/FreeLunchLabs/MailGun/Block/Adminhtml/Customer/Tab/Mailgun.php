<?php

class FreeLunchLabs_MailGun_Block_Adminhtml_Customer_Tab_Mailgun extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    public function _construct() {
        parent::_construct();

        $this->setTemplate('mailgun/customer/tab/tab.phtml');
    }

    public function getTabLabel() {
        return $this->__('Email Tracking');
    }

    public function getTabTitle() {
        return $this->__('View emails sent to this customer and the email history');
    }

    public function canShowTab() {
        return Mage::getStoreConfig('mailgun/events/store');
    }

    public function isHidden() {
        return false;
    }
    
    public function getAfter() {
        return 'tags';
    }
    
    public function getGrid() {
        $gridBlock = $this->getLayout()->createBlock('freelunchlabs_mailgun/adminhtml_customer_email'); 
        
        return $gridBlock->getGridHtml();
    }

}