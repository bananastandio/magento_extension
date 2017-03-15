<?php

/**
 * This client handles all communication with the banana stand server
 */
class Wf_Bananastand_Model_Client extends Varien_Object
{

    /**
     * Send the add to cart event to the banana stand server.
     * @param  Mage_Catalog_Model_Product $product    
     * @param  Integer|null $customerId Customer ID if it is available, null otherwise
     * @param  String|null $visitorId  Visitor ID if it is available, null otherwise
     * @return $this
     */
    public function sendAddToCartEvent($product, $customerId = null, $visitorId = null)
    {
        $startMicroTime = microtime(true);

        $this->pushEvent('add_to_cart', $product->getId(), $customerId, $visitorId);

        Mage::helper('banana')->log("Add to Cart event was sent with product ID {$product->getId()}. "
            ."The request took ". (microtime(true)-$startMicroTime) ." seconds.");

        return $this;
    }

    /**
     * Send a new order event to the banaan stand server
     * @param  Mage_Sales_Model_Order $order     Order that was placed
     * @param  string|null $visitorId Visitor ID if available
     * @return $this
     */
    public function sendOrderEvent($order, $visitorId = null)
    {
        $startMicroTime = microtime(true);

        $productIdsStr = $this->getProductIds($order);
        $customerId = $order->getCustomerId();

        $this->pushEvent('order', $productIdsStr, $customerId, $visitorId);

        Mage::helper('banana')->log("New order event was sent with order ID #{$order->getId()} ands product "
            ."IDs [{$productIdsStr}] to the banana stand server. The request took ". (microtime(true)-$startMicroTime) 
            ." seconds.");

        return $this;
    }

    protected function getProductIds($order)
    {
        $productIds = array();

        foreach($order->getAllItems() as $item) {
            $productIds[] = $item->getProductId();
        }

        return implode(',', $productIds);
    }

    protected function pushEvent($eventTypeCode, $productIdOrIds, $customerId, $visitorId = null)
    {
        // Sanitize customer ID:
        $customerId = empty($customerId) ? '0' : $customerId; // 0 = we don't know the customer id.
        
        // Sanitize visitor ID
        $visitorId = is_string($visitorId) ? str_ireplace(' path=/', '', $visitorId) : $visitorId; 

        $url = $this->getApiEventsUrl($eventTypeCode, $productIdOrIds, $customerId, $visitorId);

        Mage::helper('banana')->debug("GET {$url}");

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); # 2 Second timeout limit

        $return = curl_exec($ch);
        curl_close($ch);

        return $this;
    }

    protected function getApiEventsUrl($eventTypeCode, $productIdOrIds, $customerId, $visitorId = null)
    {
        $url = Mage::helper('banana')->getApiUrl();
        $url .= "/stores/" . Mage::helper('banana')->getPublicKey();

        $url .= '/push_event/' . $eventTypeCode;
        $url .= '/p/' . $productIdOrIds;
        $url .= '/c/' . $customerId . ".png";

        if ($visitorId) {
            $url .= "?visitor_id=" . $visitorId;
        }

        return $url;
    }
}
