<?php

class FreeLunchLabs_MailGun_Block_Adminhtml_Customer_Email_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('mailgun_email_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('freelunchlabs_mailgun/email_collection')->getGridCollection();
        
        //Filter on Customer
        $collection->addFieldToFilter('customer_id', Mage::registry('current_customer')->getId());
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('subject', array(
            'header' => 'Subject',
            'type' => 'text',
            'index' => 'subject'
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
        return $this->getUrl('*/emailtracking/emailgrid', array(
            '_current' => true,
            'id' => Mage::registry('current_customer')->getId()
        ));
    }

}