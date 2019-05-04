<?php

class Fera_Ai_Block_Footer_Product_View extends Mage_Core_Block_Template
{
    protected $_product;
    /**
     * Retrieve current product model
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            if ($this->getData('product_id')) {
                $this->_product = Mage::getModel('catalog/product')->load($this->getData('product_id'));
            } else {
                $this->_product = Mage::registry('product');
            }
        }
        return $this->_product;
    }

    public function getProductJson()
    {
        $p = $this->getProduct();

        $thumb = $p->getThumbnailUrl();
        $productData = [
            "id" =>               $p->getId(), // String
            "name" =>             $p->getName(), // String
            "price" =>             $p->getFinalPrice(), // Float
            "status" =>           $p->getStatus() == 1 ? 'published' : 'draft', // (Optional) String
            "created_at" =>       (new DateTime($p->getCreatedAt()))->format(DateTime::ATOM), // (Optional) String (ISO 8601 format DateTime) 
            "modified_at" =>      (new DateTime($p->getUpdatedAt()))->format(DateTime::ATOM), // (Optional) String (ISO 8601 format DateTime) 
            "stock" =>            $p->getStockItem()->getQty(), // (Optional) Integer, If null assumed to be infinite.
            "in_stock" =>         $p->isInStock(), // (Optional) Boolean
            "url" =>              $p->getUrl(), // String
            "thumbnail_url" =>    $thumb, // String
            "needs_shipping" =>   $p->getTypeId() != 'virtual', // (Optional) Boolean
            "hidden" =>           $p->getVisibility() == '1', // (Optional) Boolean
            'tags' =>             [],
            "variants" =>         [], // (Optional) Array<Variant>: Variants that are applicable to this product.
            "platform_data" => [ // (Optional) Hash/Object of attributes to store about the product specific to the integration platform (can be used in future filters)
                "sku" => $p->getSku(),
                "type" => $p->getTypeId(),
                "regular_price" => $p->getPrice()
            ]
        ];

        $tags = Mage::getModel('tag/tag')->getResourceCollection()
                ->addPopularity()
                ->addStatusFilter(Mage::getModel('tag/tag')->getApprovedStatus())
                ->addProductFilter($p->getId());
        foreach($tags as $tag) {
            $productData['tags'][] = $tag->getName();
        }

        if ($p->getTypeId() == 'configurable') {
            $cfgAttr = $p->getTypeInstance()->getConfigurableAttributesAsArray();
            foreach ($p->getTypeInstance()->getUsedProducts() as $subProduct) {
                $variant = [
                    "id" =>               $subProduct->getId(),
                    "name" =>             $subProduct->getName(), // String
                    "status" =>           $subProduct->getStatus() == 1 ? 'published' : 'draft', // (Optional) String
                    "created_at" =>       (new DateTime($subProduct->getCreatedAt()))->format(DateTime::ATOM), // (Optional) String (ISO 8601 format DateTime) 
                    "modified_at" =>      (new DateTime($subProduct->getUpdatedAt()))->format(DateTime::ATOM), // (Optional) String (ISO 8601 format DateTime) 
                    "stock" =>            $subProduct->getStockItem()->getQty(), // (Optional) Integer, If null assumed to be infinite.
                    "in_stock" =>         $subProduct->isInStock(), // (Optional) Boolean
                    "price" =>            $subProduct->getPrice(), // Float
                    "platform_data" => [ // (Optional) Hash/Object of attributes to store about the product specific to the integration platform (can be used in future filters)
                        "sku" => $subProduct->getSku()
                    ]
                ];

                $variantImage = $subProduct->getThumbnailUrl();
                if ($variantImage != $thumb && stripos($variantImage, '/placeholder') === false){
                    $variant['thumbnail_url'] = $subProduct->getThumbnailUrl();
                }

                $variantAttrVals = [];
                foreach ($cfgAttr as $attr) {
                    $attrValIndex = $subProduct->getData($attr['attribute_code']);
                    foreach ($attr['values'] as $attrVal) {
                        if ($attrVal['value_index'] == $attrValIndex) {
                            $variantAttrVals[] = $attrVal['label'];
                        }
                    }
                    # code...
                }
                $variant['name'] = implode(' / ', $variantAttrVals);

                $productData['variants'][] = $variant;
            }
        }

        return json_encode($productData);
    }

    public function getProductId()
    {
        return $this->getProduct()->getId();
    }
}
