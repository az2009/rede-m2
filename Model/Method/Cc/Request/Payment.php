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
namespace Az2009\Rede\Model\Method\Cc\Request;

class Payment extends \Magento\Framework\DataObject
{

    const TYPE = 'credit';

    /**
     * @var \Az2009\Rede\Model\Source\Cctype
     */
    protected $_cctype;

    /**
     * @var \Magento\Sales\Model\Order\Address
     */
    protected $billingAddress;

    /**
     * @var \Az2009\Rede\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    public function __construct(
        \Az2009\Rede\Model\Source\Cctype $cctype,
        \Az2009\Rede\Helper\Data $helper,
        array $data = []
    ) {
        $this->_cctype = $cctype;
        $this->helper = $helper;
        parent::__construct($data);
    }

    public function getRequest()
    {
        $this->order = $this->getOrder();
        $this->billingAddress = $this->order->getBillingAddress();
        $payment = $this->order
                        ->getPayment()
                        ->getMethodInstance();

        $info = $payment->getInfoInstance();

        $this->setInfo($info);
        $this->setPayment($payment);

        return $this->setData(
                        [
                           'kind' => Payment::TYPE,
                           'Amount' => $this->helper->formatNumber($info->getAmount()),
                           'installments' => $this->getInstallments(),
                           'capture' => $info->getAdditionalInformation('can_capture'),
                           'softDescriptor' => $this->helper->prepareString($this->getSoftDescriptor(), 13, 0),
                           'cardNumber' => $this->getInfo()->getAdditionalInformation('cc_number'),
                           'cardHolderName' => $this->getInfo()->getAdditionalInformation('cc_name'),
                           'expirationMonth' => $this->getExpMonth(),
                           'expirationYear' => $this->getExpYear(),
                           'subscription' => false,
                           'Origin' => 1,
                           'distributorAffiliation' => $this->helper->getMerchantId(),
                           'securityCode' => $this->getInfo()->getAdditionalInformation('cc_cid'),
                           'Brand' => $this->_cctype->getBrandFormatRede($this->getInfo()->getAdditionalInformation('cc_type'))
                        ]
                      )->toArray();


    }

    /**
     * @return bool|string
     */
    public function getSoftDescriptor()
    {
        $desc = $this->getPayment()
                     ->getConfigData(
                         'billing_description',
                         $this->order->getStoreId()
                     );

        return $desc;
    }

    /**
     * prepare installments
     * @return int
     */
    public function getInstallments()
    {
        $installments = $this->getInfo()->getAdditionalInformation('cc_installments');
        $installments = intval($installments);
        if ($installments <= 0) {
            $installments = 1;
        }

        return $installments;
    }

    /**
     * mock data date due of card
     * @return string
     */
    public function getExpMonth()
    {
        return $this->getInfo()->getAdditionalInformation('cc_exp_month');
    }

    /**
     * mock data date due of card
     * @return string
     */
    public function getExpYear()
    {
        return $this->getInfo()->getAdditionalInformation('cc_exp_year');
    }

}