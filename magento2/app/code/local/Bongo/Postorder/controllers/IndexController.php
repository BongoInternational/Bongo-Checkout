<?php

class Bongo_Postorder_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function processAction() {
        if (Mage::getStoreConfig('postorder/general/enable_creation') != 1) {
            die('Order creation is disabled');
        }
        $post = $this->getRequest()->getPost();
        if ($post) {
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);
            try {
                $postObject = new Varien_Object();
                $postObject->setData($post);

                $error = false;

                $error_msg = '';

                if (!Zend_Validate::is(trim($post['partner_key']), 'NotEmpty')) {
                    $error = true;
                    $error_msg .= 'No partner_key';
                }

                if (!Zend_Validate::is(trim($post['status']), 'NotEmpty')) {
                    $error = true;
                    $error_msg .= 'No status';
                }

                if (!Zend_Validate::is(trim($post['order']), 'NotEmpty')) {
                    $error = true;
                    $error_msg .= 'No order information';
                }

                if ($error) {
                    throw new Exception();
                }

                //decode the order information
                $decoded_xml_order = base64_decode($post['order']);
                //save the data
                $id = 0;
                $model = Mage::getModel('postorder/postorder')->load($id);
                $model->setData($post);
                $model->setCreatedTime(now())->setUpdateTime(now());
                $model->save();
                //load the order to xml
                $order_information = simplexml_load_string($decoded_xml_order, 'SimpleXMLElement', LIBXML_NOCDATA);
                $store_id = Mage::app()->getStore('default')->getId();

                //check if we have the custom order 1 field
                if ((string) $order_information->channel->item->custom_order1 != '') {
                    //we have the order already created, so just update it
                    $_order = Mage::getModel('sales/order')->loadByIncrementId((string) $order_information->channel->item->custom_order1);
                } else {
                    //create the quote
                    $quote = Mage::getModel('sales/quote')->setStoreId($store_id);

                    $quote->setCustomerEmail((string) $order_information->channel->item->customeremail);

                    $billing_region_id = '';
                    if ($regionModel = Mage::getModel('directory/region')->loadByCode((string) $order_information->channel->item->customerstate, (string) $order_information->channel->item->customercountry)) {
                        $billing_region_id = $regionModel->getId();
                    }

                    $shipping_region_id = '';
                    if ($regionModel = Mage::getModel('directory/region')->loadByCode((string) $order_information->channel->item->shipstate, (string) $order_information->channel->item->shipcountry)) {
                        $shipping_region_id = $regionModel->getId();
                    }

                    //get products information
                    foreach ($order_information->channel->item->products->itemproducts as $product) {
                        // check if we have the product in the database
                        if (!$_product = Mage::getModel('catalog/product')->loadByAttribute('sku', (string) $product->productid)) {
                            $_product = Mage::getModel('catalog/product');

                            $_product->setWebsiteIds($store_id);
                            $_product->setSku((string) $product->productid);
                            $_product->setPrice((float) $product->price);
                            $_product->setAttributeSetId(4);
                            //$_product_types = Mage::getModel('catalog/product_type')->getTypes();
                            $_product->setTypeId('simple');
                            $_product->setName((string) $product->productid);
                            $_product->setDescription((string) $product->productid);
                            $_product->setShortDescription((string) $product->productid);
                            $_product->setStatus(1);
                            $_product->setTaxClassId('2');
                            $_product->setWeight(0);
                            $_product->setCreatedAt(strtotime('now'));
                            $_product->save();
                        } else {
                            $_product->setPrice((float) $product->price);
                            $_product->save();
                        }

                        //stock is a must for auto order creation
                        $stockItem = Mage::getModel('cataloginventory/stock_item');
                        $stockItem->assignProduct($_product);
                        $stockItem->setData('store_id', $store_id);
                        $stockItem->setData('stock_id', 1);
                        $stockItem->setData('is_in_stock', 1);
                        $stockItem->setData('manage_stock', 0);
                        $stockItem->setData('use_config_manage_stock', 0);
                        $stockItem->setData('min_sale_qty', 0);
                        $stockItem->setData('use_config_min_sale_qty', 0);
                        $stockItem->setData('max_sale_qty', 100000);
                        $stockItem->setData('use_config_max_sale_qty', 0);
                        $stockItem->save();

                        // add product(s)
                        $buyInfo = array(
                            'qty' => (float) $product->qty,
                                // custom option id => value id
                                // or
                                // configurable attribute id => value id
                        );
                        $quote->addProduct($_product, new Varien_Object($buyInfo));
                    }

                    $billingAddressData = array(
                        'firstname' => (string) $order_information->channel->item->customerfirstname,
                        'lastname' => (string) $order_information->channel->item->customerlastname,
                        'street' => (string) $order_information->channel->item->customeraddres1 . " " . (string) $order_information->channel->item->customeraddres2,
                        'city' => (string) $order_information->channel->item->customercity,
                        'postcode' => (string) $order_information->channel->item->customerzip,
                        'telephone' => (string) $order_information->channel->item->customerphone,
                        'country_id' => (string) $order_information->channel->item->customercountry,
                        'region' => (string) $order_information->channel->item->customerstate,
                        'region_id' => $billing_region_id,
                    );

                    $billingAddress = $quote->getBillingAddress()->addData($billingAddressData);
                    $billingAddress->setPaymentMethod('checkmo');

                    $shippingAddressData = array(
                        'firstname' => (string) $order_information->channel->item->customerfirstname,
                        'lastname' => (string) $order_information->channel->item->customerlastname,
                        'street' => (string) $order_information->channel->item->shipaddress1 . " " . (string) $order_information->channel->item->shipaddress2,
                        'city' => (string) $order_information->channel->item->shipcity,
                        'postcode' => (string) $order_information->channel->item->shipzip,
                        'telephone' => (string) $order_information->channel->item->shipphone,
                        'country_id' => (string) $order_information->channel->item->shipcountry,
                        'region' => (string) $order_information->channel->item->shipstate,
                        'region_id' => $shipping_region_id,
                    );
                    $shippingAddress = $quote->getShippingAddress()->addData($shippingAddressData);

                    $shipping_amount = (float) $order_information->channel->item->ordershippingcost;
                    $shipping_amount += (float) $order_information->channel->item->ordershippingcostdomestic;
                    $shipping_amount += (float) $order_information->channel->item->orderinsurancecost;

                    global $custom_bongo_shipping;
                    $custom_bongo_shipping = $shipping_amount;

                    $tax_amount = (float) $order_information->channel->item->ordertaxcost;
                    $billingAddress->setTotalAmount('tax', $tax_amount);
                    $billingAddress->setBaseTotalAmount('tax', $tax_amount);

                    global $custom_bongo_tax, $is_custom_bongo_tax;
                    $custom_bongo_tax = $tax_amount;
                    $is_custom_bongo_tax = true;

                    $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
                            ->setShippingMethod('flatrate_flatrate');

                    $quote->getPayment()->importData(array('method' => 'checkmo'));

                    $quote->collectTotals()->save();

                    $service = Mage::getModel('sales/service_quote', $quote);
                    $service->submitAll();
                    $_order = $service->getOrder();
                }

                //check status
                switch ($post['status']) {
                    case 'P':
                        //the order is pending by default
                        break;
                    case 'V':
                        //ship
                        if (!$_order->canShip()) {
                            die("ERROR:Cannot do shipment for the order.");
                        }
                        //disable the shipment notification for now
                        $data['comment_customer_notify'] = false;
                        $data['comment_text'] = '';
                        $data['send_email'] = '';
                        $shipment = false;
                        /**
                         * Check shipment create availability
                         */
                        $_items = $_order->getItemsCollection();
                        $savedQtys = array();
                        foreach ($_items as $_item) {
                            /*
                              //check if the item is shipped
                              $shipped = false;
                              $shipments = Mage::getModel('sales/order_shipment')->getCollection()
                              ->addAttributeToSelect('*')
                              ->addAttributeToFilter('sfsi.product_id', $_item->getProductId())
                              ->addAttributeToFilter('order_id', $order_real_id)
                              ->addAttributeToSort('main_table.entity_id', 'asc')
                              ;
                              $shipments->getSelect()
                              ->join(array('sfsi' => $_helper->getTableName('sales_flat_shipment_item')), 'sfsi.parent_id=main_table.entity_id', array())
                              ;
                              foreach ($shipments as $id => $shipment) {
                              $shipped = true;
                              break;
                              }
                              if ($shipped) {
                              continue;
                              } */

                            $savedQtys[$_item->getId()] = $_item->getQtyOrdered();
                        }

                        if (sizeof($savedQtys) > 0) {
                            $shipment = Mage::getModel('sales/service_order', $_order)->prepareShipment($savedQtys);

                            $tracking['carrier_code'] = 'custom';
                            $tracking['title'] = 'Bongo';
                            $tracking['number'] = '';
                            $track = Mage::getModel('sales/order_shipment_track')
                                    ->addData($tracking);
                            $shipment->addTrack($track);
                            if (!empty($data['comment_text'])) {
                                $shipment->addComment($data['comment_text'], isset($data['comment_customer_notify']));
                            }

                            if ($shipment) {
                                $shipment->register();

                                if (!empty($data['send_email'])) {
                                    $shipment->setEmailSent(true);
                                }

                                $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                                $shipment->getOrder()->setIsInProcess(true);
                                $transactionSave = Mage::getModel('core/resource_transaction')
                                        ->addObject($shipment)
                                        ->addObject($shipment->getOrder())
                                        ->save();
                                $shipment->sendEmail(!empty($data['send_email']), $comment);
                            }
                        }
                        break;
                    case 'B':
                    case 'C':
                        //cancel
                        $_order->cancel()->save();
                        break;
                }

                printf("SUCCESS:%s\n", $_order->getIncrementId());
                exit;

                return;
            } catch (Exception $e) {
                $translate->setTranslateInline(true);
                echo 'ERROR:' . $e->getMessage();
                exit;

                return;
            }
        } else {
            $this->_redirect('*/*/');
        }
    }

}