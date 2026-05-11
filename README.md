# ItchenKing Upsell

A custom WooCommerce upsell plugin that helps customers unlock free delivery by showing a progress bar and recommended product slider on the cart and checkout pages.

The plugin calculates the remaining amount needed for free delivery and displays matching products that can be added directly to the cart without leaving the page.

## Features

- Free delivery progress bar
- Cart and checkout page upsell widget
- Product slider for recommended items
- AJAX add to cart
- Instant widget refresh after product is added
- Cart and checkout totals refresh
- Support for simple products
- Support planned/implemented for variable products with variation selection
- Custom WooCommerce hooks
- Lightweight PHP, JavaScript, and CSS structure

## Plugin Purpose

The goal of this plugin is to increase average order value by encouraging customers to add more products to their cart in order to qualify for free delivery.

Example:

> You are £8.50 away from free delivery. Add one of these products to unlock free shipping.

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