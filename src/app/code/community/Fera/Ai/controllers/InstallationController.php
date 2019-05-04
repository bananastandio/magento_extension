<?php
class Fera_Ai_InstallationController extends Mage_Core_Controller_Front_Action
{
    protected function _getHelper()
    {
        return Mage::helper('fera_ai');
    }


    public function checkAction()
    {
        try {
            $params = $this->getRequest()->getParams();

            if (Mage::getStoreConfig('fera_ai/fera_ai_group/secret_key') != $params['sk']) {
                die("Invalid key");
            }

            Mage::app()->cleanCache();

            $responseData = [
                "version" =>  ((array)Mage::getConfig()->getNode('modules/Fera_Ai/version'))[0],
                "public_key" => $this->_getHelper()->getPublicKey(),
                "api_url" => $this->_getHelper()->getApiUrl(),
                "js_url" => $this->_getHelper()->getJsUrl()
            ];

            $response = json_encode($responseData);
        } catch (Mage_Core_Exception $e) {
            $response = $e->getMessage();
        } catch (Exception $e) {
            $response = 'Error: System error during request';
        }
        
        $this->getResponse()->setBody($response);
        return $this;
    }


    /**
     */
    public function autoconfigureAction()
    {
        try {
            $params = $this->getRequest()->getParams();

            if (Mage::getStoreConfig('fera_ai/fera_ai_group/secret_key')) {
                die("This account has already been auto-configured and cannot be auto-configured again. If you want to update the API credentials please go to the System settings and enter in the API credentials manually.");
            }

            Mage::getConfig()->saveConfig('fera_ai/fera_ai_group/secret_key', $params['sk']);
            Mage::getConfig()->saveConfig('fera_ai/fera_ai_group/public_key', $params['pk']);

            if ($params['api_url']) {
                Mage::getConfig()->saveConfig('fera_ai/fera_ai_group/api_url', $params['api_url']);
            }

            if ($params['js_url']) {
                Mage::getConfig()->saveConfig('fera_ai/fera_ai_group/js_url', $params['js_url']);
            }

            Mage::app()->cleanCache();

            $response = "Automatic confiuration was successful. You may now close this window.";
        } catch (Mage_Core_Exception $e) {
            $response = $e->getMessage();
        } catch (Exception $e) {
            $response = 'Error: System error during request';
        }
        $this->getResponse()->setBody($response);
        return $this;
    }

}
