<?php

class Bongo_Postorder_Block_Postorder extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getPostorder() {
        if (!$this->hasData('postorder')) {
            $this->setData('postorder', Mage::registry('postorder'));
        }
        return $this->getData('postorder');
    }

}