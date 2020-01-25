<?php

class Cminds_Marketplace_Block_Adminhtml_Customer_Edit_Tab_Profile_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    public $customFieldsValues;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('marketplace/customer/tab/view/profile.phtml');
        $this->setDestElementId('edit_form');
        $this->setShowGlobalIcon(false);
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'      => 'edit_form',
                'method'  => 'post',
            )
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        $customer = Mage::registry('current_customer');

        $fieldset = $form->addFieldset(
            'customer_profile_data_new',
            array(
            )
        );

        $fieldset->addField('supplier_logo_new', 'image', array(
            'label'     => Mage::helper('marketplace')->__('Supplier Logo'),
            'class'     => 'disable',
            'required'  => false,
            'name'      => 'supplier_logo_new',
            'note'      => 'Allowed file types: JPG, JPEG, GIF, PNG.',
            'value'     => Mage::getModel('marketplace/supplier_logo_renderer_image')->render(),
        ));

        $fieldset->addField(
            'supplier_profile_name_new',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('Name'),
                'name'     => 'supplier_name_new',
                'value'     => $customer->getData('supplier_name_new'),
            )
        );
        $fieldset->addField(
            'generate_new_url',
            'checkbox',
            array(
                'label'    => Mage::helper('marketplace')->__('Generate New Url'),
                'name'     => 'generate_new_url',
            )
        );
        $fieldset->addField(
            'supplier_profile_description_new',
            'textarea',
            array(
                'label'     => Mage::helper('marketplace')->__('Description'),
                'name'      => 'supplier_description_new',
                'value'     => $customer->getData('supplier_description_new'),
                'wysiwyg'   => true,
                'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
            )
        );

        $customFieldsCollection = $this->getCustomFieldsCollection();
        $customFieldsValues = unserialize($customer->getNewCustomFieldsValues());
        foreach ($customFieldsCollection as $customField) {
            $fieldConfig['label'] = Mage::helper('marketplace')->__($customField->getLabel());
            $fieldConfig['name'] = $customField->getName().'_new';
            $fieldConfig['value'] = $this->_findValue($customField->getName(), $customFieldsValues);

            if ($customField->getType() == 'textarea' && $customField->getWysiwyg()) {
                $fieldConfig['wysiwyg'] = true;
                $fieldConfig['config'] = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
            }

            $fieldset->addField(
                $customField->getName().'_new',
                $customField->getType(),
                $fieldConfig
            );
        }

        return parent::_prepareForm();
    }

    private function _findValue($name, $data)
    {
        if (!is_array($data)) {
            return false;
        }

        foreach ($data as $value) {
            if ($value['name'] == $name) {
                return $value['value'];
            }
        }

        return false;
    }

    /**
     * Get custom fields.
     *
     * @return Cminds_Marketplace_Model_Mysql4_Fields_Collection
     */
    public function getCustomFieldsCollection()
    {
        $collection = Mage::getModel('marketplace/fields')->getCollection();

        return $collection;
    }

    /**
     * Get custom field value.
     *
     * @param Cminds_Marketplace_Model_Fields $customField
     *
     * @return string
     */
    public function getCustomFieldValue($customField)
    {
        if (!is_array($this->getCustomFieldsValues())) {
            return '';
        }

        foreach ($this->getCustomFieldsValues() as $customFieldsValue) {
            if ($customField->getName() === $customFieldsValue['name']) {
                return $customFieldsValue['value'];
            }
        }

        return '';
    }

    /**
     * Get custom fields values of current customer.
     *
     * @return array
     */
    public function getCustomFieldsValues()
    {
        if (!$this->customFieldsValues) {
            $customer = Mage::registry('current_customer');
            $this->customFieldsValues = unserialize($customer->getCustomFieldsValues());
        }

        return $this->customFieldsValues;
    }
}
