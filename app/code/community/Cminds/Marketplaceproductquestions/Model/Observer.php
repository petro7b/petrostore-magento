<?php
class Cminds_Marketplaceproductquestions_Model_Observer extends Mage_Core_Model_Abstract
{
    public function navLoad($observer) {
        $event = $observer->getEvent();
        $items = $event->getItems();
        if(Mage::helper('marketplaceproductquestions')->isEnabled()) {
            $items['Q&A'] =  [
                'label'     => 'Q&A',
                'url'   	=> 'supplier/questions/list',
                'parent'    => null,
                'action_names' => [
                    'cminds_supplierfrontendproductuploader_questions_list',
                    'cminds_supplierfrontendproductuploader_questions_answer',
                ],
                'sort'     => 4.6
            ];
        }
        $observer->getEvent()->setItems($items);
    }
}