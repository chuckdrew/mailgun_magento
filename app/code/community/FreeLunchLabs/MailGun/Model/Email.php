<?php

class FreeLunchLabs_MailGun_Model_Email extends Mage_Core_Model_Abstract {

    protected function _construct() {
        $this->_init('freelunchlabs_mailgun/email');
    }

    public function saveInitialSend($message, $sendResponse) {
        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId($message->getStore()->getWebsite()->getId());
        $customer->loadByEmail($message->getPrimaryRecipient());

        if ($customer) {
            $this->setCustomerId($customer->getId());
        }

        $this->setEmailAddress($message->getPrimaryRecipient());
        $this->setMailgunId(str_replace(array("<", ">"), "", $sendResponse->id));
        $this->setSubject($message->getSubject());
        $this->setBody($message->getHtmlBody());
        $this->setDateSent(Mage::getSingleton('core/date')->gmtTimestamp());
        $this->save();

        Mage::getModel('freelunchlabs_mailgun/event')->logEmailEvent($this->getId(), FreeLunchLabs_MailGun_Model_Event::GENERATED, $this);
    }

    public function loadByMailgunIdAndRecipient($mailgunId, $recipient) {
        $collection = $this->getCollection();
        $collection->addFieldToFilter('mailgun_id', $mailgunId);
        $collection->addFieldToFilter('email_address', $recipient);

        return $collection->getFirstItem();
    }

    public function deleteEmailTrackingLogsDays() {
        $days = Mage::getStoreConfig('mailgun/events/days');

        if ($days) {
            $this->deleteEmailTrackingLogs($days);
        }
    }

    public function deleteEmailTrackingLogs($days = false) {
        $this->getResource()->deleteEmailTrackingLogs($days);
    }

}