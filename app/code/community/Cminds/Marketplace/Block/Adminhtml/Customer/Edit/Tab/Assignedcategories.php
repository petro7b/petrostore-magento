<?php

class Cminds_Marketplace_Block_Adminhtml_Customer_Edit_Tab_Assignedcategories extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('marketplace/customer/tab/view/assignedcategories.phtml');
        $this->setUseAjax(true);
        $this->_withProductCount = true;
    }

    protected function _getSelectedCategories($supplierId) {
        $categories = Mage::getModel('marketplace/categories')->getCollection()->addFilter('supplier_id', $supplierId);
        $notSelectedCategories = array();

        foreach($categories AS $category) {
            $notSelectedCategories[] = $category->getCategoryId();
        }

        return $notSelectedCategories;
    }

    public function listCategory($categories) {
        $string = '';
        foreach($categories AS $category) {
            $cat = Mage::getModel('catalog/category')->load($category);
            $string .= '<li class="category-sublist level-'.$cat->getLevel().'" style="margin-left:' . (5*$cat->getLevel()).'px">';

            if($cat->getId() && $cat->getName() != '' && $cat->getId() != 1) {
                $string .= '<input type="checkbox" name="categories_all[]" value="' . $cat->getId() . '" checked hidden />';
                $string .= '<input type="checkbox" name="categories_ids[]" value="'.$cat->getId().'"' . (!in_array($cat->getId(), $this->_getSelectedCategories(Mage::registry('current_customer')->getId())) ? ' checked' : '') . '/>' . $cat->getName();
            }

            if($this->getChildCategories($cat->getId())) {
                $string .= '<i class="category-plus" style="color:red; cursor: pointer; font-weight: bold; font-size: 15px;margin-left: 5px;">-</i><ul class="category-sublist">';
                $string .= $this->listCategory($this->getChildCategories($cat->getId()));
                $string .= '</ul>';
            }
            $string .= '</li>';
        }
        return $string;
    }

    public function getChildCategories($parentId) {
        $parentCat = Mage::getModel('catalog/category')->load($parentId);
        $subcats = $parentCat->getChildren();
        if($subcats != '') {
            return explode(',',$subcats);
        }
        else {
            return false;
        }
    }
}
