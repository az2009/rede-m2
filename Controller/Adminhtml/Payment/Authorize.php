<?php
/**
 * Jefferson Porto
 *
 * Do not edit this file if you want to update this module for future new versions.
 *
 * @category  Az2009
 * @package   Az2009_Rede
 *
 * @copyright Copyright (c) 2018 Jefferson Porto - (https://www.linkedin.com/in/jeffersonbatistaporto/)
 *
 * @author    Jefferson Porto <jefferson.b.porto@gmail.com>
 */
namespace Az2009\Rede\Controller\Adminhtml\Payment;

class Authorize extends AbstractController
{
    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        try {

            $order = $this->_order->load($orderId);

            if (!$order->getId()) {
                throw new \Exception((string)__('Order %1 Not Found', $orderId));
            }

            if ($order->isPaymentReview() ) {

                /** @var \Magento\Sales\Model\Order\Payment $payment*/
                $payment = $order->getPayment();
                $transactionId = $payment->getLastTransId();
                $payment->setParentTransactionId($transactionId);
                $transactionId .= '-capture-offline';
                $payment->setTransactionId($transactionId);

                $user = $this->_auth->getUser()->getUsername();

                $payment->setTransactionAdditionalInfo(
                    \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                    ['User' => $user]
                );

                $payment->registerCaptureNotification($payment->getAmountAuthorized(), true);
                $order->addStatusHistoryComment(__('Order Captured Offline/Manual. Captured by User %1', $user));
                $payment->setAdditionalInformation('captured_offline', true);
                $order->save();
                $this->helper->getMessage()->addSuccess(__('Order Captured Offline'));
            }

        } catch (\Exception $e) {
            $this->helper->getMessage()->addError($e->getMessage());
        }

        return $this->_redirect('sales/order/view/', ['order_id' => $orderId]);
    }
}