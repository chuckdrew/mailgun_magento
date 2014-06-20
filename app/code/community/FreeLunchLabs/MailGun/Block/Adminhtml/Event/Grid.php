<?php

class FreeLunchLabs_MailGun_Block_Adminhtml_Event_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('mailgun_event_grid');
        $this->setDefaultSort('timestamp');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(false);
        $this->setFilterVisibility(false);
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('freelunchlabs_mailgun/event_collection');
        $collection->addFieldToFilter('email_id', Mage::registry('current_email')->getId());
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        
        $this->addColumn('event_type', array(
            'header' => 'Event',
            'index' => 'event_type',
            'renderer'  => 'FreeLunchLabs_MailGun_Block_Adminhtml_Event_Renderer_Type'
        ));
        
        $this->addColumn('timestamp', array(
            'header' => 'Event Time',
            'index' => 'timestamp',
            'renderer'  => 'FreeLunchLabs_MailGun_Block_Adminhtml_Event_Renderer_Timestamp'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return null;
    }

    public function getGridUrl() {
        return $this->getUrl('*/emailTracking/emailDetail', array(
            '_current' => true,
            'id' => Mage::registry('current_email')->getId()
        ));
    }

}