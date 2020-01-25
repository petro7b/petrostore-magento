<?php

class Cminds_Marketplacepaypal_Block_Adminhtml_Billing_Pay_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        if (Mage::registry('payment_data')){
                $data = Mage::registry('payment_data')->getData();
        } else {
            $data = array();
        }

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/processPayment', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ));

        $form->setUseContainer(true);

        $this->setForm($form);

        $fieldset = $form->addFieldset('payment_form', array(
            'legend' =>Mage::helper('marketplace')->__('Payment Info')
        ));

        $fieldset->addField('id', 'hidden', array(
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'id',
        ));

        $fieldset->addField('supplier_id', 'hidden', array(
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'supplier_id',
        ));

        $fieldset->addField('order_id', 'hidden', array(
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'order_id',
        ));

        $fieldset->addField('link', 'link', array(
            'label'     => Mage::helper('marketplace')->__('Order Details'),
            'after_element_html' => Mage::helper('marketplace')->__('<a href="%s">#%s</a>', Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view", array('order_id' => $data['order_id'])), Mage::registry('order_data')->getIncrementId())
        ));

        $fieldset->addField('paid', 'link', array(
            'label'  => Mage::helper('marketplace')->__('Paid'),
            'after_element_html' => isset($data['amount']) ? Mage::helper('core')->currency($data['amount'], true, false) : Mage::helper('core')->currency('0.0', true, false)
        ));

        $fieldset->addField('to_pay', 'link', array(
            'label'  => Mage::helper('marketplace')->__('Missing'),
            'after_element_html' => isset($data['amount']) ? Mage::helper('core')->currency($this->orderAmount($data) - $data['amount'], true, false) : Mage::helper('core')->currency('0.0', true, false)
        ));

        $fieldset->addField('amount', 'text', array(
            'label'     => Mage::helper('marketplace')->__('Amount'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'amount',
        ));

        $form->setValues($data);

        return parent::_prepareForm();
    }

    private function orderAmount($data) {
        $eavAttribute       = new Mage_Eav_Model_Mysql4_Entity_Attribute();
        $code               = $eavAttribute->getIdByCode('catalog_product', 'creator_id');
        $tableName          = Mage::getSingleton("core/resource")->getTableName("catalog_product_entity_int");
        $orderTable         = Mage::getSingleton('core/resource')->getTableName('sales/order');
        $supplierPayment    = Mage::getSingleton('core/resource')->getTableName('marketplace/payments');
        $collection         = Mage::getModel('sales/order_item')->getCollection();

        $collection->addExpressionFieldToSelect('vendor_amount', 'SUM(price-(price*(vendor_fee/100)))', 'vendor_fee');

        $collection->getSelect()
            ->joinInner(array('o' => $orderTable), 'o.entity_id = main_table.order_id', array('status', 'state', 'subtotal', 'increment_id'))
            ->joinInner(array('e' => $tableName), 'e.entity_id = main_table.product_id AND e.attribute_id = ' . $code, array('value as supplier_id') )
            ->joinLeft(array('p' => $supplierPayment), 'p.order_id = main_table.order_id AND p.supplier_id = supplier_id', array('amount AS payment_amount', 'payment_date', 'id AS payment_id'))
            ->where('main_table.parent_item_id is null')
            ->where('e.value IS NOT NULL')
            ->where('o.state != "canceled"')
            ->where('supplier_id = ' . $data['supplier_id'])
            ->where('o.entity_id = ' . $data['order_id']);

        $collection->getSelect()->group('o.entity_id', 'e.value');

        return $collection->getFirstItem()->getData('vendor_amount');
    }
}