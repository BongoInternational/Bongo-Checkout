<?php
class Bongo_Postorder_Block_Adminhtml_Postorder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_postorder';
    $this->_blockGroup = 'postorder';
    $this->_headerText = Mage::helper('postorder')->__('Posted Orders Log Manager');
    $this->_addButtonLabel = Mage::helper('postorder')->__('Add Order');
    parent::__construct();
  }
}