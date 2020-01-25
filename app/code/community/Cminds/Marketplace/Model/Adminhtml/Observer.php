<?php
class Cminds_Marketplace_Model_Adminhtml_Observer {
    private $_transaction;
    public function onCustomerSave($observer) {
        $request = $observer->getRequest();
        $customer = $observer->getCustomer();
        $postData = $request->getPost();

        if(!Mage::helper('supplierfrontendproductuploader')->isSupplier($customer)) {
            return false;
        }

        try {
            if(isset($postData['categories_all']) && is_array($postData['categories_all'])) {
                $supplierId = $customer->getId();
                foreach($postData['categories_all'] as $categoryId) {
                    Mage::getModel('marketplace/categories')
                        ->getCollection()
                        ->addFilter('supplier_id', $supplierId)
                        ->addFilter('category_id', $categoryId)
                        ->getFirstItem()
                        ->delete();

                    if(isset($postData['categories_ids']) && is_array($postData['categories_ids']) && !in_array($categoryId, $postData['categories_ids']))
                     {
                        Mage::getModel('marketplace/categories')
                            ->setData('supplier_id', $supplierId)
                            ->setData('category_id', $categoryId)
                            ->save();
                    }
                }
            }

            $this->_transaction = Mage::getModel('core/resource_transaction');

            $methods = $this->_setShippingMethods($postData, $customer);

            foreach($methods AS $method) {
                $this->_transaction->addObject($method);
            }

            $customer = $this->_handleProfileUpdate($postData, $customer);
            $this->_transaction->addObject($customer);

            if(isset($postData['generate_new_url'])) {
                $customerPath = 'marketplace/supplier/view/id/' . $customer->getId();
                $customerPathId = 'marketplace_vendor_url_' . $customer->getId();

                $urls = Mage::getModel('core/url_rewrite')
                    ->getCollection()
                    ->addFieldtoFilter('id_path', $customerPathId);

                foreach($urls AS $url) {
                    $url->delete();
                }

                $urlPath = $this->createRewritedName( $customer );
                $website = Mage::getModel('core/website')->load($customer->getWebsiteId());

                foreach ($website->getStores() as $store) {
                    $url = Mage::getModel('core/url_rewrite')
                        ->setStoreId($store->getId())
                        ->setIsSystem(0)
                        ->setIdPath($customerPathId)
                        ->setTargetPath($customerPath)
                        ->setRequestPath($urlPath);
                    $url->save();
                }
            }

            $path = Mage::getBaseDir('media') . DS . 'supplier_logos' . DS;

            if(isset($postData['remove_logo'])) {
                $s = $customer->getSupplierLogo();

                if(file_exists($path . $s)) {
                    unlink($path . $s);
                }

                $customer->setSupplierLogo(null);
            }

            if(isset($_FILES['logo']['name']) and (file_exists($_FILES['logo']['tmp_name']))) {
                $uploader = new Varien_File_Uploader('logo');
                $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);

                $nameSplit = explode('.', $_FILES['logo']['name']);
                $ext = $nameSplit[count($nameSplit)-1];
                $newName = md5($_FILES['logo']['name'] . time()) . '.' . $ext;
                $customer->setSupplierLogo($newName);
                $uploader->save($path, $newName);
//                    $changed = true;
            }
            
            
            // Start custom image background
            
            $paths = Mage::getBaseDir('media') . DS . 'supplier_cover' . DS;

            if(isset($postData['remove_cover'])) {
                $s = $customer->getSupplierBackground();

                if(file_exists($paths . $s)) {
                    unlink($paths . $s);
                }

                $customer->setSupplierBackground(null);
            }

            if(isset($_FILES['logo']['name']) and (file_exists($_FILES['logo']['tmp_name']))) {
                $uploader = new Varien_File_Uploader('logo');
                $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);

                $nameSplit = explode('.', $_FILES['logo']['name']);
                $ext = $nameSplit[count($nameSplit)-1];
                $newName = md5($_FILES['logo']['name'] . time()) . '.' . $ext;
                $customer->setSupplierLogo($newName);
                $uploader->save($paths, $newName);
//                    $changed = true;
            }
            // End Custom

            $this->_transaction->save();

            foreach($methods AS $i => $method) {
                if(count($postData['shipping_method_id']) > count($methods)) {
                    $i = $i +1;
                }
                $this->_parseUploadedCsv($i, $method);
            }
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getBaseUrl() . 'marketplace/settings/shipping/');
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getBaseUrl() . 'marketplace/settings/shipping/');
            Mage::log($e->getMessage());
        }
    }

    private function _handleProfileUpdate($postData, $customer) {
        if(isset($postData['action']) && $postData['action'] == 'save_remark') {
            $customer->setData('supplier_remark', $postData['remark']);
            $customer->setData('rejected_notfication_seen', 0);
        }

        if(isset($postData['action']) && $postData['action'] == 'approve') {
            $customer->setData('supplier_profile_approved', 1);
            $customer->setData('supplier_profile_visible', 1);
            Mage::helper('marketplace/email')->notifySupplier($customer);
        }

        if(isset($postData['action']) && $postData['action'] == 'disapprove') {
            $customer->setData('supplier_profile_approved', 0);
            $customer->setData('rejected_notfication_seen', 1);
        }

        if(isset($postData['action']) && $postData['action'] == 'approve_changes') {
            $newCustomFieldsValues =  $this->_prepareCustomFieldsValues($postData);

            $customer->setData('supplier_remark', NULL);

            $customer->setData('supplier_name_new', '');
            $customer->setData('supplier_description_new', '');
            $customer->setCustomFieldsValues(serialize($newCustomFieldsValues));
            $customer->setNewCustomFieldsValues(NULL);

            $customer->setData('supplier_name', htmlentities($postData['supplier_name_new'], ENT_QUOTES, "UTF-8"));
            $customer->setData('supplier_description', $postData['supplier_description_new']);
            $customer->setData('supplier_profile_visible', 1);
            $customer->setData('supplier_profile_approved', 1);
            $customer->setData('rejected_notfication_seen', 1);
            Mage::helper('marketplace/email')->notifySupplier($customer);
        }
        return $customer;
    }

    private function _parseUploadedCsv($i, $method) {
        $parsedData = array();
        if (isset($_FILES["table_rate_file"])) {
            if(file_exists($_FILES["table_rate_file"]["tmp_name"][$i])) {
                if (($handle = fopen($_FILES["table_rate_file"]["tmp_name"][$i],
                        "r")) !== FALSE) {
                    while (($row = fgetcsv($handle)) !== FALSE) {
                        $parsedData[] = $row;
                    }
                    fclose($handle);
                } else {
                    throw new ErrorException('Cannot handle uploaded CSV');
                }
            }
            if(count($parsedData) < 1) return;
            if($parsedData[0][0] == 'Country') {
                unset($parsedData[0]);
            }

            $supplierRate = Mage::getModel("marketplace/rates")
                ->getCollection()
                ->addFilter('supplier_id', $method->getSupplierId())
                ->addFilter('method_id', $method->getId())
                ->getFirstItem();

            if(!$supplierRate->getId()) {
                $supplierRate->setSupplierId($method->getSupplierId());
                $supplierRate->setMethodId($method->getId());
            }

            $supplierRate->setRateData(serialize($parsedData));
            $supplierRate->save();
        }
    }

    private function _prepareCustomFieldsValues($postData) {
        $customFieldsCollection = Mage::getModel('marketplace/fields')->getCollection();

        $customFieldsValues = array();

        foreach($customFieldsCollection AS $field) {
            if(isset($postData[$field->getName().'_new'])) {
                if($field->getIsRequired() && $postData[$field->getName().'_new'] == '') {
                    throw new Exception("Field ".$field->getName()." is required");
                }

                if($field->getType() == 'date' && !strtotime($postData[$field->getName().'_new'])) {
                    throw new Exception("Field ".$field->getName()." is not valid date");
                }

                $customFieldsValues[] = array('name' => $field->getName(), 'value' => $postData[$field->getName().'_new']);
            }
        }
        return $customFieldsValues;

    }

    private function createRewritedName($customer) {
        $supplier_name = Mage::helper('supplierfrontendproductuploader')->getSupplierName($customer);
        $rewritedName = 'vendor/' . Mage::helper('supplierfrontendproductuploader')->clearString($supplier_name) . '.html';
        $baseString = Mage::getModel('core/url_rewrite')->load($rewritedName, 'request_path');
        if(!$baseString->getId()) {
            return $rewritedName;
        }
        $i = 1;

        while(true) {
            $rewritedName = 'vendor/' . Mage::helper('supplierfrontendproductuploader')->clearString($supplier_name) . '-' . $i . '.html';

            $model = Mage::getModel('core/url_rewrite')->load($rewritedName, 'request_path');

            if(!$model->getId()) {
                return $rewritedName;
            }
            $i++;
        }
    }

    protected function _setShippingMethods($postData, $customer) {
        if(!isset($postData['shipping_method_id'])) $postData['shipping_method_id'] = array();

        if(isset($postData['removedItems'])) {
            $removedItems = explode(',', $postData['removedItems']);

            foreach($removedItems AS $item) {
                if(!$item) continue;
                $shipping = Mage::getModel('marketplace/methods')->load($item);

                if($shipping->getId()) {

                    $supplierRate = Mage::getModel("marketplace/rates")
                                    ->getCollection()
                                    ->addFilter('supplier_id', $shipping->getSupplierId())
                                    ->addFilter('method_id', $shipping->getId())
                                    ->getFirstItem();

                    $shipping->delete();
                    $supplierRate->delete();
                }
            }
        }

        $shippingArray = array();
        foreach($postData['shipping_method_id'] AS $i => $k) {
            $shipping = Mage::getModel( 'marketplace/methods' )->load( $k );

            $shippingMethod = array();
            if ( $postData['flat_rate_enabled'][ $i ] ) {
                $shippingMethod[] = array( $postData['shipping_method_id'][ $i ] => 'flat_rate' );
            }

            if ( $postData['table_rate_enabled'][ $i ] ) {
                $shippingMethod[] = array( $postData['shipping_method_id'][ $i ] => 'table_rate' );
            }

            if ( $postData['free_shipping_enabled'][ $i ] ) {
                $shippingMethod[] = array( $postData['shipping_method_id'][ $i ] => 'free_shipping' );
            }

            if ( empty( $postData['shipping_name'][ $i ] ) ) {
                continue;
            }

            if ( count( $shippingMethod ) != 1 ) {
                throw new Exception( "Each method should have ONE enabled method. \"" . $postData['shipping_name'][ $i ] . "\" method didn't save." );
                continue;
            }

            $shipping->setSupplierId( $customer->getId() );
            $shipping->setName( $postData['shipping_name'][ $i ] );
            $shipping->setFlatRateFee( 0 );
            $shipping->setFlatRateAvailable( 0 );
            $shipping->setTableRateAvailable( 0 );
            $shipping->setTableRateCondition( 0 );
            $shipping->setTableRateFee( 0 );
            $shipping->setFreeShipping( 0 );

            if ( isset( $shippingMethod[0][ $k ] ) ) {
                $shippingMethod = $shippingMethod[0][ $k ];
            } else {
                $shippingMethod = false;
            }

            if ( $shippingMethod && $shippingMethod == "flat_rate" ) {
                $shipping->setFlatRateAvailable( 1 );
                $shipping->setFlatRateFee( $postData['flat_rate_fee'][ $i ] );
            } else {
                $shipping->setFlatRateFee( 0 );
                $shipping->setFlatRateAvailable( 0 );
            }
            if ( $shippingMethod && $shippingMethod == "table_rate" ) {
                $shipping->setTableRateAvailable( 1 );
                $shipping->setTableRateFee( $postData['table_rate_fee'][ $i ] );

                if ( isset( $postData['table_rate_condition'][ $i ] ) ) {
                    $shipping->setTableRateCondition( $postData['table_rate_condition'][ $i ] );
                } else {
                    $shipping->setTableRateCondition( 1 );
                }
            } else {
                $shipping->setTableRateFee( 0 );
                $shipping->setTableRateAvailable( 0 );
            }
            if ( $shippingMethod && $shippingMethod == "free_shipping" ) {
                $shipping->setFreeShipping( 1 );
            } else {
                $shipping->setFreeShipping( 0 );
            }

            $shippingArray[] = $shipping;
        }

        return $shippingArray;
    }

    public function toHtmlBefore($observer)
    {
        $grid = $observer->getBlock();

        /**
         * Cminds_Supplierfrontendproductuploader_Block_Adminhtml_Supplier_List
         */
        if ($grid instanceof Cminds_Supplierfrontendproductuploader_Block_Adminhtml_Supplier_List_Grid) {
            $grid->addColumnAfter(
                'waiting_for_approval',
                array(
                    'header' => Mage::helper('customer')->__('Profile waiting for approval'),
                    'index'  => 'rejected_notfication_seen',
                    'type'      => 'number',
                    'renderer'  => 'Cminds_Marketplace_Block_Adminhtml_Supplier_List_Renderer_Waiting'
                ),
                'entity_id'
            );
        }
    }
}