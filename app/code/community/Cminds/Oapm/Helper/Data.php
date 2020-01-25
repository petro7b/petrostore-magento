<?php

/**
 * Cminds OAPM data helper.
 *
 * @category    Cminds
 * @package     Cminds_Oapm
 * @author      Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Cminds_Oapm_Helper_Data extends Mage_Core_Helper_Abstract
{
    const EMAIL_ORDER_PLACED_PAYER = 'order_placed_payer';
    const EMAIL_ORDER_PAYED_PAYER = 'order_payed_payer';
    const EMAIL_ORDER_CANCELED_PAYER = 'order_canceled_payer';
    const EMAIL_ORDER_PENDING_REMINDER_PAYER = 'order_pending_last_reminder_payer';
    const EMAIL_ORDER_PENDING_LAST_REMINDER_PAYER = 'order_pending_last_reminder_payer';

    const EMAIL_ORDER_PLACED_CREATOR = 'order_placed_creator';
    const EMAIL_ORDER_PAYED_CREATOR = 'order_payed_creator';
    const EMAIL_ORDER_CANCELED_CREATOR = 'order_canceled_creator';
    const EMAIL_ORDER_PENDING_LAST_REMINDER_CREATOR = 'order_pending_last_reminder_creator';

    const EMAIL_ADMIN_APPROVED_ORDER = 'admin_approved_order';

    /**
     * Return email template xml id.
     *
     * @param   string $type
     * @return  string
     * @throws  Mage_Core_Exception
     */
    protected function getTemplateId($type)
    {
        switch ($type) {
            case self::EMAIL_ORDER_PLACED_PAYER:
                $xmlPath = 'cminds_oapm_order_placed_payer_notification_email';
                break;
            case self::EMAIL_ORDER_PAYED_PAYER:
                $xmlPath = 'cminds_oapm_order_payed_payer_notification_email';
                break;
            case self::EMAIL_ORDER_CANCELED_PAYER:
                $xmlPath = 'cminds_oapm_order_canceled_payer_notification_email';
                break;
            case self::EMAIL_ORDER_PENDING_REMINDER_PAYER:
                $xmlPath = 'cminds_oapm_order_pending_reminder_payer_notification_email';
                break;
            case self::EMAIL_ORDER_PENDING_LAST_REMINDER_PAYER:
                $xmlPath = 'cminds_oapm_order_pending_last_reminder_payer_notification_email';
                break;
            case self::EMAIL_ORDER_PLACED_CREATOR:
                $xmlPath = 'cminds_oapm_order_placed_creator_notification_email';
                break;
            case self::EMAIL_ORDER_PAYED_CREATOR:
                $xmlPath = 'cminds_oapm_order_payed_creator_notification_email';
                break;
            case self::EMAIL_ORDER_CANCELED_CREATOR:
                $xmlPath = 'cminds_oapm_order_canceled_creator_notification_email';
                break;
            case self::EMAIL_ORDER_PENDING_LAST_REMINDER_CREATOR:
                $xmlPath = 'cminds_oapm_order_pending_last_reminder_creator_notification_email';
                break;
            case self::EMAIL_ADMIN_APPROVED_ORDER:
                $xmlPath = 'cminds_oapm_admin_approved_order_notification_email';
                break;
            default:
                throw new Mage_Core_Exception($this->__('Unsupported template type.'));
        }

        return $xmlPath;
    }

    /**
     * Send email.
     *
     * @param   string $type
     * @param   array $recipient
     * @param   array $data
     * @return  Cminds_Oapm_Helper_Data
     */
    protected function sendEmail($type, $recipient, $data)
    {
        $storeId = Mage::app()->getStore()->getId();

        /** @var Mage_Core_Model_Translate $translate */
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        $templateId = $this->getTemplateId($type);

        Mage::getModel('core/email_template')
            ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
            ->sendTransactional(
                $templateId,
                Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY, $storeId),
                $recipient['email'],
                $recipient['name'],
                $data
            );

        $translate->setTranslateInline(true);

        return $this;
    }

    /**
     * Send notification to order creator that order has been placed using OAPM payment method.
     *
     * @param   array $recipientData
     * @return  Cminds_Oapm_Helper_Data
     */
    public function sendOrderPlacedCreatorNotification($recipientData)
    {
        $this->sendEmail(
            self::EMAIL_ORDER_PLACED_CREATOR,
            array(
                'email' => $recipientData['creator_email'],
                'name' => $recipientData['creator_name'],
            ),
            array(
                'creator_name' => $recipientData['creator_name'],
                'payer_name' => $recipientData['payer_name'],
                'payer_email' => $recipientData['payer_email'],
                'order' => $recipientData['order'],
            )
        );

        return $this;
    }

    /**
     * Send notification to person which has been marked to pay for the order placed using OAPM payment method.
     *
     * @param   array $recipientData
     * @return  Cminds_Oapm_Helper_Data
     */
    public function sendOrderPlacedPayerNotification($recipientData)
    {
        $this->sendEmail(
            self::EMAIL_ORDER_PLACED_PAYER,
            array(
                'email' => $recipientData['payer_email'],
                'name' => $recipientData['payer_name'],
            ),
            array(
                'creator_name' => $recipientData['creator_name'],
                'creator_email' => $recipientData['creator_email'],
                'payer_name' => $recipientData['payer_name'],
                'payer_note' => $recipientData['payer_note'],
                'checkout_url' => $recipientData['checkout_url'],
                'cancel_url' => $recipientData['cancel_url'],
                'order' => $recipientData['order'],
            )
        );

        return $this;
    }

    /**
     * Send notification to order creator that order has been payed.
     *
     * @param   array $recipientData
     * @return  Cminds_Oapm_Helper_Data
     */
    public function sendOrderPayedCreatorNotification($recipientData)
    {
        $this->sendEmail(
            self::EMAIL_ORDER_PAYED_CREATOR,
            array(
                'email' => $recipientData['creator_email'],
                'name' => $recipientData['creator_name'],
            ),
            array()
        );

        return $this;
    }

    /**
     * Send notification to order payer that order has been payed.
     *
     * @param   array $recipientData
     * @return  Cminds_Oapm_Helper_Data
     */
    public function sendOrderPayedPayerNotification($recipientData)
    {
        $this->sendEmail(
            self::EMAIL_ORDER_PAYED_PAYER,
            array(
                'email' => $recipientData['payer_email'],
                'name' => $recipientData['payer_name'],
            ),
            array(
                'creator_name' => $recipientData['creator_name'],
            )
        );

        return $this;
    }

    /**
     * Send notification to order creator that order has been canceled.
     *
     * @param   array $recipientData
     * @return  Cminds_Oapm_Helper_Data
     */
    public function sendOrderCanceledCreatorNotification($recipientData)
    {
        $this->sendEmail(
            self::EMAIL_ORDER_CANCELED_CREATOR,
            array(
                'email' => $recipientData['creator_email'],
                'name' => $recipientData['creator_name'],
            ),
            array(
                'payer_name' => $recipientData['payer_name'],
            )
        );

        return $this;
    }

    /**
     * Send notification to order payer that order has been canceled.
     *
     * @param   array $recipientData
     * @return  Cminds_Oapm_Helper_Data
     */
    public function sendOrderCanceledPayerNotification($recipientData)
    {
        $this->sendEmail(
            self::EMAIL_ORDER_CANCELED_PAYER,
            array(
                'email' => $recipientData['payer_email'],
                'name' => $recipientData['payer_name'],
            ),
            array(
                'creator_name' => $recipientData['creator_name'],
            )
        );

        return $this;
    }

    /**
     * Return checkout url.
     *
     * @param   string $hash
     * @return  string
     */
    public function getCheckoutUrl($hash)
    {
        return Mage::getUrl('oapm/checkout/finalize', array('order' => $hash));
    }

    /**
     * Return cancel url.
     *
     * @param   string $hash
     * @return  string
     */
    public function getCancelUrl($hash)
    {
        return Mage::getUrl('oapm/checkout/cancel', array('order' => $hash));
    }

    /**
     * Send reminder to person which has been marked to pay for the order placed using OAPM payment method.
     *
     * @param   array $recipientData
     * @return  Cminds_Oapm_Helper_Data
     */
    public function sendOrderPendingReminderPayerNotification($recipientData)
    {
        $this->sendEmail(
            self::EMAIL_ORDER_PENDING_REMINDER_PAYER,
            array(
                'email' => $recipientData['payer_email'],
                'name' => $recipientData['payer_name'],
            ),
            array(
                'creator_name' => $recipientData['creator_name'],
                'creator_email' => $recipientData['creator_email'],
                'payer_name' => $recipientData['payer_name'],
                'checkout_url' => $recipientData['checkout_url'],
                'cancel_url' => $recipientData['cancel_url'],
                'order' => $recipientData['order'],
            )
        );

        return $this;
    }

    /**
     * Send last reminder to person which has been marked to pay for the order placed using OAPM payment method.
     *
     * @param   array $recipientData
     * @return  Cminds_Oapm_Helper_Data
     */
    public function sendOrderPendingLastReminderPayerNotification($recipientData)
    {
        $this->sendEmail(
            self::EMAIL_ORDER_PENDING_LAST_REMINDER_PAYER,
            array(
                'email' => $recipientData['payer_email'],
                'name' => $recipientData['payer_name'],
            ),
            array(
                'creator_name' => $recipientData['creator_name'],
                'creator_email' => $recipientData['creator_email'],
                'payer_name' => $recipientData['payer_name'],
                'checkout_url' => $recipientData['checkout_url'],
                'cancel_url' => $recipientData['cancel_url'],
                'order' => $recipientData['order'],
            )
        );

        return $this;
    }

    /**
     * Send last reminder to order creator for the order placed using OAPM payment method.
     *
     * @param   array $recipientData
     * @return  Cminds_Oapm_Helper_Data
     */
    public function sendOrderPendingLastReminderCreatorNotification($recipientData)
    {
        $this->sendEmail(
            self::EMAIL_ORDER_PENDING_LAST_REMINDER_CREATOR,
            array(
                'email' => $recipientData['creator_email'],
                'name' => $recipientData['creator_name'],
            ),
            array(
                'creator_name' => $recipientData['creator_name'],
                'creator_email' => $recipientData['creator_email'],
                'payer_name' => $recipientData['payer_name'],
                'payer_email' => $recipientData['payer_email'],
            )
        );

        return $this;
    }
    public function sendAdminApprovedNotification($recipientData)
    {
        $this->sendEmail(
            self::EMAIL_ADMIN_APPROVED_ORDER,
            array (
                'email' => $recipientData['creator_email'],
                'name' => $recipientData['creator_name'],
            ),
            array (
                'order' => $recipientData['order'],
                'checkout_url' => $recipientData['checkout_url'],
                'cancel_url' => $recipientData['cancel_url']
            )
        );

        return $this;
    }
}
