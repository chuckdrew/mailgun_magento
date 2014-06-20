<?php

class FreeLunchLabs_MailGun_Model_Resource_Email_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();

        $this->_init('freelunchlabs_mailgun/email');
    }
    
    public function getGridCollection() {
        //Fields
        $this->addFieldToSelect('id');
        $this->addFieldToSelect('subject');
        $this->addFieldToSelect('email_address');
        $this->addFieldToSelect('mailgun_id');
        $this->addFieldToSelect('date_sent');
        $this->addFieldToSelect('customer_id');
        
        //Get latest status
        $this->getSelect()->joinLeft(
            array('me1' => Mage::getResourceModel('freelunchlabs_mailgun/event')->getMainTable()),
            'main_table.id = me1.email_id',
            'event_type as current_status'
        );
        
        $this->getSelect()->joinLeft(
            array('me2' => Mage::getResourceModel('freelunchlabs_mailgun/event')->getMainTable()),
            '(main_table.id = me2.email_id AND (me1.timestamp < me2.timestamp OR me1.timestamp = me2.timestamp AND me1.id < me2.id))',
            false    
        );
        
        $this->getSelect()->where('me2.id IS NULL');
        
        return $this;
    }
}