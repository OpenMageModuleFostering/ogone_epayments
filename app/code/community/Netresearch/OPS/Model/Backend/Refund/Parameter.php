<?php

/**
 * @author      Paul Siedler <paul.siedler@netresearch.de>
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Model_Backend_Refund_Parameter
    extends Netresearch_OPS_Model_Backend_Parameter_Abstract
{
    /**
     * checks whether we need to retrieve additional parameter for the refund request or not
     *
     * @param Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod
     *
     * @return bool - true if we need to retrieve any additional parameters, false otherwise
     */
    protected function isPmRequiringAdditionalParams(Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod)
    {
        $opsPaymentMethodClass = get_class($opsPaymentMethod);
        $opsPmsRequiringSpecialParams = $this->getOpsConfig()
                                            ->getMethodsRequiringAdditionalParametersFor(
                                                Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_TRANSACTION_TYPE
                                            );

        return (in_array($opsPaymentMethodClass, array_values($opsPmsRequiringSpecialParams)));
    }

    /**
     * sets the model which retrieves the additional params for the refund request
     *
     * @param Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod
     */
    protected function setAdditionalParamsModelFor(Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod)
    {
        if ($opsPaymentMethod instanceof Netresearch_OPS_Model_Payment_OpenInvoice_Abstract) {
            $this->additionalParamsModel = Mage::getModel('ops/backend_refund_additional_openInvoiceNl');
        }
    }

    protected function addPmSpecificParams(
        Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod,
        Varien_Object $payment,
        $amount
    ) { 
        if ($this->isPmRequiringAdditionalParams($opsPaymentMethod)) {
            $this->setAdditionalParamsModelFor($opsPaymentMethod);
            if ($this->additionalParamsModel instanceof
                Netresearch_OPS_Model_Backend_Parameter_Additional_Interface
            ) {
                $params = $this->additionalParamsModel->extractAdditionalParams($payment);
                $this->requestParams = array_merge($this->requestParams, $params);
            }
        }

        return $this;
    }

    /**
     * Returns the order helper for the corresponding transaction type
     *
     * @return Netresearch_OPS_Helper_Order_Abstract
     */
    public function getOrderHelper()
    {
        return Mage::helper('ops/order_refund');
    }


}
