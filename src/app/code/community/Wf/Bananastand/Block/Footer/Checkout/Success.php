<?php

class Wf_Bananastand_Block_Footer_Checkout_Success extends Mage_Core_Block_Template
{

    /**
     * Get last order from Mage, JSONify it and return it.
     * @return JSON
     */
    public function getOrderInformation()
    {
        $order = Mage::getSingleton('sales/order');
        $order->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
        return Mage::helper('banana')->jsonifyOrder($order);
    }
}
