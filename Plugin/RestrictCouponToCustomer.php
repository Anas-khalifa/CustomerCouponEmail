<?php
namespace Anas\CustomerCoupon\Plugin;

use Magento\SalesRule\Model\Rule;
use Magento\Quote\Model\Quote;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;

class RestrictCouponToCustomer
{
    protected $customerSession;

    public function __construct(
        CustomerSession $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    public function aroundCanProcessRule(
        \Magento\SalesRule\Model\Validator $subject,
        \Closure $proceed,
        Rule $rule,
        Quote $quote
    ) {
        $couponCode = $quote->getCouponCode();
        $coupon = $rule->getCoupon(); // ✅ correct method

        if ($coupon && $couponCode) {
            $desc = $coupon->getDescription();

            if (strpos($desc, 'CUSTOMER_EMAIL:') !== false) {
                $allowedEmail = trim(str_replace('CUSTOMER_EMAIL:', '', $desc));
                $currentEmail = $this->customerSession->getCustomer()->getEmail();

                if (strcasecmp($currentEmail, $allowedEmail) !== 0) {
                    return false; // ⛔ block coupon if emails don't match
                }
            }
        }

        return $proceed($rule, $quote); // ✅ allow coupon if allowed
    }
}
