<?php

class Fera_Bananastand_Block_Footer_Checkout_Success extends Mage_Core_Block_Template
{

    /**
     * Get last order from Mage, JSONify it and return it.
     * @return JSON
     */
    public function getOrderInformation()
    {
        $order = Mage::getSingleton('sales/order');
        $order->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
        $bananaOrder = Mage::getModel('banana/order')->loadFromMageOrder($order);
        return $bananaOrder->getJson(); // returns JSON string of data ready to be sent to Fera API
    }
}
