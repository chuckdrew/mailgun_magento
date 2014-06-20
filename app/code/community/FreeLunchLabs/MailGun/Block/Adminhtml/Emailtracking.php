<?php

class FreeLunchLabs_MailGun_Block_Adminhtml_Emailtracking extends Mage_Adminhtml_Block_Template {

    function getFetchActivityUrl() {
        return $this->getUrl('*/*/getEmailEvents');
    }

    public function getEmailDetail() {
        $email = Mage::registry('current_email');

        if ($email->getCustomerId()) {
            $customer = Mage::getModel('customer/customer')->load($email->getCustomerId());
            $email->setCustomer($customer);
        }

        return $email;
    }

    public function getBackButtonHtml() {
        return $this->getLayout()->createBlock('adminhtml/widget')->getButtonHtml('Back', 'history.back()', 'back');
    }

    public function getViewEmailBodyButtonHtml($emailId) {
        $url = $this->getUrl('*/*/emailView', array(
            'id' => $emailId
        ));

        $onClick = "window.open('{$url}','name','width=700,height=800')";

        return $this->getLayout()->createBlock('adminhtml/widget')->getButtonHtml('View Email Body', $onClick);
    }

    public function getEditCustomerUrl($customerId) {
        return $url = $this->getUrl('*/customer/edit', array(
            'id' => $customerId
        ));
    }

    public function getCustomerGroupName($customerGroupId) {
        return Mage::getModel('customer/group')->load($customerGroupId)->getCustomerGroupCode();
    }

    public function formatCustomerCreateDate($createdTimestamp) {
        return Mage::helper('core')->formatDate($createdTimestamp, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true);
    }

    public function getFetch24HoursOfEmailActivityButton() {
        $onClick = "setLocation('{$this->getFetchActivityUrl()}')";

        return $this->getLayout()->createBlock('adminhtml/widget')->getButtonHtml('Fetch Past 24 Hours of Email Activity', $onClick);
    }

    public function getDeleteEmailTrackingLogsDaysButton() {       
        $days = Mage::getStoreConfig('mailgun/events/days');

        if ($days) {
            $url = $this->getUrl('*/*/deleteEmailTrackingLogsDays');
            $onClick = "confirmSetLocation('Are you sure?', '{$url}')";
            
            return $this->getLayout()->createBlock('adminhtml/widget')->getButtonHtml("Delete Email Records Older Than {$days} Days", $onClick, 'delete');
        } else {
            return "";
        }
    }

    public function getDeleteAllEmailTrackingLogsButton() {
        $url = $this->getUrl('*/*/deleteEmailTrackingLogs');
        $onClick = "confirmSetLocation('Are you sure?', '{$url}')";

        return $this->getLayout()->createBlock('adminhtml/widget')->getButtonHtml('Delete All Email Records', $onClick, 'delete');
    }

}

