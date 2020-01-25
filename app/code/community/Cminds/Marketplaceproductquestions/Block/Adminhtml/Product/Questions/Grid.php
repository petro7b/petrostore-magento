<?php

class Cminds_Marketplaceproductquestions_Block_Adminhtml_Product_Questions_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function _construct()
    {
        parent::_construct();

        $this->setId('product_questions_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $code = $this->getEavAttrCode();
        $table = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'name')->getBackend()->getTable();
        $tableName = Mage::getSingleton('core/resource')->getTableName('catalog/product');
        $tableAnswers = Mage::getSingleton('core/resource')->getTableName('marketplaceproductquestions/answers');

        $collection = Mage::getModel('marketplaceproductquestions/questions')->getCollection()
            ->addFieldToSelect('author_name')
            ->addFieldToSelect('id')
            ->addFieldToSelect('question_body')
            ->addFieldToSelect('visibility')
            ->addFieldToSelect('supplier_id')
            ->addFieldToSelect('created_at');

        $collection->getSelect()
            ->distinct(true)
            ->joinLeft(array('a' => $tableName), 'main_table.product_id = a.entity_id',array('sku'))
            ->joinLeft(array('b' => $table), 'main_table.product_id = b.entity_id AND b.attribute_id ='.$code ,array('value as name'))
            ->joinLeft(array('c' => $tableAnswers), 'main_table.id = c.question_id', array('answer_body', 'question_id', 'admin_id', 'customer_id' ));

        $collection->getSelect();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;

    }

    protected function _prepareColumns()
    {
        $this->addColumn('id',
            array(
                'header'=> Mage::helper('marketplaceproductquestions')->__('ID'),
                'width' => '30px',
                'type'  => 'number',
                'index' => 'id',
                'filter_index' => 'main_table.id'
            ));

        $this->addColumn('sku',
            array(
                'header'=> Mage::helper('marketplaceproductquestions')->__('SKU'),
                'width' => '80px',
                'index' => 'sku',
            ));

        $this->addColumn('name',
            array(
                'header'=> Mage::helper('marketplaceproductquestions')->__('Product name'),
                'width' => '150px',
                'index' => 'name',
                'filter_index' => 'b.value'
            ));

        $this->addColumn('author_name',
            array(
                'header'=> Mage::helper('marketplaceproductquestions')->__('Author Name'),
                'width' => '150px',
                'index' => 'author_name',
                'filter_index' => 'main_table.author_name'
            ));

        $this->addColumn('question_body',
            array(
                'header'=> Mage::helper('marketplaceproductquestions')->__('Question'),
                'index' => 'question_body',
            ));

        $this->addColumn('created_at',
            array(
                'header'=> Mage::helper('marketplaceproductquestions')->__('Created At'),
                'index' => 'created_at',
            ));

        $this->addColumn('answer_body',
            array(
                'header'=> Mage::helper('marketplaceproductquestions')->__('Answer'),
                'index' => 'answer_body',
            ));

        $this->addColumn('visibility',
            array(
                'header'=> Mage::helper('marketplaceproductquestions')->__('Is visible'),
                'index' => 'visibility',
                'width' => '50px',
                'align' => 'right',
                'type' => 'options',
                'options' => array('1' => 'Yes', '0' => 'No')
            ));

        $this->addColumn('supplier_id',
            array(
                'header'=> Mage::helper('marketplaceproductquestions')->__('Supplier Id'),
                'index' => 'supplier_id',
                'width' => '50px',
                'align' => 'right',
            ));

        $this->addColumn('approved',
            array(
                'header'=> Mage::helper('supplierfrontendproductuploader')->__('Approve'),
                'width' => '75px',
                'sortable'  => false,
                'index'     => 'enable',
                'type'      => 'options',
                'renderer' => 'Cminds_Marketplaceproductquestions_Block_Adminhtml_Product_Questions_Renderer_Approve'
            ));

        $this->addColumn('action',
            array(
                'header'=> Mage::helper('marketplaceproductquestions')->__('Action'),
                'width' => '50px',
                'type' => 'action',
                'actions' => array(
                    array('caption' => Mage::helper('marketplaceproductquestions')->__('Edit'),
                        'url' => array('base' =>'*/*/edit',),
                        'field' => 'id'
                ),
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'id',
            ));
    }

    private function getEavAttrCode() {
        $eavAttribute   = new Mage_Eav_Model_Mysql4_Entity_Attribute();
        return $eavAttribute->getIdByCode('catalog_product', 'name');
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('id')
        ->setErrorText(
            Mage::helper('core')->jsQuoteEscape(
                Mage::helper('marketplaceproductquestions')->__('Please select questions')
            )
        );

        $this->getMassactionBlock()->addItem('delete', array(
                'label' => Mage::helper('marketplaceproductquestions')->__('Delete'),
                'url'   => $this->getUrl('*/*/massDelete'),
                'confirm' => Mage::helper('marketplaceproductquestions')->__('Are you sure?')
            ));

        $this->getMassactionBlock()->addItem('approve', array(
            'label' => Mage::helper('marketplaceproductquestions')->__('Set Visible'),
            'url'   => $this->getUrl('*/*/massApprove'),
            'confirm' => Mage::helper('marketplaceproductquestions')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('dissapprove', array(
            'label' => Mage::helper('marketplaceproductquestions')->__('Set Not Visible'),
            'url'   => $this->getUrl('*/*/massDissapprove'),
            'confirm' => Mage::helper('marketplaceproductquestions')->__('Are you sure?')
        ));
        return $this;
    }

}