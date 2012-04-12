<?php

class Bongo_Shipping_Model_Shipping extends Mage_Shipping_Model_Shipping
{
    const XML_PATH_MERCHANT_COUNTRY = 'settings/merchant_address/country_id';
    const XML_PATH_MERCHANT_REGION = 'settings/merchant_address/region_id';
    const XML_PATH_MERCHANT_CITY = 'settings/merchant_address/city';
	const XML_PATH_MERCHANT_ZIP = 'settings/merchant_address/postcode';
	const XML_PATH_BONGO_COUNTRY = 'settings/bongo_address/country_id';
	const XML_PATH_BONGO_REGION = 'settings/bongo_address/region_id';
	const XML_PATH_BONGO_CITY = 'settings/bongo_address/city';
    const XML_PATH_BONGO_ZIP = 'settings/bongo_address/postcode';
    const XML_PATH_DOMESTIC_SHIPPING = 'settings/domestic_shipping/highest_or_lowest';
    const LOWEST_COST = 0;
    const HIGHEST_COST = 1;
 
    protected $domestic_shipping_information = "No information available";
    protected $cart = null;
    protected $store = null;    
    protected $hilow = null;
    
    public $costLabel = "";

    /**
     * Initialize resources
     */
    public function __construct()
    {
		$this->cart = Mage::helper('checkout/cart');
		$this->store = $this->cart->getCart()->getStore();
		$this->hilow = Mage::getStoreConfig(self::XML_PATH_DOMESTIC_SHIPPING, $this->store);
    }


    /**
     * Override Shippping collectRates function
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $storeId = $request->getStoreId();

        $limitCarrier = $request->getLimitCarrier();
        if (!$limitCarrier) {
            $carriers = Mage::getStoreConfig('carriers', $storeId);

            foreach ($carriers as $carrierCode => $carrierConfig) {
                $this->collectCarrierRates($carrierCode, $request);
            }
        } else {
            if (!is_array($limitCarrier)) {
                $limitCarrier = array($limitCarrier);
            }
            foreach ($limitCarrier as $carrierCode) {
                $carrierConfig = Mage::getStoreConfig('carriers/' . $carrierCode, $storeId);
                if (!$carrierConfig) {
                    continue;
                }
                $this->collectCarrierRates($carrierCode, $request);
            }
        }

        return $this;
    }

    
	/**
     * Domestic Shipping Cost
     */
    public function getBongoDomesticShipping()
    {
/*    	
		//Get Bongo cost **********************************************
		$bongo_cost = $this->getBongoCost();

		//Get Merchant cost **********************************************
		$merchant_cost = $this->getMerchantCost();

		if($this->hilow == self::LOWEST_COST){
			$bongo_domestic_shipping = ($merchant_cost < $bongo_cost) ? $merchant_cost : $bongo_cost;
			$this->costLabel = "Lowest domestic shipping cost: ";
		}else {
			$bongo_domestic_shipping = ($merchant_cost > $bongo_cost) ? $merchant_cost : $bongo_cost;
			$this->costLabel = "Highest domestic shipping cost: ";
		}
*/

		//Get Bongo to Merchant cost **********************************************
		$bongo_domestic_shipping = $this->getBongoToMerchantCost();
		if($this->hilow == self::LOWEST_COST){
			$this->costLabel = "Lowest domestic shipping cost: ";
		}else {
			$this->costLabel = "Highest domestic shipping cost: ";
		}
		
		return $bongo_domestic_shipping;
	}



	/**
     * Get Domestic Shipping additional information
     */
    public function getBongoShippingInformation()
    {
    	return 'Domestic shipping info: '.$this->domestic_shipping_information."<br />";
	}


	/**
     * Get Bongo to Merchant Cost
     */
    protected function getBongoToMerchantCost()
    {
		$cost = ($this->hilow == self::LOWEST_COST) ? 10000 : 0;

		$address = $this->cart->getQuote()->getShippingAddress();
		$origCountry = Mage::getStoreConfig(self::XML_PATH_BONGO_COUNTRY, $this->store);
		$origRegionCode = Mage::getStoreConfig(self::XML_PATH_BONGO_REGION, $this->store);
		$origCity = Mage::getStoreConfig(self::XML_PATH_BONGO_CITY, $this->store);
		$origPostal = Mage::getStoreConfig(self::XML_PATH_BONGO_ZIP, $this->store);
		$destCountry = Mage::getStoreConfig(self::XML_PATH_MERCHANT_COUNTRY, $this->store);
		$destRegionCode = Mage::getStoreConfig(self::XML_PATH_MERCHANT_REGION, $this->store);
		$destCity = Mage::getStoreConfig(self::XML_PATH_MERCHANT_CITY, $this->store);
		$destPostal = Mage::getStoreConfig(self::XML_PATH_MERCHANT_ZIP, $this->store);
        		
        Mage::getConfig()
        	->saveConfig('shipping/origin/country_id', $origCountry )
			->saveConfig('shipping/origin/region_id', $origRegionCode )
			->saveConfig('shipping/origin/city', $origCity )
			->saveConfig('shipping/origin/postcode', $origPostal );
		Mage::getConfig()->reinit();
		Mage::app()->reinitStores();
        		
		$request = Mage::getModel('shipping/rate_request');
        $request
            ->setCountryId($origCountry)
        	->setOrigCountry(Mage::getModel('directory/country')->load($origCountry)->getIso2Code())
            ->setRegionId($origRegionCode)
            ->setOrigRegionCode(Mage::getModel('directory/region')->load($origRegionCode)->getCode())
            ->setCity($origCity)
            ->setOrigCity($origCity)
            ->setPostcode($origPostal)
            ->setOrigPostal($origPostal)
        	->setLimitCarrier(null)
	        ->setAllItems($address->getAllItems())
	        ->setDestCountryId($destCountry)
	        ->setDestRegionId($destRegionCode)
	        ->setDestCity($destCity)
	        ->setDestPostcode($destPostal)
	        ->setPackageValue($address->getBaseSubtotal())
	        ->setPackageValueWithDiscount($address->getBaseSubtotalWithDiscount())
	        ->setPackageWeight($address->getWeight())
	        ->setFreeMethodWeight($address->getFreeMethodWeight())
	        ->setPackageQty($address->getItemQty())
	        ->setStoreId(Mage::app()->getStore()->getId())
	        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
	        ->setBaseCurrency(Mage::app()->getStore()->getBaseCurrency())
	        ->setPackageCurrency(Mage::app()->getStore()->getCurrentCurrency());
	        
	    
        $this->resetResult();
		$this->collectRates($request);
		$rates = $this->getResult()->getAllRates();
        foreach ($rates as $rate) {
			$price = $rate->getPrice();
			if ( ($this->hilow == self::LOWEST_COST AND $price < $cost) OR ($this->hilow == self::HIGHEST_COST AND $price > $cost) ){
				$cost = $price;
				$this->domestic_shipping_information = $rate->getCarrierTitle()." (".$rate->getMethodTitle().")";
				$this->domestic_shipping_information .= " - (Bongo to Merchant: ".Mage::helper('core')->currency($price,true,false).") ";
			}
        }
		return ($cost == 10000) ? 0 : $cost;
	}
	

	/**
     * Get Bongo Cost
     */
    protected function getBongoCost()
    {
		$cost = ($this->hilow == self::LOWEST_COST) ? 10000 : 0;

		$address = $this->cart->getQuote()->getShippingAddress();
		$origCountry = Mage::getStoreConfig(self::XML_PATH_BONGO_COUNTRY, $this->store);
		$origRegionCode = Mage::getStoreConfig(self::XML_PATH_BONGO_REGION, $this->store);
		$origCity = Mage::getStoreConfig(self::XML_PATH_BONGO_CITY, $this->store);
		$origPostal = Mage::getStoreConfig(self::XML_PATH_BONGO_ZIP, $this->store);
        		
        Mage::getConfig()
        	->saveConfig('shipping/origin/country_id', $origCountry )
			->saveConfig('shipping/origin/region_id', $origRegionCode )
			->saveConfig('shipping/origin/city', $origCity )
			->saveConfig('shipping/origin/postcode', $origPostal );
		Mage::getConfig()->reinit();
		Mage::app()->reinitStores();
        		
		$request = Mage::getModel('shipping/rate_request');
        $request
            ->setCountryId($origCountry)
        	->setOrigCountry(Mage::getModel('directory/country')->load($origCountry)->getIso2Code())
            ->setRegionId($origRegionCode)
            ->setOrigRegionCode(Mage::getModel('directory/region')->load($origRegionCode)->getCode())
            ->setCity($origCity)
            ->setOrigCity($origCity)
            ->setPostcode($origPostal)
            ->setOrigPostal($origPostal)
        	->setLimitCarrier(null)
	        ->setAllItems($address->getAllItems())
	        ->setDestCountryId($address->getCountryId())
	        ->setDestRegionId($address->getRegionId())
	        ->setDestPostcode($address->getPostcode())
	        ->setPackageValue($address->getBaseSubtotal())
	        ->setPackageValueWithDiscount($address->getBaseSubtotalWithDiscount())
	        ->setPackageWeight($address->getWeight())
	        ->setFreeMethodWeight($address->getFreeMethodWeight())
	        ->setPackageQty($address->getItemQty())
	        ->setStoreId(Mage::app()->getStore()->getId())
	        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
	        ->setBaseCurrency(Mage::app()->getStore()->getBaseCurrency())
	        ->setPackageCurrency(Mage::app()->getStore()->getCurrentCurrency());
	        
	    
        $this->resetResult();
		$this->collectRates($request);
		$rates = $this->getResult()->getAllRates();
        foreach ($rates as $rate) {
			$price = $rate->getPrice();
			if ( ($this->hilow == self::LOWEST_COST AND $price < $cost) OR ($this->hilow == self::HIGHEST_COST AND $price > $cost) ){
				$cost = $price;
				$this->domestic_shipping_information = $rate->getCarrierTitle()." (".$rate->getMethodTitle().")";
				$this->domestic_shipping_information .= " - (Bongo: ".Mage::helper('core')->currency($price,true,false).") ";
			}
        }
		return ($cost == 10000) ? 0 : $cost;
	}
	
	/**
     * Get Merchant Cost
     */
    protected function getMerchantCost()
    {
		$cost = ($this->hilow == self::LOWEST_COST) ? 10000 : 0;
		$address = $this->cart->getQuote()->getShippingAddress();
		
		$origCountry = Mage::getStoreConfig(self::XML_PATH_MERCHANT_COUNTRY, $this->store);
		$origRegionCode = Mage::getStoreConfig(self::XML_PATH_MERCHANT_REGION, $this->store);
		$origCity = Mage::getStoreConfig(self::XML_PATH_MERCHANT_CITY, $this->store);
		$origPostal = Mage::getStoreConfig(self::XML_PATH_MERCHANT_ZIP, $this->store);
        		
        Mage::getConfig()
        	->saveConfig('shipping/origin/country_id', $origCountry )
			->saveConfig('shipping/origin/region_id', $origRegionCode )
			->saveConfig('shipping/origin/city', $origCity )
			->saveConfig('shipping/origin/postcode', $origPostal );
		Mage::getConfig()->reinit();
		Mage::app()->reinitStores();
        		
		$request = Mage::getModel('shipping/rate_request');
        $request
            ->setCountryId($origCountry)
        	->setOrigCountry(Mage::getModel('directory/country')->load($origCountry)->getIso2Code())
            ->setRegionId($origRegionCode)
            ->setOrigRegionCode(Mage::getModel('directory/region')->load($origRegionCode)->getCode())
            ->setCity($origCity)
            ->setOrigCity($origCity)
            ->setPostcode($origPostal)
            ->setOrigPostal($origPostal)
        	->setLimitCarrier(null)
	        ->setAllItems($address->getAllItems())
	        ->setDestCountryId($address->getCountryId())
	        ->setDestRegionId($address->getRegionId())
	        ->setDestPostcode($address->getPostcode())
	        ->setPackageValue($address->getBaseSubtotal())
	        ->setPackageValueWithDiscount($address->getBaseSubtotalWithDiscount())
	        ->setPackageWeight($address->getWeight())
	        ->setFreeMethodWeight($address->getFreeMethodWeight())
	        ->setPackageQty($address->getItemQty())
	        ->setStoreId(Mage::app()->getStore()->getId())
	        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
	        ->setBaseCurrency(Mage::app()->getStore()->getBaseCurrency())
	        ->setPackageCurrency(Mage::app()->getStore()->getCurrentCurrency());

	
        $this->resetResult();
		$this->collectRates($request);
		$rates = $this->getResult()->getAllRates();
        foreach ($rates as $rate) {
			$price = $rate->getPrice();
			if ( ($this->hilow == self::LOWEST_COST AND $price < $cost) OR ($this->hilow == self::HIGHEST_COST AND $price > $cost) ){
				$cost = $price;
				$info = " (Merchant:".Mage::helper('core')->currency($price,true,false).")";
			}
        }
        $this->domestic_shipping_information .= $info;
		return ($cost == 10000) ? 0 : $cost;
	}
}
