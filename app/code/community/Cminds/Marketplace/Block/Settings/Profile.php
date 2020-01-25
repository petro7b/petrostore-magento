<?php

class Cminds_Marketplace_Block_Settings_Profile extends Cminds_Marketplace_Block_Supplier
{
    /**
     * Get custom fields collection.
     *
     * @return Cminds_Marketplace_Model_Mysql4_Fields_Collection
     */
    public function getCustomFields()
    {
        $collection = Mage::getModel('marketplace/fields')->getCollection();

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

    protected function _getTextField( $attribute, $data, $required )
    {
        $value = $this->_getValue( $attribute->getName(), $data );
        $class = $required ? ' required-entry' : '';

        return '<input type="text" value="' . $value . '" name="' . $attribute->getName() . '" id="' . $attribute->getName() . '" class="input-text form-control' . $class . '">';
    }

    protected function _getTextareaField( $attribute, $data, $required )
    {
        $value = $this->_getValue( $attribute->getName(), $data );
        $class = $required ? ' required-entry' : '';
        $class .= $attribute->getIsWysiwyg() ? ' wysiwyg' : '';

        return '<textarea name="' . $attribute->getName() . '" id="' . $attribute->getName() . '" class="input-text form-control' . $class . '"">' . $value . '</textarea>';
    }

    protected function _getValue( $customFieldName, $data ) {
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

    public function getCustomFieldValue($customFieldName, $data)
    {
        if ( !is_array( $data ) ) {
            return '';
        }

        foreach ( $data AS $value ) {
            if ( $customFieldName->getName() == $value['name'] ) {
                return $value['value'];
            }
        }

        return '';
    }
}