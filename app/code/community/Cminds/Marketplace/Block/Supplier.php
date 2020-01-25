<?php

class Cminds_Marketplace_Block_Supplier extends Mage_Core_Block_Template {
    public function _construct()
    {
        $this->setTemplate( 'marketplace/supplier.phtml' );
    }

    public function getCustomer()
    {
        return Mage::registry( 'customer' );
    }

    public function getCustomFieldsValues( $skipSystem = false )
    {
        $customer = $this->getCustomer();
        $dbValues = unserialize($customer->getCustomFieldsValues());
        $ret = array();

        foreach ($dbValues as $value) {
            $v = Mage::getModel('marketplace/fields')->load($value['name'],
                'name');
            if ($skipSystem) {
                if ($v->getData('is_system')) {
                    continue;
                }
            }

            if (isset($v)) {
                $ret[] = $value;
            }
        }

        return $ret;
    }

    public function getCustomFields()
    {
        $collection = Mage::getModel( 'marketplace/fields' )->getCollection()
                          ->addFieldToFilter( 'show_in_registration', 1 );

        return $collection;
    }

    public function getCustomField( $field, $data = null, $required = false )
    {
        switch ( $field->getType() ) {
            case 'text' :
                return $this->_getTextField( $field, $data, $required );
                break;
            case 'textarea' :
                return $this->_getTextareaField( $field, $data, $required );
                break;
            case 'date' :
                return $this->_getDateField( $field, $data, $required );
                break;
            default :
                return '';
                break;
        }
    }

    protected function _getTextField( $attribute, $data, $required)
    {
        $value = $this->_getValue( $attribute->getName(), $data );
        $class = $attribute->getIsRequired() ? ' required' : '';

        return '<input type="text" value="' . $value . '" name="' . $attribute->getName() . '" id="' . $attribute->getName() . '" class="input-text form-control' . $class . '">';
    }

    protected function _getTextareaField( $attribute, $data, $required)
    {
        $value = $this->_getValue( $attribute->getName(), $data );
        $class = $attribute->getIsRequired() ? ' required' : '';
        $class .= $attribute->getIsWysiwyg() ? ' wysiwyg' : '';

        return '<textarea name="' . $attribute->getName() . '" id="' . $attribute->getName() . '" class="input-text form-control' . $class . '"">' . $value . '</textarea>';
    }

    protected function _getDateField( $attribute, $data )
    {
        $value = $this->_getValue( $attribute->getName(), $data );
        $class = $attribute->getIsRequired() ? ' required' : '';

        return '<input type="text" value="' . $value . '" name="' . $attribute->getName() . '" id="' . $attribute->getName() . '" value="' . $value . '" class="datepicker input-text form-control' . $class . '">';
    }

    protected function _getValue( $customFieldName, $data )
    {
        if ( ! is_array( $data ) ) {
            return '';
        }

        foreach ( $data AS $value ) {
            if ( $customFieldName == $value['name'] ) {
                return $value['value'];
            }
        }

        return '';
    }

    public function getFieldLabel( $name )
    {
        $label = Mage::getModel( 'marketplace/fields' )
            ->load($name, 'name')
            ->getData( 'label' );

        return $label;
    }

    public function getProductCollection()
    {
        return Mage::getSingleton('marketplace/layer')->getProductCollection();
    }
}