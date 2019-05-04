<?php

class Fera_Ai_Block_Footer_Checkout_Success extends Mage_Core_Block_Template
{

    /**
     * Get last order from Mage, JSONify it and return it.
     * @return JSON
     */
    public function getOrderInformation()
    {
        $order = Mage::getSingleton('sales/order');
        $order->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
        $feraOrder = Mage::getModel('fera_ai/order')->loadFromMageOrder($order);
        return $feraOrder->getJson(); // returns JSON string of data ready to be sent to Fera API
    }
}
