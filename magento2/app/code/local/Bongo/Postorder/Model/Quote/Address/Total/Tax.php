<?php
class Bongo_Postorder_Model_Quote_Address_Total_Tax extends Mage_Sales_Model_Quote_Address_Total_Tax
{
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        if ($is_custom_bongo_tax){
            $address->setTaxAmount($custom_bongo_tax);
            $address->setBaseTaxAmount($custom_bongo_tax);
            $address->setGrandTotal($address->getGrandTotal() + $address->getTaxAmount());
            $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseTaxAmount());
            return $this;
        } else {
        }
    }
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        global $custom_bongo_tax, $is_custom_bongo_tax;
        echo 'here'; exit;
        $applied = $address->getAppliedTaxes();
        $store = $address->getQuote()->getStore();
        $amount = $address->getTaxAmount();

        if (($amount!=0) || (Mage::helper('tax')->displayZeroTax($store))) {
            $address->addTotal(array(
                'code'=>$this->getCode(),
                'title'=>Mage::helper('sales')->__('Tax'),
                'full_info'=>$applied ? $applied : array(),
                'value'=>$amount
            ));
        }
        return $this;
    }
}