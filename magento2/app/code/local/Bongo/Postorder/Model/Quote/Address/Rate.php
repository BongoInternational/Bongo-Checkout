<?php
class Bongo_Postorder_Model_Quote_Address_Rate extends Mage_Sales_Model_Quote_Address_Rate {

    // overwrite default shipping price with custom shipping price that includes posted shipping price
    public function importShippingRate(Mage_Shipping_Model_Rate_Result_Abstract $rate) {
        if ($rate instanceof Mage_Shipping_Model_Rate_Result_Error) {
            $this
                    ->setCode($rate->getCarrier() . '_error')
                    ->setCarrier($rate->getCarrier())
                    ->setCarrierTitle($rate->getCarrierTitle())
                    ->setErrorMessage($rate->getErrorMessage())
            ;
        } elseif ($rate instanceof Mage_Shipping_Model_Rate_Result_Method) {

            $this
                    ->setCode($rate->getCarrier() . '_' . $rate->getMethod())
                    ->setCarrier($rate->getCarrier())
                    ->setCarrierTitle($rate->getCarrierTitle())
                    ->setMethod($rate->getMethod())
                    ->setMethodTitle($rate->getMethodTitle())
                    ->setMethodDescription($rate->getMethodDescription())
            ;
            global $custom_bongo_shipping;

            if ((float)$custom_bongo_shipping > 0) {
                $this->setPrice($custom_bongo_shipping);
            } else {
                $this->setPrice($rate->getPrice());
            }
        }
        return $this;
    }

}