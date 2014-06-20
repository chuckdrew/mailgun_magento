<?php

class FreeLunchLabs_MailGun_Model_Resource_Event extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('freelunchlabs_mailgun/event', 'id');
    }
    
}
