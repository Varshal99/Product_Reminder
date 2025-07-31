# Product Reminder Module for Magento 2

The **Product Reminder** module for Magento 2 enables automatic email reminders to customers, prompting them to repurchase specific products based on a custom product attribute. It ensures that customers are notified when a product they previously purchased is about to run out.

---

## Preview

* **Product Attribute (Admin Product Page)**
  ![Enable Product Reminder](readme-images/attribute.png?raw=true "Enable Product Reminder")

* **Menu (Marketing → Product Reminder)**
  ![Menu](readme-images/menu.png?raw=true "Menu")

* **Product Reminder Grid (Marketing → Product Reminder → Product Reminder Email Log)**
  ![Product Reminder Grid](readme-images/grid.png?raw=true "Product Reminder Grid")

* **Configuration (Stores → Configuration → Product Reminder → Product Reminder)**
  ![Product Reminder Configuration](readme-images/configuration.png?raw=true "Product Reminder Grid")

* **Email Template View**
  ![Email Template View](readme-images/email.png?raw=true "Email Template View")

---

## Features

* **Custom Product Attribute**: A custom attribute called *Enable Product Reminder*. Reminder emails are sent only if this is enabled for the product.
* **Cron Jobs**:

  * One cron job checks orders within the last 6 months and sends reminder emails.
  * Another cron job cleans up reminder logs older than 7 days.
* **Configuration Options**:

  * Enable/Disable the module.
  * Set a stock threshold to trigger reminder emails.
* **Custom Email Template**: Includes product image, name, price, and a direct product link.
* **Admin Grid**: A dedicated "Product Reminder Email Log" grid under **Marketing → Product Reminder** to view, filter, and manage reminder logs.

---

## Settings

To use the Product Reminder module, enable it from the admin configuration:

**Admin → Stores → Configuration → Product Reminder → Product Reminder**

* **Enable Product Reminder**: Toggle to activate or deactivate the module.
* **Stock Threshold for Reminder**: Customers will receive a reminder if product stock is less than or equal to this value.
  *Example:* If set to 10, and the `repurchase_reminder` attribute is enabled for the product, then reminder emails will be sent only when stock is ≤ 10.

---

## How to Install and Run

1. **Copy the Module**
   Place the `Product_Reminder` directory inside your Magento installation at:
   `app/code/Product/Reminder`

2. **Enable the Module and Deploy**
   Run the following commands from your Magento root directory:

   ```bash
   php bin/magento setup:upgrade
   php bin/magento setup:di:compile
   php bin/magento cache:flush
   ```

3. **Verify Functionality**
   The module should now be active. You can test it by enabling the *Enable Product Reminder* attribute on a product and setting the stock threshold value in the configuration.

