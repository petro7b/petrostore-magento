<?php

class Cminds_Marketplaceproductquestions_QuestionsController extends Cminds_Supplierfrontendproductuploader_Controller_Action
{
    public function preDispatch()
    {
        parent::preDispatch();
        $hasAccess = $this->_getHelper()->hasAccess();

        if (!$hasAccess) {
            $this->getResponse()->setRedirect(
                $this->_getHelper('supplierfrontendproductuploader')->getSupplierLoginPage()
            );
        }
    }

    public function listAction()
    {
        $this->_renderBlocks();
    }

    public function answerAction()
    {
        $id = $this->_request->getParam('id', null);
        $p = Mage::getModel('marketplaceproductquestions/questions')->load($id);

        if ($id == null || ($p->getData('supplier_id') != Mage::helper('supplierfrontendproductuploader')->getSupplierId())) {
            $this->getResponse()->setRedirect(Mage::getUrl('supplier/questions/list'));
            Mage::getSingleton('core/session')->addError($this->__("There is no question with this Id"));
            return;
        }
        Mage::register('question_id', $id);

        $this->_renderBlocks();
    }

    public function saveAction()
    {
        if ($this->_request->isPost()) {
            $postData = $this->_request->getPost();

            if ($id = $postData['question_id']) {
                $question = Mage::getModel('marketplaceproductquestions/questions')->load($id);
                $answer = Mage::getModel('marketplaceproductquestions/answers')->load($id, 'question_id');

                $question->setQuestionBody($postData['question_body']);
                $question->setAuthorName($postData['author_name']);
                $question->setVisibility($postData['author_name']);
                $question->setUpdatedAt(date('Y-m-d H:i:s'));
                if (isset($postData['visibility'])) {
                    $question->setVisibility(1);
                } else {
                    $question->setVisibility(0);
                }
                $answer->setQuestionId($id);
                $answer->setCustomerId($question->getCustomerId());
                $answer->setAuthorName($postData['author_name']);
                $answer->setAnswerBody($postData['answer_body']);
                if ($answer->isObjectNew()) {
                    $answer->setCreatedAt(date('Y-m-d H:i:s'));
                }
                $answer->setUpdatedAt(date('Y-m-d H:i:s'));

                try {
                    $question->save();
                    $answer->save();
                    if (!$answer->getData('answer_body') == ""
                        && isset($postData['notify_customer'])
                        && $postData['notify_customer']
                    ) {
                        $customerData = Mage::getModel('customer/customer')->load($question->getCustomerId());
                        $productId = $question->getData('product_id');
                        $questionBody = $question->getData('question_body');

                        Mage::helper('marketplaceproductquestions/email')
                            ->notifyCustomerOnAnswer(
                                $customerData,
                                $productId,
                                $questionBody,
                                $answer->getData('answer_body')
                            );

                        $answer->addData(array(
                            'is_customer_notified' => '1'
                        ));
                        $answer->save();
                    }

                    Mage::getSingleton('core/session')->addSuccess('Saved');
                    $this->_redirect('*/*/list');
                } catch (Exception $e) {
                    Mage::getSingleton('core/session')->addError('Not Saved. Error:' . $e->getMessage());
                    $this->_redirect('*/*/list');
                }
            }
        }
    }

    public function addQuestionAction()
    {
        $mailHelper = Mage::helper('marketplaceproductquestions/email');
        if ($this->getRequest()->isPost()) {
            $postData =$this->getRequest()->getPost();
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            if (isset($postData['author_name'])) {
                $authorName = $postData['author_name'];
            } else {
                $authorName = $customer->getName();
            }
            $questionBody = $postData['question_body'];
            $productId = $postData['product_id'];
            $supplierId = Mage::helper('marketplace')->getSupplierIdByProductId($productId);
            $question = Mage::getModel('marketplaceproductquestions/questions');

            $question->setSupplierId($supplierId);
            $question->setQuestionBody($questionBody);
            $question->setAuthorName($authorName);
            $question->setCustomerId($customer->getEntityId());
            $question->setProductId($productId);
            $question->setCreatedAt(date('Y-m-d H:i:s'));

            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                try {
                    $question->save();
                    $mailHelper->notifyCustomerOnQuestion($customer, $productId, $questionBody);
                    $mailHelper->notifyAdminOnQuestion($customer, $productId, $questionBody, $question->getId());
                    if ($supplierId) {
                        $mailHelper->notifySupplierOnQuestion($customer, $productId, $questionBody, $question->getId(), $supplierId);
                    }
                    Mage::getSingleton('core/session')->addSuccess(
                        $this->__('The question was sent successfully, waiting for admin approval')
                    );
                } catch (Exception $e) {
                    Mage::getSingleton('core/session')->addError('Not Send. Error:' . $e->getMessage());
                }
            } else {
                $response = $this->getRequest()->getPost('g-recaptcha-response');
                if (!Mage::helper('marketplaceproductquestions')->isRecaptchaEnabled()
                    || (!empty($response) && $this->_VerifyCaptcha($response))
                ) {
                    try {
                        $question->save();
                        $mailHelper->notifyAdminOnQuestion($authorName, $productId, $questionBody, $question->getId());
                        if ($supplierId) {
                            $mailHelper->notifySupplierOnQuestion(
                                $authorName,
                                $productId,
                                $questionBody,
                                $question->getId(),
                                $supplierId
                            );
                        }
                        Mage::getSingleton('core/session')->addSuccess(
                            $this->__('The question was sent successfully, waiting for admin approval')
                        );
                    } catch (Exception $e) {
                        Mage::getSingleton('core/session')->addError('Not Send. Error:' . $e->getMessage());
                    }
                } else {
                    Mage::getSingleton('core/session')->addError($this->__('Please check the captcha form'));
                }
            }
        }
    }

    public function _VerifyCaptcha($response)
    {
        $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . Mage::helper('marketplaceproductquestions')->getRecaptchaSecretKey() .
            "&response=" . $response;
        $data = file_get_contents($url);
        $res = json_decode($data, TRUE);

        if ($res['success'] == 'true')
            return TRUE;
        else
            return FALSE;
    }

}