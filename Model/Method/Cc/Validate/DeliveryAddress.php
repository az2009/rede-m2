<?php

namespace Az2009\Cielo\Model\Method\Cc\Validate;

class DeliveryAddress extends \Az2009\Cielo\Model\Method\Validate
{
    protected $_fieldsValidate = [
        'Street' => [
            'required' => false,
            'maxlength' => 255,
        ],
        'Number' => [
            'required' => false,
            'maxlength' => 15,
        ],
        'Complement' => [
            'required' => false,
            'maxlength' => 50,
        ],
        'ZipCode' => [
            'required' => false,
            'maxlength' => 9,
        ],
        'City' => [
            'required' => false,
            'maxlength' => 50,
        ],
        'Country' => [
            'required' => false,
            'maxlength' => 35,
        ]
    ];

    public function validate()
    {
        $params = $this->getRequest();
        if (!isset($params['Customer']['DeliveryAddress'])) {
            throw new \Az2009\Cielo\Exception\CC(__('Shipping Address info invalid'));
        }

        $creditCard = $params['Customer']['DeliveryAddress'];
        foreach ($creditCard as $k => $v) {
            $this->required($k,$v, __('Shipping Address: '));
            $this->maxLength($k,$v, __('Shipping Address: '));
        }
    }
}