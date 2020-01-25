<?php
class Cminds_Marketplacerma_Model_Observer extends Mage_Core_Model_Abstract
{
    public function addRmaViewSupplierColumn($event) {
        $grid = $event->getGrid();
        $grid->addColumnAfter('vendor_name', array(
            'header'    => Mage::helper('marketplacerma')->__('Supplier'),
            'index'     => 'vendor_name',
            'renderer'  =>  'Cminds_Marketplacerma_Block_Adminhtml_Grid_Renderer_Vendor_Name'
        ), 'item_id');
    }

    public function navLoad($observer) {
        $event = $observer->getEvent();
        $items = $event->getItems();
            $items['RMA'] =  array(
                'label'     => 'RMA',
                'url'     => 'marketplace/rma_supplier/list/',
                'parent'    => null,
                'action_names' => [
                    'cminds_marketplace_rma_supplier_list',
                ],
                'sort'     => 4
            );
        $observer->getEvent()->setItems($items);
    }

    public function cmindsRmaSaveAfter($observer) {
        $rma = $observer->getDataObject();
	if(!$rma->isObjectNew()) return false;
        $items = array();

        foreach($rma->getAllItems() AS $item) {
            $product = $item->getProduct();

            if($product->getCreatorId()) {
                $items[$product->getCreatorId()][] = $item->getProduct();
            }
        }

        foreach($items AS $vendor_id => $products) {
            $vendor = Mage::getModel("customer/customer")->load($vendor_id);
            $this->notifyVendor($vendor, $products, $rma);
        }
    }

    protected function notifyVendor($vendor, $products, $rma) {
        try {
            $emailTemplate  = Mage::getModel('core/email_template')
                                  ->loadDefault('rma_vendor_new');

            $emailTemplateVariables = array();
            $emailTemplateVariables['rma'] = $rma;
            $emailTemplateVariables['order'] = $rma->getOrder();


            $emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name'));
            $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));

            $emailTemplate->getProcessedTemplate($emailTemplateVariables);
            $emailTemplate->send(
                $vendor->getEmail(),
                $vendor->getName(),
                $emailTemplateVariables
            );
        }
        catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
    }
}