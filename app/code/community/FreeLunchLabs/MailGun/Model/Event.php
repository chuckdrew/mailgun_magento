<?php

class FreeLunchLabs_MailGun_Model_Event extends Mage_Core_Model_Abstract {
    //Event types

    const GENERATED = "generated";
    const ACCEPTED = "accepted";
    const REJECTED = "rejected";
    const DELIVERED = "delivered";
    const FAILED = "failed";
    const OPENED = "opened";
    const CLICKED = "clicked";
    const UNSUBSCRIBED = "unsubscribed";
    const COMPLAINED = "complained";
    const STORED = "stored";

    protected function _construct() {
        $this->_init('freelunchlabs_mailgun/event');
    }

    public function logEmailEvent($emailId, $eventType, $email = false) {
        $this->setEmailId($emailId);
        $this->setEventType($eventType);
        $this->setTimestamp(Mage::getSingleton('core/date')->gmtTimestamp());
        $this->save();
    }

    public function loadByTimestampAndEmailId($timestamp, $emailId) {
        $collection = $this->getCollection();
        $collection->addFieldToFilter('timestamp', $timestamp);
        $collection->addFieldToFilter('email_id', $emailId);

        return $collection->getFirstItem();
    }

}