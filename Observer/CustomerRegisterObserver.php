<?php

namespace Anas\CustomerCoupon\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\Data\CouponInterfaceFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Translate\Inline\StateInterface;

class CustomerRegisterObserver implements ObserverInterface
{
    protected $logger;
    protected $ruleFactory;
    protected $date;
    protected $couponRepository;
    protected $couponInterfaceFactory;
    protected $transportBuilder;
    protected $storeManager;
    protected $scopeConfig;
    protected $inlineTranslation;

    public function __construct(
        LoggerInterface $logger,
        RuleFactory $ruleFactory,
        DateTime $date,
        CouponRepositoryInterface $couponRepository,
        CouponInterfaceFactory $couponInterfaceFactory,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        StateInterface $inlineTranslation
    ) {
        $this->logger = $logger;
        $this->ruleFactory = $ruleFactory;
        $this->date = $date;
        $this->couponRepository = $couponRepository;
        $this->couponInterfaceFactory = $couponInterfaceFactory;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->inlineTranslation = $inlineTranslation;
    }

    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $email = $customer->getEmail();
        $name = $customer->getFirstname();

        $this->logger->info('Customer registered: ' . $email);

        $ruleId = 1; // Your active cart price rule ID
        $rule = $this->ruleFactory->create()->load($ruleId);

        if ($rule->getId()) {
            $couponCode = 'REG-' . strtoupper(substr(md5(uniqid()), 0, 8));

            $coupon = $this->couponInterfaceFactory->create();
            $coupon->setRuleId($ruleId);
            $coupon->setCode($couponCode);
            $coupon->setIsPrimary(false);
            $coupon->setUsageLimit(1);
            $coupon->setUsagePerCustomer(1);
            $coupon->setDescription('CUSTOMER_EMAIL:' . $email);
            $coupon->setTimesUsed(0);
            $coupon->setCreatedAt($this->date->gmtDate());
            $coupon->setExpirationDate('2025-12-31');

            try {
                $this->couponRepository->save($coupon);
                $this->logger->info("Generated coupon for $email: $couponCode");

                $storeId = $this->storeManager->getStore()->getId();
                $transport = $this->transportBuilder
                    ->setTemplateIdentifier(2) // Must match the code of your template
                    ->setTemplateOptions([
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $storeId,
                    ])
                    ->setTemplateVars([
                        'customer_name' => $name,
                        'coupon_code' => $couponCode,
                    ])
                    ->setFromByScope('general') // Uses General Contact from store email settings
                    ->addTo($email, $name)
                    ->getTransport();

                $transport->sendMessage();

                $this->logger->info("Coupon email sent to $email");

            } catch (\Exception $e) {
                $this->logger->error("Failed to save or email coupon: " . $e->getMessage());
            }
        } else {
            $this->logger->error("Cart price rule ID $ruleId not found.");
        }
    }
}
