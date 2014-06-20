<?php

class FreeLunchLabs_MailGun_Model_Resource_Email extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('freelunchlabs_mailgun/email', 'id');
    }

    public function deleteEmailTrackingLogs($days = false) {
        if($days) {   
            $daysPrior = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time() - (86400 * $days)));
            $where = " WHERE date_sent < '{$daysPrior}'";
        } else {
            $where = "";
        }
     
        $query = "DELETE FROM {$this->getMainTable()}" . $where;

        Mage::getSingleton('core/resource')
                ->getConnection('core_write')
                ->query($query);
 
    }
    
}
