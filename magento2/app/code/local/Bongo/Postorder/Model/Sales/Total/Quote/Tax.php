<?php
/**
 * Tax totals calculation model
 */
class Bongo_Postorder_Model_Sales_Total_Quote_Tax extends Mage_Tax_Model_Sales_Total_Quote_Tax
{
    /**
     * Collect tax totals for quote address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Tax_Model_Sales_Total_Quote
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        global $custom_bongo_tax, $is_custom_bongo_tax;

        if ($is_custom_bongo_tax){
            $this->_addAmount($custom_bongo_tax);
            $this->_addBaseAmount($custom_bongo_tax);
        }

        return $this;
    }
}
