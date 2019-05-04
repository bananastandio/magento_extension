<?php
 /**
 * This client handles the creation of order JSON for sending to Fera
 */
class Fera_Ai_Model_Order extends Varien_Object
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

        $customerId = $order->getCustomerId();
        if (!empty($customerId)) {
            $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
            $addressObj = $customer->getPrimaryShippingAddress();
            $address;
            if (!empty($addressObj)) {
                $address = $addressObj->getData();
            }
            $customer = array(
                'id'            => $customerId,
                'first_name'    => $customer->getFirstname(),
                'email'         => $customer->getEmail(),
                'address'       => $address
            );
        }

        $currencyCode = $order->getOrderCurrencyCode();
        $total = $order->getGrandTotal();
        $totalUsd = Mage::helper('directory')->currencyConvert($total, $currencyCode, 'USD');

        $orderData = array(
            'id'            => $order->getId(),
            'number'        => $order->getIncrementId(),

            'total'         => $total,
            'total_usd'     => $totalUsd,

            'created_at'    => (new DateTime($order->getCreatedAt()))->format(DateTime::ATOM),
            'modified_at'   => (new DateTime($order->getUpdatedAt()))->format(DateTime::ATOM),

            'line_items'    => Mage::helper('fera_ai')->serializeQuoteItems($order->getItemsCollection()),

            'customer'      => $customer,
            'source_name'   => 'web'
        );

        return json_encode($orderData);
    }

}
