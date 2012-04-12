<?php


class Bongo_Checkout_Model_Method_Bongo extends Mage_Payment_Model_Method_Abstract
{

    protected $_code  = 'bongo';
    protected $_formBlockType = 'bongo_checkout/form_bongo';
    protected $_infoBlockType = 'bongo_checkout/info_bongo';
	
	public function getPartnerKey()
    {
        return $this->getConfigData('partner_key');
    }
	
	public function getCheckoutURL()
    {
        return $this->getConfigData('checkout_url');
    }
	
	public function getExcludedCountries()
	{
		return $this->getConfigData('excluded_countries');
	}

    /*public function assignData($data)
    {
        $details = array();
        if ($this->getPhoneNumber()) {
            $details['phone_number'] = $this->getPhoneNumber();
        }
        if ($this->getInternationalPhoneNumber()) {
            $details['intl_phone_number'] = $this->getInternationalPhoneNumber();
        }
        if (!empty($details)) {
            $this->getInfoInstance()->setAdditionalData(serialize($details));
        }
        return $this;
    }

    public function getPhoneNumber()
    {
        return Mage::getStoreConfig('payment/phone/phone_number');
    }

    public function getInternationalPhoneNumber()
    {
        return Mage::getStoreConfig('payment/phone/intl_phone_number');
    }*/
}
?>