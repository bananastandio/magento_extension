<?php

class Fera_Bananastand_Block_Footer_Product_View extends Mage_Core_Block_Template
{
    /**
     * Retrieve current product model
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!Mage::registry('product') && $this->getProductId()) {
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
            Mage::register('product', $product);
        }
        return Mage::registry('product');
    }

    public function getProductId()
    {
        return $this->getProduct()->getId();
    }
}
