<?php

class FreeLunchLabs_MailGun_Block_Adminhtml_Global_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('mailgun_global_grid');
        $this->setDefaultSort('date_sent');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(false);
        $this->setFilterVisibility(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('freelunchlabs_mailgun/email_collection')->getGridCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        
        $this->addColumn('subject', array(
            'header' => 'Subject',
            'type' => 'text',
            'index' => 'subject'
        ));
        
        $this->addColumn('email_address', array(
            'header' => 'Recipient Address',
            'type' => 'email',
            'index' => 'email_address'
        ));
        
        $this->addColumn('mailgun_id', array(
            'header' => 'Mailgun ID',
            'type' => 'text',
            'index' => 'mailgun_id'
        ));
        
        $this->addColumn('current_status', array(
            'header' => 'Latest Status',
            'index' => 'current_status',
            'renderer'  => 'FreeLunchLabs_MailGun_Block_Adminhtml_Event_Renderer_Type'
        ));
        
        $this->addColumn('date_sent', array(
            'header' => 'Date Sent',
            'type' => 'datetime',
            'index' => 'date_sent'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/emailtracking/emaildetail', array(
            'id' => $row->getId()
        ));
    }

    public function getGridUrl() {
        return $this->getUrl('*/emailtracking');
    }

}