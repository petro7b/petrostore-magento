<?php

class Cminds_Marketplacerma_Rma_SupplierController extends Cminds_Marketplace_Controller_Action {
    public function preDispatch() {
        parent::preDispatch();

        $hasAccess = $this->_getHelper()->hasAccess();

        if(!$hasAccess) {
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::helper('customer')->getLoginUrl());
        }
    }

    public function listAction() {
        $this->_renderBlocks();
    }

    public function viewAction() {
        $rmaId = $this->getRequest()->getParam('id', null);

        Mage::register('marketplace_rma', $rmaId);

        $this->_renderBlocks();
    }

    public function saveCommentAction() {
        $postData = $this->getRequest()->getPost();

        if(!isset($postData['rma_id'])) $this->_forceError("No RMA Selected", Mage::getUrl("*/*/list"));
        $transaction = Mage::getModel('core/resource_transaction');

        $rma = Mage::getModel('cminds_rma/rma')->load($postData['rma_id']);
        try {
            $data['old_status_id'] = $rma->getStatusId();

            $rma->setStatusId($postData['status_id']);
            $transaction->addObject($rma);
            $commentModel = Mage::getModel('cminds_rma/rma_comment')->setData($postData);

            $transaction->addObject($commentModel);

            $transaction->save();
            $this->getResponse()->setRedirect(Mage::getUrl("*/*/view", array('id' => $postData['rma_id'])));
        } catch(Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->getResponse()->setRedirect(Mage::getUrl("*/*/view", array('id' => $postData['rma_id'])));
        }
    }

    private function _forceError($msg, $url) {
        Mage::getSingleton('core/session')->addError($this->__($msg));
        $this->getResponse()->setRedirect($url);
//      exit;
    }

    public function createCreditMemoAction() {
        try {
            if (!$id = $this->getRequest()->getParam('id', null)) {
                throw new Exception("RMA with this ID");
            }

            $rma = Mage::getModel('cminds_rma/rma')->load($id);
            $order = $rma->getOrder();

            $rmaItems = $rma->getAllItems();

            $creditmemoData = array(
                'qtys' => array(),
                'shipping_amount' => null,
                'adjustment_positive' => '0',
                'adjustment_negative' => null
            );

            foreach ($rmaItems AS $item) {
                $creditmemoData['qtys'][$item->getItemId()] = $item->getQty();
            }

            $comment = 'Comment for Credit Memo';

            $notifyCustomer = true;
            $includeComment = false;
            $refundToStoreCreditAmount = '1';

            $order->setForcedCanCreditmemo(1);

            if ($order->getId() && $order->canCreditmemo()) {
                $service = Mage::getModel('sales/service_order', $order);

                $creditmemo = $service->prepareCreditmemo($creditmemoData);
                $refundToStoreCreditAmount = max(
                    0,
                    min($creditmemo->getBaseCustomerBalanceReturnMax(), $refundToStoreCreditAmount)
                );
                if ($refundToStoreCreditAmount) {
                    $refundToStoreCreditAmount = $creditmemo->getStore()->roundPrice($refundToStoreCreditAmount);
                    $creditmemo->setBaseCustomerBalanceTotalRefunded($refundToStoreCreditAmount);
                    $refundToStoreCreditAmount = $creditmemo->getStore()->roundPrice(
                        $refundToStoreCreditAmount * $order->getStoreToOrderRate()
                    );
                    $creditmemo->setBsCustomerBalTotalRefunded($refundToStoreCreditAmount);
                    $creditmemo->setCustomerBalanceRefundFlag(true);
                }
                $creditmemo->setPaymentRefundDisallowed(true)->register();

                if (!empty($comment)) {
                    $creditmemo->addComment($comment, $notifyCustomer);
                }

                Mage::getModel('core/resource_transaction')
                    ->addObject($creditmemo)
                    ->addObject($order)
                    ->save();
                $creditmemo->sendEmail($notifyCustomer, ($includeComment ? $comment : ''));
                $rma->close();
                Mage::getSingleton('core/session')->addSuccess(Mage::helper('cminds_rma')->__('Credit Memo has been created'));
            } else {
                Mage::throwException($this->__("Credit Memo cannot be created"));
            }

            return $this->_redirect(
                'marketplace/rma_supplier/view',
                array("id" => $id)
            );
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('core/session')->addError($e->getMessage());

            return $this->_redirect(
                'marketplace/rma_supplier/view',
                array("id" => $id)
            );
        }
    }

}
