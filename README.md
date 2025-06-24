# Magento 2 Customer Coupon Email Extension

This Magento 2 extension allows administrators to assign and email custom discount coupons directly to registered customers from the admin panel. It's ideal for personalized promotions, customer retention, and manual reward systems.

## 📦 Features

✅ Admin can assign any existing cart price rule (coupon) to a customer  
✅ Coupon is emailed directly to the selected customer  
✅ Uses Magento's default email templates with support for customization  
✅ Secure and modular implementation following Magento best practices  
✅ Logs coupon assignments for audit or debugging  

## ✉️ How It Works

1. Admin navigates to **Customers > All Customers**
2. Select a customer and click "Send Coupon" from the customer view page
3. A form appears to select:
   - A **Cart Price Rule (with coupon code)**
   - Optional **custom message**
4. The coupon code is sent to the customer's email
5. All data is logged for traceability

## 📂 Installation

1. Copy the extension to:app/code/Anas/CustomerCoupon

2. Run the following Magento CLI commands:
```bash
php bin/magento module:enable Anas_CustomerCoupon
php bin/magento setup:upgrade
php bin/magento cache:flush
