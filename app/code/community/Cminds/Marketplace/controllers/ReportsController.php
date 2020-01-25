<?php

class Cminds_Marketplace_ReportsController extends Cminds_Marketplace_Controller_Action {
    public function preDispatch() {
        parent::preDispatch();

        $hasAccess = $this->_getHelper()->hasAccess();

        if(!$hasAccess) {
            $this->getResponse()->setRedirect($this->_getHelper('supplierfrontendproductuploader')->getSupplierLoginPage());
        }
    }
    public function ordersAction() {
        $postData = $this->getRequest()->getPost();
        if(isset($postData['submit']) && $postData['submit'] == 'Export to CSV' ) {
            $this->_redirect('*/*/exportOrders');
        }
        $this->_renderBlocks(false, true);
    }

    public function mostViewedAction() {
        $postData = $this->getRequest()->getPost();
        if(isset($postData['submit']) && $postData['submit'] == 'Export to CSV' ) {
            $this->_redirect('*/*/exportMostViewed');
        }
        $this->_renderBlocks(false, true);
    }

    public function bestsellersAction() {
        $postData = $this->getRequest()->getPost();
        if(isset($postData['submit']) && $postData['submit'] == 'Export to CSV' ) {
            $this->_redirect('*/*/exportBestsellers');
        }

        $this->_renderBlocks(false, true);
    }

    public function exportBestsellersAction() {
        $fileName   = 'bestsellers-' . gmdate('YmdHis') . '.csv';
        $grid       = $this->getLayout()->createBlock('marketplace/report_bestsellers');

        $this->_prepareDownloadResponse($fileName, $grid->getCsvFileEnhanced());
    }

    public function exportOrdersAction() {
        $fileName   = 'orders-' . gmdate('YmdHis') . '.csv';
        $grid       = $this->getLayout()->createBlock('marketplace/report_order');

        $this->_prepareDownloadResponse($fileName, $grid->getCsvFileEnhanced());
    }

    public function exportMostViewedAction() {
        $fileName   = 'mostviewed-' . gmdate('YmdHis') . '.csv';
        $grid       = $this->getLayout()->createBlock('marketplace/report_mostviewed');

        $this->_prepareDownloadResponse($fileName, $grid->getCsvFileEnhanced());
    }

    public function exportLowStockAction() {
        $fileName   = 'lowstock-' . gmdate('YmdHis') . '.csv';
        $grid       = $this->getLayout()->createBlock('marketplace/report_lowstock');

        $this->_prepareDownloadResponse($fileName, $grid->getCsvFileEnhanced());
    }

    public function lowStockAction() {
        $postData = $this->getRequest()->getPost();
        if(isset($postData['submit']) && $postData['submit'] == 'Export to CSV' ) {
            $this->_redirect('*/*/exportLowStock');
        }
        $this->_renderBlocks(false, true);
    }
}
