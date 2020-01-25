<?php

class Cminds_Marketplaceproductquestions_Block_Adminhtml_Product_Questions_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array(
                    'id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
        ));
        $form->setUseContainer(true);

        $id = Mage::registry('question_edit_id');
        $productId = Mage::getModel('marketplaceproductquestions/questions')->load($id)->getData('product_id');

        $model1 = Mage::getModel('marketplaceproductquestions/questions')->load($id);
        $model2 = Mage::getModel('catalog/product')->load($productId);
        $model3 = Mage::getModel('marketplaceproductquestions/answers')->load($id, 'question_id');

        $data = $model1->getData() + $model2->getData() + $model3->getData();

        $this->setForm($form);

        $fieldset = $form->addFieldset('base_fieldset', array(
                'legend' => Mage::helper('marketplaceproductquestions')->__('Answer')
        ));

        $fieldset->addField('name', 'text', array(
            'class'     => 'required-entry',
            'name'      => 'name',
            'label'     => Mage::helper('marketplaceproductquestions')->__('Product name'),
            'readonly'  => true
        ));

        $fieldset->addField('author_name', 'text', array(
            'class'     => 'required-entry',
            'name'      => 'author_name',
            'label'     => Mage::helper('marketplaceproductquestions')->__('Author Name'),
            'readonly'  => true
        ));

        $fieldset->addField('visibility', 'select', array(
            'class'     => 'required-entry',
            'label'     => Mage::helper('marketplaceproductquestions')->__('Is visible'),
            'title'     => Mage::helper('marketplaceproductquestions')->__('Is visible'),
            'name'      => 'visibility',
            'options'   => array(
                0 => Mage::helper('adminhtml')->__('No'),
                1 => Mage::helper('adminhtml')->__('Yes'),
            ),
        ));

        $fieldset->addField('question_body', 'textarea', array(
            'class'     => 'required-entry',
            'name'      => 'question_body',
            'label'     => Mage::helper('marketplaceproductquestions')->__('Question'),
        ));

        $fieldset->addField('answer_body', 'textarea', array(
            'required'  => false,
            'name'      => 'answer_body',
            'label'     => Mage::helper('marketplaceproductquestions')->__('Answer'),
        ));

        $fieldset->addField('notify_customer', 'checkbox', array(
            'label'     => Mage::helper('marketplaceproductquestions')->__('Notify Customer'),
            'onclick'   => 'this.value = this.checked ? 1 : 0;',
            'name'      => 'notify_customer',
        ));

        $form->setValues($data);

        return parent::_prepareForm();
    }
}