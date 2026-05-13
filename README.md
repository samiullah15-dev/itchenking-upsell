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
- Customization options for button colors, slider arrows, progress bar, text colors, product title/price colors, typography, and font sizes
- Admin settings page under WooCommerc
- Manual product selection for slider
- Product search with image preview in admin

## Folder Structure 

```txt
itchenking-upsell/
├── assets/
│   ├── css/
│   │   ├── upsell.css
│   │   └── admin-settings.css
│   └── js/
│       ├── upsell.js
│       └── admin-settings.js
├── includes/
│   ├── class-admin-settings.php
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
- jQuery
- WooCommerce cart and checkout pages

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
1. If manual products are selected in admin settings, those products are shown in the slider.
2. If no manual products are selected, the plugin shows the lowest-priced available WooCommerce products.
3. Simple products can be added instantly with AJAX.
4. Variable products show variation dropdowns inside the slider.
5. After a valid variation is selected, the customer can add it without  visiting the product page.
6. After add to cart, the widget, cart fragments, cart page, and checkout review are refreshed.
7. When the cart reaches the free delivery threshold, the plugin shows the free delivery unlocked message.

### Admin Settings


- WooCommerce > ItchenKing Upsell

1. Manual Slider Products

2. You can manually search and select WooCommerce products for the slider.

3. If products are selected manually, the slider will show those products first.

4. If no manual products are selected, the plugin automatically shows the lowest-priced visible, purchasable, in-stock WooCommerce products.

### Available style

Button background color
Button text color
Button hover background color
Slider arrow color
Progress bar color
Main text color
Highlighted amount color
Product title color
Product price color

- Typography

Font family
Product title font size
Product price font size
Button font size

## Main Files

### `itchenking-upsell.php`

Main plugin file. Loads constants, WooCommerce checks, scripts, styles, hooks, and required classes, Dynamic frontend CSS from admin settings, Admin CSS and JS loading.

### `includes/class-free-shipping.php`

Finds the free delivery threshold and calculates current progress.

### `includes/class-upsell-query.php`

Shows manually selected products if configured.
Otherwise shows the lowest-priced WooCommerce products.
Excludes products already in cart.
Only shows visible, purchasable, in-stock products.
Supports simple and variable products.

### `includes/class-ajax-handler.php`

Handles AJAX refresh and AJAX add to cart for simple and variable products.

### `templates/widget.php`

Frontend widget template for the progress bar and product slider.

### `assets/js/upsell.js`

Frontend JavaScript for Swiper, WooCommerce variation selection, and AJAX add to cart.

### `assets/css/upsell.css`

Frontend styling for the widget, product cards, variation fields, progress bar, and buttons.

### assets/js/admin-settings.js

WordPress color picker
WooCommerce SelectWoo product search
Product search with image preview
Manual product selector UI

### assets/css/admin-settings.css

Admin settings page styling.Settings cards
Product selector styling
Search dropdown styling
Color field layout
Typography field layou

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
