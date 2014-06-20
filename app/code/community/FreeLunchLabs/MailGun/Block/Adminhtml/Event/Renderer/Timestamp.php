<?php
class FreeLunchLabs_MailGun_Block_Adminhtml_Event_Renderer_Timestamp extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value =  $row->getData($this->getColumn()->getIndex());
        $date = Mage::getSingleton('core/date')->date(null, $value);
        return Mage::helper('core')->formatDate(new Zend_Date($date), 'medium', true);
    }
}