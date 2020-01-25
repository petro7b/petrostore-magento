<?php

class Cminds_Marketplace_Block_Adminhtml_Customer_Edit_Tab_Shippingfees_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'      => 'edit_form',
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        $currentMethods = Mage::getModel('marketplace/methods')->getCollection()->addFieldToFilter('supplier_id', Mage::app()->getRequest()->getParam('id'));
        $i = null;
        $fieldset = $form->addFieldset(
            'flatrate_fieldset',
            array(
                'legend' => Mage::helper('marketplace')->__('New Method')
            )
        );

        $fieldset->addField('removedItems', 'hidden', array(
            'name' => 'removedItems',
            'after_element_html' => '<p class="nm"><small><i>' . ' Leave empty if you don\'t want to add a new Method ' . '</i></small></p>',
        ));

        $fieldset->addField(
            'shipping_name[' . $i . ']',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('Shipping Method Name'),
                'name'     => 'shipping_name[]',
                'style' => 'margin-bottom:20px',
            )
        );

        $fieldset->addField('shipping_method_id[' . $i . ']', 'hidden', array(
            'name' => 'shipping_method_id[]',
        ));

        $fieldset->addField(
            'flat_rate_enabled[' . $i . ']',
            'select',
            array(
                'label'    => Mage::helper('marketplace')->__('Flat Rate Enabled'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'flat_rate_enabled[]',
                'options'   => Mage::getModel('adminhtml/system_config_source_yesno')->toArray(),
            )
        );

        $fieldset->addField(
            'flat_rate_fee[' . $i . ']',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('Handling Fee'),
                'name'     => 'flat_rate_fee[]',
                'style' => 'margin-bottom:20px',
            )
        );
        
        // Add custom shipping
        $fieldset->addField(
            'jne_rate_enabled[' . $i . ']',
            'select',
            array(
                'label'    => Mage::helper('marketplace')->__('JNE Enabled'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'jne_rate_enabled[]',
                'options'   => Mage::getModel('adminhtml/system_config_source_yesno')->toArray(),
            )
        );

        $fieldset->addField(
            'jne_rate_fee[' . $i . ']',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('Handling Fee'),
                'name'     => 'jne_rate_fee[]',
                'style' => 'margin-bottom:20px',
            )
        );
        // End of custom shipping

        $fieldset->addField(
            'table_rate_enabled[' . $i . ']',
            'select',
            array(
                'label'    => Mage::helper('marketplace')->__('Table Rate Enabled'),
                'class'    => 'required-entry',
                'required' => true,
                'name'     => 'table_rate_enabled[]',
                'options'   => Mage::getModel('adminhtml/system_config_source_yesno')->toArray(),
            )
        );

        $fieldset->addField(
            'table_rate_file[' . $i . ']',
            'file',
            array(
                'label'    => Mage::helper('marketplace')->__('Upload CSV file'),
                'name'     => 'table_rate_file[]',
            )
        );

        $fieldset->addField(
            'table_rate_fee[' . $i . ']',
            'text',
            array(
                'label'    => Mage::helper('marketplace')->__('Default Handling Fee'),
                'name'     => 'table_rate_fee[]',

            )
        );
        $fieldset->addField(
            'table_rate_condition[' . $i . ']',
            'select',
            array(
                'label'    => Mage::helper('marketplace')->__('Condition'),
                'name'     => 'table_rate_condition[]',
                'options'   => array(
                    1 => Mage::helper('marketplace')->__('Weight vs. Destination'),
                    2 => Mage::helper('marketplace')->__('Price vs. Destination'),
                    3 => Mage::helper('marketplace')->__('# of Items vs. Destination'),
                ),
                'style' => 'margin-bottom:20px',
            )
        );

        $fieldset->addField(
            'free_shipping_enabled[' . $i . ']',
            'select',
            array(
                'label'     => Mage::helper('marketplace')->__('Free Shipping Enabled'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'free_shipping_enabled[]',
                'options'   => Mage::getModel('adminhtml/system_config_source_yesno')->toArray(),
            )
        );

        $i = 0;

        foreach ($currentMethods as $method) {

            $fieldset = $form->addFieldset(
                'flatrate_fieldset[' . $i . ']',
                array(
                    'legend' => $method->getName(),
                )
            );

            $fieldset->addField('shipping_method_id[' . $i . ']', 'hidden', array(
                'name' => 'shipping_method_id[]',
                'value' => $method->getId()
            ));

            $fieldset->setHeaderBar('<button type="button" onclick="removeShippingMethod(' . $i .  ')">Remove</button>');
            $fieldset->addField(
                'shipping_name[' . $i . ']',
                'text',
                array(
                    'label'    => Mage::helper('marketplace')->__('Shipping Method Name'),
                    'name'     => 'shipping_name[]',
                    'value'     => $method->getName(),
                    'style' => 'margin-bottom:20px',
                )
            );

            $fieldset->addField(
                'flat_rate_enabled[' . $i . ']',
                'select',
                array(
                    'label'    => Mage::helper('marketplace')->__('Flat Rate Enabled'),
                    'class'    => 'required-entry',
                    'required' => true,
                    'name'     => 'flat_rate_enabled[]',
                    'options'   => Mage::getModel('adminhtml/system_config_source_yesno')->toArray(),
                    'value'     => $method->getData('flat_rate_available'),
                )
            );

            $fieldset->addField(
                'flat_rate_fee[' . $i . ']',
                'text',
                array(
                    'label'    => Mage::helper('marketplace')->__('Handling Fee'),
                    'name'     => 'flat_rate_fee[]',
                    'value'     => $method->getData('flat_rate_fee'),
                    'style' => 'margin-bottom:20px',
                )
            );

            $fieldset->addField(
                'table_rate_enabled[' . $i . ']',
                'select',
                array(
                    'label'    => Mage::helper('marketplace')->__('Table Rate Enabled'),
                    'class'    => 'required-entry',
                    'required' => true,
                    'name'     => 'table_rate_enabled[]',
                    'options'   => Mage::getModel('adminhtml/system_config_source_yesno')->toArray(),
                    'value'     => $method->getData('table_rate_available')
                )
            );

            $fieldset->addField(
                'table_rate_file[' . $i . ']',
                'file',
                array(
                    'label'    => Mage::helper('marketplace')->__('Upload CSV file'),
                    'name'     => 'table_rate_file[]',
                )
            );

            $fieldset->addField(
                'table_rate_fee[' . $i . ']',
                'text',
                array(
                    'label'    => Mage::helper('marketplace')->__('Default Handling Fee'),
                    'name'     => 'table_rate_fee[]',
                    'value'     => $method->getTableRateFee()

                )
            );
            $fieldset->addField(
                'table_rate_condition[' . $i . ']',
                'select',
                array(
                    'label'    => Mage::helper('marketplace')->__('Condition'),
                    'name'     => 'table_rate_condition[]',
                    'options'   => array(
                        1 => Mage::helper('marketplace')->__('Weight vs. Destination'),
                        2 => Mage::helper('marketplace')->__('Price vs. Destination'),
                        3 => Mage::helper('marketplace')->__('# of Items vs. Destination'),
                    ),
                    'value'     => $method->getTableRateCondition(),
                    'style' => 'margin-bottom:20px',
                )
            );

            $fieldset->addField(
                'free_shipping_enabled[' . $i . ']',
                'select',
                array(
                    'label'     => Mage::helper('marketplace')->__('Free Shipping Enabled'),
                    'class'     => 'required-entry',
                    'required'  => true,
                    'name'      => 'free_shipping_enabled[]',
                    'options'   => Mage::getModel('adminhtml/system_config_source_yesno')->toArray(),
                    'value'     => $method->getData('free_shipping')
                )
            );
            $i++;
        }

        return parent::_prepareForm();
    }
}