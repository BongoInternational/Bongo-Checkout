<?php

class Bongo_Postorder_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getPostorderUrl() {
        return $this->_getUrl('postorder/index');
    }

}