<?php
class FreeLunchLabs_MailGun_Block_Adminhtml_Event_Renderer_Type extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value =  $row->getData($this->getColumn()->getIndex());
        return ucwords($value);
    }
}