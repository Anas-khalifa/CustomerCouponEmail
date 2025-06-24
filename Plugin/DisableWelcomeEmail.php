<?php
namespace Anas\CustomerCoupon\Plugin;

use Magento\Customer\Api\Data\CustomerInterface;

class DisableWelcomeEmail
{
    public function aroundNewAccount(
        \Magento\Customer\Model\EmailNotification $subject,
        \Closure $proceed,
        CustomerInterface $customer,
        $type = null,
        $backUrl = ''
    ) {
        // Return null to skip sending welcome email
        return null;
    }
}

