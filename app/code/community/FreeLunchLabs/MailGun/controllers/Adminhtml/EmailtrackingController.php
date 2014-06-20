<?php

class FreeLunchLabs_MailGun_Adminhtml_EmailtrackingController extends Mage_Adminhtml_Controller_Action {

    protected function _initCustomer($idFieldName = 'id')
    {
        $this->_title($this->__('Customers'))->_title($this->__('Manage Customers'));

        $customerId = (int) $this->getRequest()->getParam($idFieldName);
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);
        return $this;
    }
    
    protected function _initEmail($idFieldName = 'id') {
        $emailId = (int) $this->getRequest()->getParam($idFieldName);
        $email = Mage::getModel('freelunchlabs_mailgun/email');  

        if ($emailId) {
            $email->load($emailId);
        }

        Mage::register('current_email', $email);
        return $this;
    } 

    public function emailGridAction()
    {
        $this->_initCustomer();
        $this->loadLayout();
        $gridBlock = $this->getLayout()->createBlock('freelunchlabs_mailgun/adminhtml_customer_email');
        
        $this->getResponse()->setBody($gridBlock->getGridHtml());
    }
    
    public function indexAction() {
        $this->_title($this->__('System'))->_title($this->__('Email Tracking'));
        
        $this->loadLayout();      
        $this->_setActiveMenu('customer');
        $this->renderLayout();
    }
    
    public function emailDetailAction() {
        $this->_title($this->__('System'))->_title($this->__('Email Tracking - Detail'));
        
        $this->_initEmail();
        
        $this->loadLayout();      
        $this->_setActiveMenu('customer');
        $this->renderLayout();
    }
    
    public function getEmailEventsAction() {
        Mage::getModel('freelunchlabs_mailgun/mailgun')->processEmailEventsForAllStores();

        $this->_getSession()->addSuccess(
            Mage::helper('adminhtml')->__('Past 24 hours of email events fetched.')
        );

        $this->_redirect('*/*');
    }
    
    public function emailViewAction() {
        $this->_title($this->__('System'))->_title($this->__('Email Tracking - Detail - Email Body'));
        $this->_initEmail();
   
        $this->getResponse()->setBody(Mage::registry('current_email')->getBody());
    }
    
    public function deleteEmailTrackingLogsDaysAction() {
        Mage::getModel('freelunchlabs_mailgun/email')->deleteEmailTrackingLogsDays();

        $this->_getSession()->addSuccess(
            Mage::helper('adminhtml')->__('Email records older than ' . Mage::getStoreConfig('mailgun/events/days') . ' days were deleted.')
        );

        $this->_redirect('*/*');
    }
    
    public function deleteEmailTrackingLogsAction() {
        Mage::getModel('freelunchlabs_mailgun/email')->deleteEmailTrackingLogs();

        $this->_getSession()->addSuccess(
            Mage::helper('adminhtml')->__('All Email Records Were Deleted.')
        );

        $this->_redirect('*/*');
    }

}