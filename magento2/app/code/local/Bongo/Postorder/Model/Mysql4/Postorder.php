<?php

class Bongo_Postorder_Model_Mysql4_Postorder extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the postorder_id refers to the key field in your database table.
        $this->_init('postorder/postorder', 'postorder_id');
    }
}