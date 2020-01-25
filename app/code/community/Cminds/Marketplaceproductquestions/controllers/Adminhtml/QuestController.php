<?php

class Cminds_Marketplaceproductquestions_Adminhtml_QuestController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Questions'));
        $this->loadLayout();
        $this->_setActiveMenu('catalog/cminds_questions');
        $this->_addContent($this->getLayout()->createBlock('marketplaceproductquestions/adminhtml_product_questions'));
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->_title($this->__('Questions'));
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('marketplaceproductquestions/adminhtml_product_questions_grid')->toHtml()
        );
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        Mage::register('question_edit_id', $id);
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('marketplaceproductquestions/adminhtml_product_edit'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($postData = $this->getRequest()->getPost()) {
            $questions = Mage::getModel('marketplaceproductquestions/questions');
            $answers = Mage::getModel('marketplaceproductquestions/answers');

            if ($id = $this->getRequest()->getParam('id', false)) {

                $questions->load($id);
                $answers->load($id, 'question_id');

                $answerBody = $this->getRequest()->getPost('answer_body');
                $customerId = $questions->getData('customer_id');
                $authorName = $questions->getData('author_name');
                $adminId = Mage::getSingleton('admin/session')->getUser()->getUserId();
                $postData['updated_at'] = date('Y-m-d H:i:s');
                $questions->addData($postData);
                $answers->addData(array(
                    'answer_body' => $answerBody,
                    'question_id' => $this->getRequest()->getParam('id'),
                    'customer_id' => $customerId,
                    'author_name' => $authorName,
                    'admin_id' => $adminId,
                    'updated_at' => date('Y-m-d H:i:s')
                ));
                if($answers->isObjectNew()) {
                    $answers->setCreatedAt(date('Y-m-d H:i:s'));
                }

                try {
                    $questions->save();
                    $answers->save();

                    if ($answers->getData('answer_body') != ""
                        && isset($postData['notify_customer'])
                        && $postData['notify_customer']
                    ) {
                        $customerData = Mage::getModel('customer/customer')->load($customerId);
                        $productId = $questions->getData('product_id');
                        $questionBody = $questions->getData('question_body');
                        Mage::helper('marketplaceproductquestions/email')
                            ->notifyCustomerOnAnswer(
                                $customerData, $productId, $questionBody, $answers->getData('answer_body')
                            );
                        $answers->addData(array(
                            'is_customer_notified' => '1'
                        ));
                        $answers->save();
                    }
                    Mage::getSingleton('adminhtml/session')->addSuccess('Saved');
                    $this->_redirect('*/*/');
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError('Not Saved. Error:' . $e->getMessage());
                    Mage::getSingleton('adminhtml/session')->setExampleFormData($postData);
                    $this->_redirect('*/*/edit', array('id' => $questions->getId(), '_current' => true));
                }
            }
        }
    }

    public function massDeleteAction()
    {
        $questionIds = $this->getRequest()->getParam('id');
        if(!is_array($questionIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('marketplaceproductquestions')->__('Please select questions'));
        } else {
            try {
                $questionModel = Mage::getModel('marketplaceproductquestions/questions');
                foreach ($questionIds as $questionId) {
                    $questionModel->load($questionId)->delete();

                    $answerModel = Mage::getModel('marketplaceproductquestions/answers')
                        ->load($questionId, 'question_id');
                    $answerIds = $answerModel->getData('id');
                    $answerModel->load($answerIds)->delete();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('marketplaceproductquestions')->__('Total of %d record(s) were deleted.', count($questionIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massApproveAction()
    {
        $questionIds = $this->getRequest()->getParam('id');
        if(!is_array($questionIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('marketplaceproductquestions')->__('Please select questions'));
        } else {
            try {
                $questionModel = Mage::getModel('productquestions/questions');
                foreach ($questionIds as $questionId) {
                    $questionModel->load($questionId)->addData(array(
                        'visibility' => 1
                    ));
                    $questionModel->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('marketplaceproductquestions')->__('Total of %d record(s) were approved.', count($questionIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massDissapproveAction()
    {
        $questionIds = $this->getRequest()->getParam('id');
        if(!is_array($questionIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('marketplaceproductquestions')->__('Please select questions'));
        } else {
            try {
                $questionModel = Mage::getModel('marketplaceproductquestions/questions');
                foreach ($questionIds as $questionId) {
                    $questionModel->load($questionId)->addData(array(
                        'visibility' => 0
                    ));
                    $questionModel->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('marketplaceproductquestions')->__('Total of %d record(s) were dissapproved.', count($questionIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function approveAction() {
        $id = $this->_request->getParam('id');

        $answer = Mage::getModel('marketplaceproductquestions/answers')->load($id, 'question_id');
        $answer->setAdminApproval(1);
        $answer->save();
        Mage::getSingleton('core/session')->addSuccess('Question has been approved.');

        Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("*/*/index"));
    }

    public function disapproveAction() {
        $id = $this->_request->getParam('id');

        $answer = Mage::getModel('marketplaceproductquestions/answers')->load($id, 'question_id');
        $answer->setAdminApproval(0);
        $answer->save();
        Mage::getSingleton('core/session')->addSuccess('Question has been disapproved.');

        Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("*/*/index"));
    }

}