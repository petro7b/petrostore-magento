<?php
class Cminds_Marketplace_Block_Report_Abstract extends Mage_Core_Block_Template {
    protected $_lastColumnHeader = 'Qty Ordered';
    protected $_lastColumnValue = 'qty_ordered';
    protected $_columns = false;
    protected $_removeIndexes = false;
    protected $_availableIndexes = false;

    public function getCollection() {
        $collection = $this->_prepareCollection();

        return $collection;
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel($this->_resourceModel);

        if($this->getFilter('period_type') != 'year') {
            if($this->getFilter('from') && $this->getFilter('to')) {
                $collection->setDateRange($collection->formatDate($this->getFilter('from')), $collection->formatDate($this->getFilter('to')));
            }
        }

        if($this->getFilter('period_type')) {
            $collection->setPeriod($this->getFilter('period_type'));
        }
        else{
            $collection->addFieldToFilter('entity_id', 0);
        }
        $collection->getSelect()->joinInner(array('eav' => $this->_getEntityIntTable()), 'eav.entity_id = product_id AND eav.attribute_id = ' . $this->_getAttributeId(), array() );
        $collection->getSelect()->where('eav.value = ?', $this->_getSupplierId());

        $collection->addStoreFilter(0);
        return $collection->load();
    }

    protected function getFilter($key) {
        return $this->getRequest()->getPost($key);
    }

    /**
     * Get period for sorting.
     *
     * @param $dateString
     *
     * @return date
     */
    public function getPeriodString($dateString)
    {
        $date = Mage::helper('marketplace')->getTimeCreatedAt($dateString);

        switch ($this->getFilter('period_type')) {
            case 'day':
                return $date->format('D, F d');
                break;
            case 'month':
                return $date->format('F Y');
                break;
            case 'year':
                return $date->format('Y');
                break;
            default :
                return $date->format('m/d/Y');
                break;
        }
    }

    public function getTitle() {
        return $this->title;
    }

    public function getLastColumnHeader() {
        return $this->_lastColumnHeader;
    }

    public function getLastColumnIndex() {
        return $this->_lastColumnValue;
    }

    protected function _getEntityIntTable() {
        return Mage::getSingleton("core/resource")->getTableName('catalog_product_entity_int');
    }

    protected function _getAttributeId() {
        $eavAttribute   = new Mage_Eav_Model_Mysql4_Entity_Attribute();
        return $eavAttribute->getIdByCode('catalog_product', 'creator_id');
    }

    protected function _getSupplierId() {
        return Mage::helper('marketplace')->getSupplierId();
    }

    public function getCsvFileEnhanced() {
        $collectionData = $this->getCollection()->getData();

        $this->_isExport = true;

        $io = new Varien_Io_File();

        $path = Mage::getBaseDir('var') . DS . 'export' . DS;
        $name = md5(microtime());
        $file = $path . DS . $name . '.csv';

        while (file_exists($file)) {
            sleep(1);
            $name = md5(microtime());
            $file = $path . DS . $name . '.csv';
        }

        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        $io->streamOpen($file, 'w+');
        $io->streamLock(true);

        if($this->_columns) {
            $io->streamWriteCsv($this->_columns);
        }
        foreach($this->getCollection() AS $item) {
            $i = $item->getData();
            if(!$this->_availableIndexes) {
                if($this->_removeIndexes && is_array($this->_removeIndexes)) {
                    foreach($this->_removeIndexes AS $index) {
                        unset($i[$index]);
                    }
                }

                foreach($i AS $k => $v) {
                    if(is_object($v)) {
                        unset($i[$k]);
                    }
                }
                $io->streamWriteCsv($i);
            } else {
                $d = array();
                foreach($this->_availableIndexes AS $k) {
                    $d[] = $i[$k];
                }
                $io->streamWriteCsv($d);
            }
        }

        $io->streamUnlock();
        $io->streamClose();

        return array(
            'type'  => 'filename',
            'value' => $file,
            'rm'    => true
        );
    }
}