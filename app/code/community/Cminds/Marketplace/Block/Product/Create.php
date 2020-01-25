<?php
class Cminds_Marketplace_Block_Product_Create extends Cminds_Supplierfrontendproductuploader_Block_Product_Create
{
    private $_allowedCategories = false;
    public function _construct()
    {
        parent::_construct();
    }

    public function getAllowedCategories() {
        if(!$this->_allowedCategories) {
            $categories = Mage::getModel('marketplace/categories')->getCollection()->addFilter('supplier_id', Mage::helper('marketplace')->getSupplierId());
            $this->_allowedCategories = array();

            foreach($categories AS $category) {
                $this->_allowedCategories[] = $category->getCategoryId();
            }
        }
        return $this->_allowedCategories;
    }

    public function isEditMode()
    {
        $requestParams = Mage::registry('cminds_configurable_request');

        if (Mage::registry('supplier_product_id')) {
            return true;
        }

        if(isset($requestParams['id']) && !isset($requestParams['attribute_set_id'])) {
            return true;
        } elseif(!isset($requestParams['id']) && isset($requestParams['attribute_set_id'])) {
            return false;
        } else {
            if(Mage::registry('is_configurable')) {
                throw new Exception();
            } else {
                return false;
            }
        }

    }

    public function getNodes($categories) {
        $str = '';
        $allowedCategories = $this->getAllowedCategories();

        foreach($categories AS $category) {
            $cat = Mage::getModel('catalog/category')->load($category->getEntityId());

            if(in_array($cat->getId(), $allowedCategories)) continue;
            if($this->_checkAvailableForSupplier($cat->getPath())) continue;

            $str .= $this->_renderCategory($cat);
        }

        return $str;
    }

    public function getAvailableAttributeSets() {
        $s = Mage::getModel('eav/entity_attribute_set')->getCollection()->addFieldToFilter('available_for_supplier', 1);
        return $s;
    }

    public function getProductId() {
        $product = Mage::registry('product_object');
        return $product->getId();
    }

    public function getAttributeSetId()
    {
        if (!$this->isEditMode()) {
            if (Mage::registry('is_configurable')) {
                $requestParams = Mage::registry('cminds_configurable_request');
                return $requestParams['attribute_set_id'];
            } else {
                $params = Mage::app()->getFrontController()->getRequest()->getParams();

                if (!isset($params['attribute_set_id']) || !$params['attribute_set_id']) {
                    $configAttributeSet = Mage::getStoreConfig('supplierfrontendproductuploader_products/supplierfrontendproductuploader_catalog_config/attribute_set');
                } else {
                    $configAttributeSet = $params['attribute_set_id'];
                }

                return $configAttributeSet;
            }
        } else {
            $product = $this->getProduct();
            return $product->getAttributeSetId();
        }
    }

    public function getAttributes() {
        $attributesCollection = Mage::getModel('catalog/product_attribute_api')->items($this->getAttributeSetId());
        return $attributesCollection;
    }

    public function isMarketplaceEnabled() {
        $cmindsCore = Mage::getModel("cminds/core");

        if($cmindsCore) {
            $cmindsCore->validateModule('Cminds_Marketplace');
        } else {
            throw new Mage_Exception('Cminds Core Module is disabled or removed');
        }
    }

    protected function _checkAvailableForSupplier($categoriesPath) {
        $pathCategoryIds = explode('/', $categoriesPath);
        foreach($pathCategoryIds as $categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            if($category->getData('available_for_supplier') == 0) return true;
        }
    }
}