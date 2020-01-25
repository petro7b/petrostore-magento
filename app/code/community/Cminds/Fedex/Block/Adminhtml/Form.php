<?php

class Cminds_Fedex_Block_Adminhtml_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $vendor = Mage::registry('vendor_data');
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('vendor_form', array('legend' => 'Vendor Registration'));
        $fieldset->addField(
            'name',
            'text',
            array(
             'name' => 'name',
             'label' => 'test',
             'required' => true,
        ));
        $this->setForm($form);
        $form->setValues($vendor->getData());

        return parent::_prepareForm();
    }
}
