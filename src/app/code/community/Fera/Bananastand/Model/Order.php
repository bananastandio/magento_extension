<?php
 /**
 * This client handles the creation of order JSON for sending to Fera
 */
class Fera_Bananastand_Model_Order extends Varien_Object
{

    protected $order;

     /**
     * Set the order for this model
     * @param  Mage_Sales_Model_Order $order
     * @return $this
     */
    public function loadFromMageOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Format our mage order object into JSON for sending VIA js api
     * @return JSON
     */
    public function getJson()
    {
        $order = $this->order;
        $items = array();

        foreach ($order->getAllItems() as $item) {
            $product = $item->getProduct();
            $items[] = array(
                'name'     => $item->getName(),
                'total'    => $item->getPrice(),
                'quantity' => $item->getQtyOrdered(),
                'product'  => array(
                    'id'            => $product->getId(),
                    'name'          => $product->getName(),
                    'status'        => $product->getStatus(),
                    'in_stock'      => $product->isInStock(),
                    'url'           => $product->getProductUrl(),
                    'thumbnail_url' => $product->getThumbnailUrl()
                ),
            );
        }

        $customerId = $order->getCustomerId();
        $customer;
        if (!empty($customerId)) {
            $customerData = Mage::getModel('customer/customer')->load($customerId);
            $addressObj = $customerData->getPrimaryShippingAddress();
            $address;
            if (!empty($addressObj)) {
                $address = $addressObj->getData();
            }
            $customer = array(
                'id'            => $customerId,
                'first_name'    => $customerData->getFirstname(),
                'last_name'     => $customerData->getLastname(),
                'email'         => $customerData->getEmail(),
                'address'       => $address
            );
        }

        $currencyCode = $order->getOrderCurrencyCode();
        $total = $order->getGrandTotal();
        $totalUsd = Mage::helper('directory')->currencyConvert($total, $currencyCode, 'USD');
        $timeNow = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s');

        $orderData = array(
            'order_id'      => $order->getId(),
            'number'        => $order->getIncrementId(),

            'total'         => $total,
            'total_usd'     => $totalUsd,

            'created_at'    => $timeNow,
            'modified_at'   => $timeNow,

            'line_items'    => $items,

            'customer'      => $customer
        );

        return json_encode($orderData);
    }

}
