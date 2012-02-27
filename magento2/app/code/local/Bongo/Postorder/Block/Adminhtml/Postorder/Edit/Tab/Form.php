<?php

class Bongo_Postorder_Block_Adminhtml_Postorder_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('postorder_form', array('legend'=>Mage::helper('postorder')->__('Request Information')));
     
      $fieldset->addField('partner_key', 'text', array(
          'label'     => Mage::helper('postorder')->__('Partner Key'),
          'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'partner_key',
      ));

      $fieldset->addField('status', 'text', array(
          'label'     => Mage::helper('postorder')->__('Status'),
          'required'  => false,
          'name'      => 'status',
      ));

      $fieldset->addField('order', 'editor', array(
          'name'      => 'order',
          'label'     => Mage::helper('postorder')->__('Order'),
          'title'     => Mage::helper('postorder')->__('Order'),
          // 'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));

      if ( Mage::getSingleton('adminhtml/session')->getPostorderData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getPostorderData());
          Mage::getSingleton('adminhtml/session')->setPostorderData(null);
      } elseif ( Mage::registry('postorder_data') ) {
          $form->setValues(Mage::registry('postorder_data')->getData());
      }
      return parent::_prepareForm();
  }
}