<?php

class Bongo_Postorder_Block_Adminhtml_Postorder_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('postorder_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('postorder')->__('Log Item'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('postorder')->__('Edit Log Item'),
          'title'     => Mage::helper('postorder')->__('Edit Log Item'),
          'content'   => $this->getLayout()->createBlock('postorder/adminhtml_postorder_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}