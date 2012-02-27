<?php

class Bongo_Postorder_Model_Mysql4_Postorder_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('postorder/postorder');
    }
}