<?php

class Fera_Ai_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Write to the Fera.ai log file
     * @param  mixed $msg message to log
     * @return $this
     */
    public function log($msg)
    {
        Mage::log($msg, null, 'fera_ai.log');

        return $this;
    }

    /**
     * Write to the debug output ONLY if the debug mode is enabled
     * @param  mixed $msg Message to log
     * @return $this
     */
    public function debug($msg)
    {
        if ($this->isDebugMode()) {
            return $this->log($msg);
        }

        return $this;
    }

    /**
     * Log a global Magento exception and log to the Fera.ai log file that an exception occured.
     * @param  mixed $msg Message to attach that explains why the exception may have occurred
     * @param  Exception $e   Error that actually occurred
     * @return $this
     */
    public function logException($msg, $e)
    {
        Mage::logException($e);

        return $this->log($msg);
    }
    
    /**
     * @return     String Version of the extension (x.x.x)
     */ 
    public function getVersion()
    {
        $cfg = (array) Mage::getConfig()->getNode('modules/Fera_Ai/version');
        return $cfg[0];
    }
    
    /**
     * Fera Ai public key either from the store config or the environment files
     * @return string
     */
    public function getPublicKey()
    {
        if (isset($_SERVER['FERA_AI_PUBLIC_KEY'])) {
            return $_SERVER['FERA_AI_PUBLIC_KEY'];
        }
        return Mage::getStoreConfig('fera_ai/fera_ai_group/public_key');
    }

    /**
     * Fera Ai secret (private) key, either from the environment fiels or the store config
     * @return string
     */
    public function getSecretKey()
    {
        if (isset($_SERVER['FERA_AI_SECRET_KEY'])) {
            return $_SERVER['FERA_AI_SECRET_KEY'];
        }
        return Mage::getStoreConfig('fera_ai/fera_ai_group/secret_key');
    }

    public function isEnabled()
    {
        if (isset($_SERVER['FERA_AI_ENABLED'])) {
            return $_SERVER['FERA_AI_ENABLED'] == '1';
        }

        if (!$this->isConfigured()) {
            return false;
        }

        return Mage::getStoreConfigFlag('fera_ai/general/enabled');
    }

    /**
     * True if the current Fera Ai configuration is setup to work properly
     * @return boolean false if it is not ready for use
     */
    public function isConfigured()
    {
        $publicKey = $this->getPublicKey();
        $secretKey = $this->getSecretKey();
        return !empty($publicKey) && !empty($secretKey);
    }

    /**
     * The URL path to the API (https). For example: https://api.fera.ai/api/v1
     * @return string
     */
    public function getApiUrl()
    {
        if (isset($_SERVER['FERA_AI_API_URL'])) {
            return $_SERVER['FERA_AI_API_URL'];
        }

        $urlFromConfig = Mage::getStoreConfig('fera_ai/fera_ai_group/api_url');
        if ($urlFromConfig) {
            return $urlFromConfig;
        }
        return "https://app.fera.ai/api/v2";
    }

    /**
     * The URL to the javascript file on the Fera CDN. For example: https://cdn.fera.ai/js/bananastand.js
     * @return string
     */
    public function getJsUrl()
    {
        if (isset($_SERVER['FERA_AI_JS_URL'])) {
            return $_SERVER['FERA_AI_JS_URL'];
        }
        
        $urlFromConfig = Mage::getStoreConfig('fera_ai/fera_ai_group/js_url');
        if ($urlFromConfig) {
            return $urlFromConfig;
        }

        return "https://cdn.jsdelivr.net/gh/feracommerce/ferajs@latest/dist/fera.js";
    }

    /**
     * Is debug mode enabled? If so we will output much more extra info to the logs to help developers.
     * @return boolean
     */
    public function isDebugMode()
    {
        if (isset($_SERVER['FERA_AI_DEBUG_MODE'])) {
            return $_SERVER['FERA_AI_DEBUG_MODE'] == '1';
        }
        return Mage::getStoreConfigFlag('fera_ai/general/debug_mode');
    }

    public function serializeQuoteItems($items)
    {
        $configurableItems = [];
        $itemMap = [];
        $childItems = [];
        foreach ($items as $cartItem) {

            if ($cartItem->getParentItemId()) {
                $childItems[] = $cartItem;
            } else {
                $itemMap[$cartItem->getId()] = [
                    'product_id' => $cartItem->getProductId(),
                    'price' => $cartItem->getPrice(),
                    'total' => $cartItem->getRowTotal(),
                    'name' => $cartItem->getName()
                ];
                if ($cartItem->getProductType() == 'configurable') {
                    $configurableItems[$cartItem->getId()] = $itemMap[$cartItem->getId()];
                }
            }

        }

        foreach ($childItems as $cartItem) {
            if ($configurableItems[$cartItem->getParentItemId()]) {
                // product is configurable
                $itemMap[$cartItem->getParentItemId()]['name'] = $cartItem->getName();
                $itemMap[$cartItem->getParentItemId()]['variant_id'] = $cartItem->getProductId();
            } else {
                // product is bundle or something else, just add it as a normal item

                $itemMap[$cartItem->getId()] = [
                    'product_id' => $cartItem->getProductId(),
                    'price' => $cartItem->getPrice(),
                    'total' => $cartItem->getRowTotal(),
                    'name' => $cartItem->getName()
                ];
            }
        }

        return array_values($itemMap);
    }

    /**
     * @return json - The contents of the cart as a json string.
     */
    public function getCartJson()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $data = [
            'currency' => Mage::app()->getStore()->getCurrentCurrencyCode(),
            'total' => $quote->getSubtotal(),
            'grand_total' => $quote->getGrandTotal()
        ];

        $data['items'] = $this->serializeQuoteItems($quote->getItemsCollection());

        return json_encode($data);
    }


    /**
     * @return string - JS to trigger debug mode if required.
     */
    public function getDebugJs() {
        if ($this->isDebugMode()) {
            return "window.feraDebugMode = true;";
        }
        return "";
    }
}
