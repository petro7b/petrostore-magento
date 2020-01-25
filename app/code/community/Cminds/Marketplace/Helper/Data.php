<?php
class Cminds_Marketplace_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getAllShippingMethods() {
        $methods = array();
        $config = Mage::getStoreConfig('carriers');
        foreach ($config as $code => $methodConfig) {
            if(!isset($methodConfig['title'])) continue;
            $methods[$code] = $methodConfig['title'];
        }

        return $methods;
    }

    public function hasAccess()
    {
        $cmindsCore = Mage::getModel("cminds/core");

        if ($cmindsCore) {
            $cmindsCore->validateModule('Cminds_Marketplace');
        } else {
//            throw new Mage_Exception('Cminds Core Module is disabled or removed');
        }

        $supplierHelper = Mage::helper("supplierfrontendproductuploader");
        return $supplierHelper->validateLoggedInUser();
    }

    /**
     * Check supplier profile fully filed.
     *
     * If config marketplace_configuration/general/check_profile_fully is set on "YES"
     * and profile is not fully filed create redirect to profile settings page.
     *
     * @param Mage_Customer_Model_Customer $customer
     */
    public function checkSupplierProfileFully(Mage_Customer_Model_Customer $customer)
    {
        $checkProfileFully = (bool) Mage::getStoreConfig('marketplace_configuration/general/check_profile_fully');
        $supplierPageEnabled = (bool) Mage::getStoreConfig('marketplace_configuration/general/supplier_page_enabled');
        $currentAction  =  Mage::app()->getFrontController()->getRequest()->getActionName();
        $currentController  =  Mage::app()->getFrontController()->getRequest()->getControllerName();
        $currentModule  =  Mage::app()->getFrontController()->getRequest()->getModuleName();

        $settingsProfilePage = false;

        if ($currentAction === 'profile' && $currentController === 'settings') {
            $settingsProfilePage = true;
        }

        if ($currentModule != "supplier" && $currentModule != "marketplace") {
            $settingsProfilePage = true;
        }

        if ($settingsProfilePage === false
            && $checkProfileFully
            && $supplierPageEnabled
            && !$this->checkSupplierRequiredCustomFields($customer)
        ) {
            Mage::getSingleton('core/session')->addError(
                'You need file required fields in profile. If you did this you need wait to admin will approve profile.'
            );
            session_write_close();
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getBaseUrl() . 'marketplace/settings/profile');
        }
    }

    public function getImageDir($postData)
    {
        $path = Mage::getBaseDir('media').'/catalog/product';
        return $path;
    }

    public function getSupplierId()
    {
        if($this->hasAccess()) {
            $loggedUser = Mage::getSingleton( 'customer/session', array('name' => 'frontend') );
            $customer = $loggedUser->getCustomer();

            return $customer->getId();
        }

        return false;
    }

    public function getLoggedSupplier()
    {
        $loggedUser = Mage::getSingleton('customer/session', array('name' => 'frontend'));
        $c = $loggedUser->getCustomer();
        $customer = Mage::getModel('customer/customer')->load($c->getId());

        return $customer;
    }

    public function getSupplierLogo($supplier_id = false)
    {
        $supplierHelper = Mage::helper("supplierfrontendproductuploader");
        if (!$supplier_id) {
            $supplier = $this->getLoggedSupplier();
        } else {
            $supplier = Mage::getModel('customer/customer')->load($supplier_id);
            if (!$supplierHelper->isSupplier($supplier)) {
                Mage::throwException($this->__("This customer is not supplier"));
            }
        }

        $path = Mage::getBaseDir('media') . DS . 'supplier_logos' . DS;
        $path  .= $supplier->getSupplierLogo();

        if (!file_exists($path) || !$supplier->getSupplierLogo()) {
            return false;
        } else {
            return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'supplier_logos' . DS . $supplier->getSupplierLogo();
        }
    }
    
    
    // Function Background
    public function getSupplierBackground($supplier_id = false)
    {
        $custid = $this->getSupplierId();
        
        $sql = "SELECT id_cover FROM customer_entity WHERE entity_id='$custid'";
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		$hasil = $connection->fetchOne($sql);
		
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'supplier_cover' . DS . $hasil;
    }
    
    
    public function insertCover($newName) {
        $custid = $this->getSupplierId();
                    
        $sql = "UPDATE customer_entity SET id_cover='$newName' WHERE entity_id='$custid'";
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		$hasil = $connection->query($sql);
                    
                    
    }
    
    public function removeCover($newName) {
        $custid = $this->getSupplierId();
        $new = '0';
                    
        $sql = "UPDATE customer_entity SET id_cover='$new' WHERE entity_id='$custid'";
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		$hasil = $connection->query($sql);
                    
                    
    }
    // end

    public function getSupplierProducts($id)
    {
        $collection = Mage::getResourceModel('supplierfrontendproductuploader/product_collection')
            ->addAttributeToSelect('*')
            ->filterBySupplier($id)
            ->filterByFrontendproductStatus('active')
            ->addAttributeToFilter('is_saleable', array('like' => '1'));

        $collection->addWebsiteFilter();
        $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents();

        Mage::getSingleton('catalog/product_visibility')
            ->addVisibleInCatalogFilterToCollection($collection);

        Mage::getSingleton('catalog/product_status')
            ->addVisibleFilterToCollection($collection);

        $collection
            ->addAttributeToFilter('is_saleable', array('like' => '1'));

        return $collection;
    }

    public function isOwner($_product, $supplier_id = false)
    {
        if (!$supplier_id) {
            $supplier_id = $this->getSupplierId();
        }

        $owner_id = $this->getSupplierIdByProductId($_product);

        return $supplier_id == $owner_id;
    }

    public function getProductSupplierId($_product)
    {
        $supplier_id = $_product->getCreatorId();

        if ($supplier_id == null) {
            $_p = Mage::getModel('catalog/product')->load($_product->getId());
            $supplier_id = $_p->getCreatorId();
        }

        return $supplier_id;
    }

    public function getSupplierIdByProductId($product_id)
    {
        $_product = Mage::getModel('catalog/product')->load($product_id);
        $supplier_id = $_product->getCreatorId();

        return $supplier_id;
    }

    public function getSupplierPageUrl($product)
    {
        if ($product->getCreatorId()) {
            return $this->getSupplierRawPageUrl($product->getCreatorId());
        }
    }

    public function getSupplierRawPageUrl($customer_id)
    {
        $customerPathId = 'marketplace_vendor_url_' . $customer_id;
        $url = Mage::getModel('core/url_rewrite')->load($customerPathId, 'id_path');

        if (!$url->getId()) {
            $customer = Mage::getModel('customer/customer')->load($customer_id);
            $iDefaultStoreId = Mage::app()
                ->getWebsite($customer->getWebsiteId())
                ->getDefaultGroup()
                ->getDefaultStoreId();

            return Mage::getUrl('marketplace/supplier/view', array(
                'id' => $customer_id,
                '_store' => $iDefaultStoreId
            ));
        } else {
            return Mage::getUrl($url->getRequestPath(), array(
                '_store' => $url->getStoreId(),
            ));
        }
    }

    public function setSupplierDataInstalled($installed)
    {
        @mail('info@cminds.com', 'Marketplace installed', "IP: " . $_SERVER['SERVER_ADDR'] . " host : ". $_SERVER['SERVER_NAME']);
    }

    public function getMaxImages()
    {
        $imagesCount = Mage::getStoreConfig(
            'supplierfrontendproductuploader_products/supplierfrontendproductuploader_catalog_config/images_count'
        );

        if ($imagesCount === null || $imagesCount === '') {
            $imagesCount = 0;
        }

        $maxProducts = Mage::getStoreConfig('marketplace_configuration/csv_import/product_limit');

        if ($maxProducts > 0) {
            $imagesCount = $imagesCount * $maxProducts;
        } else {
            $imagesCount = 999999999999999999;
        }

        return $imagesCount;
    }

    public function supplierPagesEnabled()
    {
        return Mage::getStoreConfig(
            'marketplace_configuration/general/supplier_page_enabled'
        );
    }

    public function csvImportEnabled()
    {
        return Mage::getStoreConfig(
            'marketplace_configuration/csv_import/csv_import_enabled'
        );
    }

    public function canUploadLogos()
    {
        return Mage::getStoreConfig(
            'marketplace_configuration/suppliers/upload_logos'
        );
    }
    
    // Function Upload Background
        public function canUploadBackgrounds()
    {
        return Mage::getStoreConfig(
            'marketplace_configuration/suppliers/upload_cover'
        );
    }
    // end

    /**
     * Retrieve configuration if vendor can change status in
     * order details page in his portal
     *
     * @return bool
     */
    public function canChangeOrderStatus()
    {
        return Mage::getStoreConfig(
            'marketplace_configuration/orders/can_change_order_status'
        );
    }

    /**
     * Retrieve available order statuses vendor can select to change
     *
     * @return mixed
     */
    public function getAvailableVendorStatuses()
    {
        return explode(
            ',',
            Mage::getStoreConfig(
                'marketplace_configuration/orders/available_order_statuses'
            )
        );
    }

    public function getStatusesCanSee()
    {
        return explode(
            ',',
            Mage::getStoreConfig(
                'marketplace_configuration/presentation/what_order_supplier_see'
            )
        );
    }

    /**
     * Retrieve config of showing payment information in vendor dashboard
     *
     * @return bool
     */
    public function canShowPaymentInfo()
    {
        return Mage::getStoreConfig(
            'marketplace_configuration/orders/can_show_payment_info'
        );
    }

    /**
     * Retrieve config of showing shipping information in vendor dashboard
     *
     * @return bool
     */
    public function canShowShippingInfo()
    {
        return Mage::getStoreConfig(
            'marketplace_configuration/orders/can_show_shipping_info'
        );
    }


    /**
     * Retrieve config of showing notice on cart page
     *
     * @return bool
     */
    public function canShowCartNotice()
    {
        return Mage::getStoreConfig(
            'marketplace_configuration/general/show_cart_notice'
        );
    }

    public function getSupplierSoldBy($sku)
    {
        $productId = Mage::getModel('catalog/product')->getResource()->getIdBySku($sku);
        $product = Mage::getModel('catalog/product')->load($productId);
        if($product->getCreatorId()) {
                return ('<p>by<a href=' . $this->getSupplierRawPageUrl($product->getCreatorId()) .'> ' . $this->getSupplierName($product->getCreatorId()) . '</a></p>');
        }
    }

    public function getSupplierName($creatorId)
    {
        $customer = Mage::getModel('customer/customer')->load($creatorId);

        if($customer->getSupplierName()) {
            return $customer->getSupplierName();
        } else {
            return sprintf("%s %s", $customer->getFirstname(), $customer->getLastname());
        }
    }

    /**
     * Return false if supplier dosent have set all required custom fields.
     *
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return bool
     */
    public function checkSupplierRequiredCustomFields(Mage_Customer_Model_Customer $customer)
    {
        $customFieldsCollection = Mage::getModel('marketplace/fields')->getCollection()
            ->addFieldToFilter('is_required',1);
        $customFieldsValue = $this->getCustomFieldsValues($customer);

        if(!$customer->getSupplierName()) {
            return false;
        }

        if(!$customer->getSupplierDescription()) {
            return false;
        }

        foreach($customFieldsCollection AS $field) {

            if(empty($customFieldsValue[$field['name']])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get customer custom fields values.
     *
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return array
     */
    public function getCustomFieldsValues(Mage_Customer_Model_Customer $customer)
    {
        $dbValues = unserialize($customer->getCustomFieldsValues());
        $ret = array();
        if (!is_array($dbValues)) {
            $dbValues = array();
        }

        foreach ($dbValues as $value) {
            $v = Mage::getModel('marketplace/fields')->load($value['name'], 'name');

            if (isset($v)) {
                $ret[$value['name']] = $value['value'];
            }
        }

        return $ret;
    }

    public function isBillingReportInclTax()
    {
        return Mage::getStoreConfig(
            'marketplace_configuration/billing_reports/amount_calculation'
        );
    }

    /**
     * Retrieve store timezone
     *
     * @return DateTimeZone
     */
    public function getStoreTimezone()
    {
        $configTimezone = Mage::getStoreConfig('general/locale/timezone');

        return new DateTimeZone($configTimezone);
    }

    /**
     * Get Time Filter 'From' for current store in UTC format time
     *
     * @param $dateFrom
     *
     * @return DateTime
     */
    public function getTimeFilterFrom($dateFrom)
    {
        $date = new DateTime($dateFrom . ' 00:00:00', $this->getStoreTimezone());

        return $date->setTimezone(new DateTimeZone('UTC'));
    }

    /**
     * Get Time Filter 'To' for current store in UTC format time
     *
     * @param $dateTo
     *
     * @return DateTime
     */
    public function getTimeFilterTo($dateTo)
    {
        $date = new DateTime($dateTo . ' 23:59:59', $this->getStoreTimezone());
        
        return $date->setTimezone(new DateTimeZone('UTC'));
    }

    /**
     * Get the difference between the time of the store and the UTC time
     *
     * @param string $storeTime
     *
     * @return string
     */
    public function getDifferenceTime($storeTime = 'storeTime')
    {
        if ($storeTime == 'storeTime') {
            $storeTime = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        }
        $dbTime = Mage::getSingleton('core/date')->gmtDate();

        if (strtotime($storeTime) >= strtotime($dbTime)) {
            $differenceTime = strtotime($storeTime) - strtotime($dbTime);

            return '\'+' . date('H:i', $differenceTime) . '\'';
        } else {
            $differenceTime = strtotime($dbTime) - strtotime($storeTime);

            return '\'-' . date('H:i', $differenceTime) . '\'';
        }
    }


    /**
     * Get the date in store timezone
     *
     * @param $createdAt
     *
     * @return DateTime
     */
    public function getTimeCreatedAt($createdAt)
    {
        $datetime = new DateTime($createdAt);

        return $datetime->setTimezone(
            $this->getStoreTimezone()
        );
    }

}
