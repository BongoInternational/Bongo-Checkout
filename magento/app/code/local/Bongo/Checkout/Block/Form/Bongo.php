<?php

class Bongo_Checkout_Block_Form_Bongo extends Mage_Payment_Block_Form
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('bongo_checkout/form/bongo.phtml');
    }

}
?>