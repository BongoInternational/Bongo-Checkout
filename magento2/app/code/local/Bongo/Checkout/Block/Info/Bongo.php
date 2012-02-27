<?php


class Bongo_Checkout_Block_Info_Bongo extends Mage_Payment_Block_Info
{

   /* protected $_phoneNumber;
    protected $_intlPhoneNumber;*/

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('bongo_checkout/info/bongo.phtml');
    }

    /**
     * Get Phone Number
     *
     * @return string
     */
   /* public function getPhoneNumber()
    {
        if (is_null($this->_phoneNumber)) {
            $this->_convertAdditionalData();
        }
        return $this->_phoneNumber;
    }
*/
    /**
     * getInternationalPhoneNumber
     *
     * @return string
     */
  /*  public function getInternationalPhoneNumber()
    {
        if (is_null($this->_intlPhoneNumber)) {
            $this->_convertAdditionalData();
        }
        return $this->_intlPhoneNumber;
    }*/

    /**
     * Enter description here...
     *
     * @return Bongo_Checkout_Block_Info_Phone
     */
   /* protected function _convertAdditionalData()
    {
        $details = @unserialize($this->getInfo()->getAdditionalData());
        if (is_array($details)) {
            $this->_phoneNumber = isset($details['phone_number']) ? (string) $details['phone_number'] : '';
            $this->_intlPhoneNumber = isset($details['intl_phone_number']) ? (string) $details['intl_phone_number'] : '';
        } else {
            $this->_phoneNumber = '';
            $this->_intlPhoneNumber = '';
        }
        return $this;
    }*/
    
}

?>
