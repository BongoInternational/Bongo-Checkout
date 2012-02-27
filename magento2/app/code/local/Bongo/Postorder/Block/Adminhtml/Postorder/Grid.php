<?php

class Bongo_Postorder_Block_Adminhtml_Postorder_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('postorderGrid');
        $this->setDefaultSort('created_time');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $resource = Mage::getSingleton('core/resource');
        $collection = Mage::getModel('postorder/postorder')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('postorder_id', array(
            'header' => Mage::helper('postorder')->__('ID'),
            'align' => 'right',
            'width' => '10px',
            'index' => 'postorder_id',
        ));

        $this->addColumn('partner_key', array(
            'header' => Mage::helper('postorder')->__('Partner Key'),
            'align' => 'left',
            'index' => 'partner_key',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('postorder')->__('Status'),
            'align' => 'left',
            'index' => 'status',
        ));

        $this->addColumn('created_time', array(
            'header' => Mage::helper('postorder')->__('Posted'),
            'index' => 'created_time',
            'type' => 'datetime',
            'width' => '200px',
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('postorder')->__('Action'),
            'width' => '40',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('postorder')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('postorder')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('postorder')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('postorder_id');
        $this->getMassactionBlock()->setFormFieldName('postorder');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('postorder')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('postorder')->__('Are you sure?')
        ));

        return $this;
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}