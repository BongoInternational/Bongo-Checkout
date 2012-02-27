<?php

class Bongo_Postorder_Block_Adminhtml_Postorder_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'postorder';
        $this->_controller = 'adminhtml_postorder';
        
        $this->_updateButton('save', 'label', Mage::helper('postorder')->__('Save Log Item'));
        $this->_updateButton('delete', 'label', Mage::helper('postorder')->__('Delete Log Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('postorder_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'postorder_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'postorder_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('postorder_data') && Mage::registry('postorder_data')->getId() ) {
            return Mage::helper('postorder')->__("Edit Order From '%s'", $this->htmlEscape(Mage::registry('postorder_data')->getPartnerKey()));
        } else {
            return Mage::helper('postorder')->__('Add Order Item');
        }
    }
}