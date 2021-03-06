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
namespace Az2009\Rede\Model\Method\Dc\Response;

class Payment extends \Az2009\Rede\Model\Method\Cc\Response\Payment
{
    public function __construct(
        \Az2009\Rede\Model\Method\Dc\Transaction\Authorize $authorize,
        \Az2009\Rede\Model\Method\Dc\Transaction\Unauthorized $unauthorized,
        \Az2009\Rede\Model\Method\Dc\Transaction\Capture $capture,
        \Az2009\Rede\Model\Method\Dc\Transaction\Pending $pending,
        \Az2009\Rede\Model\Method\Dc\Transaction\Cancel $cancel,
        array $data = []
    ) {
        parent::__construct($authorize, $unauthorized, $capture, $pending, $cancel, $data);
    }

    public function process()
    {
        if ($this->getPayment()->getLastTransId()) {
            return parent::process();
        }

        if (!$this->getPayment()) {
            $this->_getPaymentInstance();
        }

        $body = $this->getBody(\Zend\Json\Json::TYPE_ARRAY);
        if (!isset($body['threeDSecure']['url'])) {
            throw new \Exception(__('Invalid url authenticate'));
        }

        $this->_pending
            ->setPayment($this->getPayment())
            ->setResponse($this->getResponse())
            ->process();
    }

}