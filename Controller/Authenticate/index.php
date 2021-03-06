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
namespace Az2009\Rede\Controller\Authenticate;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Index extends \Magento\Checkout\Controller\Onepage
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Model\Order $order
    ) {
        parent::__construct(
            $context, $customerSession, $customerRepository,
            $accountManagement, $coreRegistry, $translateInline,
            $formKeyValidator, $scopeConfig, $layoutFactory,
            $quoteRepository, $resultPageFactory, $resultLayoutFactory,
            $resultRawFactory, $resultJsonFactory
        );

        $this->_order = $order;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $url = 'checkout/onepage/failure/';
        $session = $this->getOnepage()->getCheckout();
        $orderId = $session->getLastOrderId();
        $order = $this->_order->load($orderId);

        try {

            if (!$order->getId()) {
                $url = '/';
                throw new \Exception(__('Order Not Found'));
            }

            $payment = $order->getPayment();
            if ($payment->getMethod() != \Az2009\Rede\Model\Method\Dc\Dc::CODE_PAYMENT) {
                throw new \Exception(__('Action not allowed to this order'));
            }

            $urlRedirect = $payment->getAdditionalInformation('redirect_url');
            if (empty($urlRedirect)) {
                throw new \Exception(__('URL authentication not available. Try Again.'));
            }

            return $this->getResponse()
                        ->setRedirect($urlRedirect)
                        ->sendResponse();

        } catch(\Exception $e) {
            if ($order->getId()) {
                $order->registerCancellation($e->getMessage())->save();
            }

            $this->messageManager->addError($e->getMessage());
        }

        return $this->_redirect($url);
    }
}