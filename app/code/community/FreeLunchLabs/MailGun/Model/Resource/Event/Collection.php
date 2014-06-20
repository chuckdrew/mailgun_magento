<?php

class FreeLunchLabs_MailGun_Model_Resource_Event_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();

        $this->_init('freelunchlabs_mailgun/event');
    }
}