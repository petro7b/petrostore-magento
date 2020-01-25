<?php

/**
 * Cminds OAPM config helper.
 *
 * @category    Cminds
 * @package     Cminds_Oapm
 * @author      Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Cminds_Oapm_Helper_Config extends Mage_Core_Helper_Abstract
{
    /**
     * Payment method configuration field codes.
     */
    const FIELD_ACTIVE_CODE = 'active';
    const FIELD_SANDBOX_CODE = 'sandbox';
    const FIELD_DEBUG_CODE = 'debug';
    const FIELD_TITLE_CODE = 'title';
    const FIELD_REMINDER_INTERVALS_CODE = 'reminder_intervals';
    const FIELD_ORDER_LIFETIME_CODE = 'order_lifetime';

    /**
     * @var bool
     */
    protected $isEnabled;

    /**
     * @var bool
     */
    protected $isSandboxEnabled;

    /**
     * @var bool
     */
    protected $isDebugEnabled;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var array
     */
    protected $reminderIntervals;

    /**
     * @var int
     */
    protected $orderLifetime;

    /**
     * Return bool value if payment method is enabled or not.
     *
     * @return bool
     */
    public function isEnabled()
    {
        if (is_null($this->isEnabled)) {
            $this->isEnabled = (bool)$this->getConfigData(self::FIELD_ACTIVE_CODE);
        }

        return $this->isEnabled;
    }

    /**
     * Return bool value if payment method sandbox is enabled or not.
     *
     * @return bool
     */
    public function isSandboxEnabled()
    {
        if (is_null($this->isSandboxEnabled)) {
            $this->isSandboxEnabled = (bool)$this->getConfigData(self::FIELD_SANDBOX_CODE);
        }

        return $this->isSandboxEnabled;
    }

    /**
     * Return bool value if payment method debug is enabled or not.
     *
     * @return bool
     */
    public function isDebugEnabled()
    {
        if (is_null($this->isDebugEnabled)) {
            $this->isDebugEnabled = (bool)$this->getConfigData(self::FIELD_DEBUG_CODE);
        }

        return $this->isDebugEnabled;
    }

    /**
     * Return payment method title.
     *
     * @return string
     */
    public function getTitle()
    {
        if (is_null($this->title)) {
            $this->title = $this->getConfigData(self::FIELD_TITLE_CODE);
        }

        return $this->title;
    }

    /**
     * Return payment method reminder intervals.
     *
     * @return array
     */
    public function getReminderIntervals()
    {
        if (is_null($this->orderLifetime)) {
            $intervals = $this->getConfigData(self::FIELD_REMINDER_INTERVALS_CODE);
            $intervals = !empty($intervals) ? explode(',', $intervals) : array();

            $intervals = array_map('intval', $intervals);
            asort($intervals);

            $this->reminderIntervals = $intervals;
        }

        return $this->reminderIntervals;
    }

    /**
     * Return payment method order lifetime.
     *
     * @return bool
     */
    public function getOrderLifetime()
    {
        if (is_null($this->orderLifetime)) {
            $this->orderLifetime = (int)$this->getConfigData(self::FIELD_ORDER_LIFETIME_CODE);
        }

        return $this->orderLifetime;
    }

    /**
     * Retrieve information from payment method configuration.
     *
     * @param string $field
     * @param int|string|null|Mage_Core_Model_Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = Mage::app()->getStore();
        }

        $path = sprintf('payment/%s/%s', $this->getCode(), $field);

        return Mage::getStoreConfig($path, $storeId);
    }

    /**
     * Retrieve payment method code.
     *
     * @return string
     */
    public function getCode()
    {
        return Cminds_Oapm_Model_Method_Oapm::METHOD_CODE;
    }

    /**
     * Return bool value which indicates if order lifetime is unlimited or not.
     *
     * @return bool
     */
    public function isOrderLifetimeUnlimited()
    {
        return $this->getOrderLifetime() === 0;
    }

    public function getAdminSenderEmail()
    {
        return Mage::getStoreConfig('trans_email/ident_'.$this->getConfigData('approver_identity').'/email');
    }

    public function getAdminSenderName()
    {
        return Mage::getStoreConfig('trans_email/ident_'.$this->getConfigData('approver_identity').'/name');
    }
}