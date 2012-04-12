<?php
class Bongo_Checkout_Model_Observer
{    
    /**
     * Periodically cancel pending cash orders by cron
     */
    public function cancelPendingCashOrders($schedule)
    {
		$minutes = Mage::getConfig()->getStoresConfigByPath('settings/cron_jobs/cancel_cash_orders');
		$minutes = ($minutes=="") ? "60:00" : sprintf("%d:00",$minutes);
		$orderCollection = Mage::getResourceModel('sales/order_collection');             
		$orderCollection
			->join('order_payment', 'main_table.entity_id = order_payment.parent_id', 'method')
			->addFieldToFilter('method', 'bongo')
			->addFieldToFilter('status', 'pending')                    
			->addFieldToFilter('created_at', array('lt' =>  new Zend_Db_Expr("DATE_ADD('".now()."', INTERVAL -'".$minutes."' HOUR_MINUTE)")))       
			->getSelect()                    
			->order('main_table.entity_id');                    
		$orders ="";            
		foreach($orderCollection->getItems() as $order)            
		{              
			$orderModel = Mage::getModel('sales/order');              
			$orderModel->load($order['entity_id']);               
			if(!$orderModel->canCancel())                
				continue;               
			$orderModel->cancel();              
			$orderModel->setStatus('canceled');              
			$orderModel->save();             
		}     
	} 
}
?>