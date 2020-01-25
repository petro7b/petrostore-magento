<?php
class Cminds_Marketplacerma_Block_Supplier_Rma_List extends Cminds_Marketplacerma_Block_Supplier_Rma_Abstract {
    public function _construct() {
        $this->setTemplate('marketplacerma/supplier/rma/list.phtml');
    }

    public function getEntries() {
		$collection = Mage::getModel('cminds_rma/rma')
			->getCollection();

		$collection->addFieldToFilter('status_id', array('neq' => Cminds_Rma_Model_Rma::DEFAULT_CANCELED_ID));

		if(Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customer = Mage::getSingleton('customer/session')->getCustomer();
		}

		return $collection;
    }
	
	public function isSupplierItemInOrder($order){
		$orderItems = $order->getAllItems();
		$currentSupplier = Mage::getSingleton('customer/session');
		
		if($currentSupplier->isLoggedIn() && $currentSupplier->getId()){
			foreach($orderItems as $item){
				$product = Mage::getModel('catalog/product')->load($item->getProductId());
				if($product->getCreatorId() && $product->getCreatorId() == $currentSupplier->getId()){
					return true;
				}
			}
		}
		return false;
	}
}