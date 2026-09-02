# Discontinued Products for Magento 2

Discontinued Products is a free Magento 2 module that lets you mark a product as discontinued - it becomes unavailable for purchase everywhere on the storefront, and shoppers see a configurable message or get redirected to a replacement product instead.

Built by [BroSolutions](https://www.brosolutions.net), Magento experts since 2015.

---

## Features

- "Product Discontinued" toggle on the product edit page (own "Discontinued Products" tab)
- Discontinued products can't be purchased anywhere - product page, listings, configurable variants, direct API/cart calls all respect it
- Configurable message shown on the product page instead of "Add to Cart"
- Optional redirect to another product by SKU (validated on save: must exist, can't point to itself)
- "Replacement Products" block on the product page, linking to suggested alternatives
- Extension point for other modules to pull a discontinued product's replacement suggestions - used out of the box by `brosolutions/quick-order-discontinued-connector`

---

## Installation

Install via Composer:

```bash
composer require brosolutions/discontinued-products
bin/magento module:enable BroSolutions_DiscontinuedProducts
bin/magento setup:upgrade
```

---

## Usage

On any product's edit page, open the **Discontinued Products** tab:

- **Product Discontinued** - marks the product as discontinued. It's hidden from purchase everywhere and shows the discontinued message on its page.
- **Redirect to Another Product** + **Redirect to SKU** - optional. If set, shoppers who land on this product's page are redirected straight to the SKU you specify instead of seeing the discontinued message.
- **Replacement Products** - pick one or more products to suggest as alternatives; shown in a "Replacement Products" block on the product page (and to any connected module, like Quick Order).

The default message and the master enable/disable switch live at:

```
Stores → Configuration → Bro Solutions → Discontinued Products
```

---

## Feedback & Contributions

Feel free to open issues or submit pull requests.

Need customizations or help with your Magento store?  
[Contact BroSolutions](mailto:contact@brosolutions.net) or visit [brosolutions.net](https://www.brosolutions.net)

---

## License

This module is open-source and free to use under the MIT license.
