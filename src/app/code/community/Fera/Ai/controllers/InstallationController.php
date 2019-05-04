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
                "version" =>  $this->_getHelper()->getVersion(),
                "public_key" => $this->_getHelper()->getPublicKey(),
                "api_url" => $this->_getHelper()->getApiUrl(),
                "js_url" => $this->_getHelper()->getJsUrl(),
                "magento_version" => Mage::getVersionInfo()
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
            $response = "<html>";

            if ($params['latest_ver'] !== $this->_getHelper()->getVersion()) {
                $response .= "<p>✘ You may be running an out-dated version of the Magento extension. ".
                             "Please download the latest files and try again. ".
                             "It looks like you're running v". $this->_getHelper()->getVersion() .
                             " and the latest is v". $params['latest_ver'] .".</p>";
            } else {
                $response .= "<p>✓ You appear to be running the latest version of the extension (v". $params['latest_ver'] .")</p>";
            }

            if (Mage::getStoreConfig('fera_ai/fera_ai_group/secret_key')) {
                $response .= "<p>* This account has already been auto-configured and cannot be auto-configured again. ".
                             "If you want to update the API credentials please go to the System settings and enter ".
                             "in the API credentials manually.</p>";
            } else {
                Mage::getConfig()->saveConfig('fera_ai/fera_ai_group/secret_key', $params['sk']);
                Mage::getConfig()->saveConfig('fera_ai/fera_ai_group/public_key', $params['pk']);

                if ($params['api_url']) {
                    Mage::getConfig()->saveConfig('fera_ai/fera_ai_group/api_url', $params['api_url']);
                }

                if ($params['js_url']) {
                    Mage::getConfig()->saveConfig('fera_ai/fera_ai_group/js_url', $params['js_url']);
                }

                $response .= "<p>✓ Automatic confiuration was successful. You may now close this window.</p>";

            }
            $response .= "</html>";

            Mage::app()->cleanCache();
        } catch (Mage_Core_Exception $e) {
            $response = $e->getMessage();
        } catch (Exception $e) {
            $response = 'Error: System error during request';
        }
        $this->getResponse()->setBody($response);
        return $this;
    }

}
