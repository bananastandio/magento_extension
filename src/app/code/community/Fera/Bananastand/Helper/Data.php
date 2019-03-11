<?php

class Fera_Bananastand_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Write to the banana stand log file
     * @param  mixed $msg message to log
     * @return $this
     */
    public function log($msg)
    {
        Mage::log($msg, null, 'banana.log');

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
     * Log a global Magento exception and log to the banana log file that an exception occured.
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
     * Banana stand public key either from the store config or the environment files
     * @return string
     */
    public function getPublicKey()
    {
        if (isset($_SERVER['BANANA_PUBLIC_KEY'])) {
            return $_SERVER['BANANA_PUBLIC_KEY'];
        }
        if (isset($_ENV['BANANA_PUBLIC_KEY'])) {
            return $_ENV['BANANA_PUBLIC_KEY'];
        }
        return Mage::getStoreConfig('banana/banana_group/public_key');
    }

    /**
     * Banana stand secret (private) key, either from the environment fiels or the store config
     * @return string
     */
    public function getSecretKey()
    {
        if (isset($_SERVER['BANANA_SECRET_KEY'])) {
            return $_SERVER['BANANA_SECRET_KEY'];
        }
        if (isset($_ENV['BANANA_SECRET_KEY'])) {
            return $_ENV['BANANA_SECRET_KEY'];
        }
        return Mage::getStoreConfig('banana/banana_group/secret_key');
    }

    public function isEnabled()
    {
        if (isset($_SERVER['BANANA_ENABLED'])) {
            return $_SERVER['BANANA_ENABLED'] == '1';
        }
        if (isset($_ENV['BANANA_ENABLED'])) {
            return $_ENV['BANANA_ENABLED'] == '1';
        }

        if (!$this->isConfigured()) {
            return false;
        }

        return Mage::getStoreConfigFlag('banana/general/enabled');
    }

    /**
     * True if the current banana stand configuration is setup to work properly
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
        if (isset($_SERVER['BANANA_API_URL'])) {
            return $_SERVER['BANANA_API_URL'];
        }
        if (isset($_ENV['BANANA_API_URL'])) {
            return $_ENV['BANANA_API_URL'];
        }
        return "https://app.fera.ai/api/v1";
    }

    /**
     * The URL to the javascript file on the banana stand CDN. For example: https://cdn.fera.ai/js/bananastand.js
     * @return string
     */
    public function getJsUrl()
    {
        if (isset($_SERVER['BANANA_JS_URL'])) {
            return $_SERVER['BANANA_JS_URL'];
        }
        if (isset($_ENV['BANANA_JS_URL'])) {
            return $_ENV['BANANA_JS_URL'];
        }
        return "https://cdn.fera.ai/js/bananastand.js";
    }

    /**
     * Is debug mode enabled? If so we will output much more extra info to the logs to help developers.
     * @return boolean
     */
    public function isDebugMode()
    {
        if (isset($_SERVER['BANANA_DEBUG_MODE'])) {
            return $_SERVER['BANANA_DEBUG_MODE'] == '1';
        }
        if (isset($_ENV['BANANA_DEBUG_MODE'])) {
            return $_ENV['BANANA_DEBUG_MODE'] == '1';
        }
        return Mage::getStoreConfigFlag('banana/general/debug_mode');
    }

    /**
     * @return json - The contents of the cart as a json string.
     */
    public function getCartJson()
    {
        $cartItems = Mage::getModel('checkout/cart')->getItems();
        if (empty($cartItems)) {
          return "[]";
        }
        return json_encode($cartItems->getData());
    }

    /**
     * @return string - JS to trigger debug mode if required.
     */
    public function getDebugJs() {
        if ($this->isDebugMode()) {
            return "window.__bsioDebugMode = true;";
        }
        return "";
    }
}
