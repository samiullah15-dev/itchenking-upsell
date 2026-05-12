# ItchenKing Upsell Free Shipping

A custom WooCommerce plugin that shows a free-delivery progress bar and recommended product slider on the cart and checkout pages. Customers can add simple and variable products directly from the slider without leaving the current page.

## Features

- Free delivery progress bar
- Remaining amount message
- Recommended product slider using Swiper
- AJAX add to cart for simple products
- AJAX add to cart for variable products
- Variation dropdowns inside the slider
- Cart fragment refresh after add to cart
- Cart page refresh trigger
- Checkout order review refresh trigger
- Excludes products already in cart
- Shows only purchasable, in-stock products
- Reads WooCommerce Free Shipping minimum amount when available

## Folder Structure

```txt
itchenking-upsell/
├── assets/
│   ├── css/
│   │   └── upsell.css
│   └── js/
│       └── upsell.js
├── includes/
│   ├── class-ajax-handler.php
│   ├── class-free-shipping.php
│   └── class-upsell-query.php
├── templates/
│   └── widget.php
├── itchenking-upsell.php
├── README.md
└── .gitignore
```

## Requirements

- WordPress
- WooCommerce
- PHP 7.4 or higher recommended

## Installation

1. Upload the `itchenking-upsell` folder to:

```txt
wp-content/plugins/ or Plugins > Add New Plugin > Upload Plugin
```

2. Open WordPress admin.
3. Go to **Plugins > Installed Plugins**.
4. Activate **ItchenKing Upsell Free Shipping**.
5. Open the cart or checkout page and test the widget.
6. WooCommerce > ItchenKing Upsell
## How It Works

1. The plugin checks the current cart product subtotal.
2. It compares the subtotal with the WooCommerce Free Shipping minimum amount.
3. If the cart is below the threshold, it shows:
   - Remaining amount
   - Progress bar
   - Recommended product slider
4. Simple products can be added instantly with AJAX.
5. Variable products show variation dropdowns inside the slider.
6. After a valid variation is selected, the customer can add it without visiting the product page.
7. After add to cart, the widget, cart fragments, cart page, and checkout review are refreshed.

## Main Files

### `itchenking-upsell.php`

Main plugin file. Loads constants, WooCommerce checks, scripts, styles, hooks, and required classes.

### `includes/class-free-shipping.php`

Finds the free delivery threshold and calculates current progress.

### `includes/class-upsell-query.php`

Finds recommended products near the remaining amount needed for free delivery.

### `includes/class-ajax-handler.php`

Handles AJAX refresh and AJAX add to cart for simple and variable products.

### `templates/widget.php`

Frontend widget template for the progress bar and product slider.

### `assets/js/upsell.js`

Frontend JavaScript for Swiper, WooCommerce variation selection, and AJAX add to cart.

### `assets/css/upsell.css`

Frontend styling for the widget, product cards, variation fields, progress bar, and buttons.

## AJAX Actions

```php
wp_ajax_itchenking_refresh
wp_ajax_nopriv_itchenking_refresh
wp_ajax_itchenking_add_to_cart
wp_ajax_nopriv_itchenking_add_to_cart
```

## Notes

Do not commit sensitive or unnecessary files such as:

```txt
.env
*.zip
*.sql
*.log
node_modules/
vendor/
.vscode/
.idea/
```

Commit these files:

```txt
itchenking-upsell.php
assets/
includes/
templates/
README.md
.gitignore
```

## Versions

1.0.0 first stable version
1.0.1 small bug fix
1.1.0 major rewrite latest
