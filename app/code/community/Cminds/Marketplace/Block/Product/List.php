<?php

class Cminds_Marketplace_Block_Product_List extends Mage_Catalog_Block_Product_List {
    protected function _getProductCollection() {
        if ( is_null( $this->_productCollection ) ) {
            $layer                    = $this->getLayer();
            $productCollection        = $layer->getProductCollection();
            $this->_productCollection = $productCollection;
        }

        return $this->_productCollection;
    }
}