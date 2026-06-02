<?php $current = basename($_SERVER['PHP_SELF']); ?>

<link rel="stylesheet" href="style.css?v=100">

<div class="sidebar" id="sidebar">

    <!-- BRAND -->
    <div class="brand">

        <img src="../storelogo.jpeg" class="logo-img">

        <div class="brand-text">
            <div class="store-name">LOZ PC Store</div>
            <div class="store-sub">Admin Console</div>
        </div>

    </div>

    <!-- OVERVIEW -->
    <a href="admin_dashboard.php"
       class="menu-item <?= $current=='admin_dashboard.php' ? 'active' : '' ?>">

        <div class="menu-left">
            <i data-lucide="layout-dashboard"></i>
            <span class="text">Overview</span>
        </div>

    </a>

    <!-- CATEGORY -->
    <div class="dropdown-wrapper">

        <button type="button"
                class="dropdown-btn <?= in_array($current,
                ['add_category.php','view_category.php'])
                ? 'active open' : '' ?>">

            <div class="menu-left">

                <i data-lucide="layers-3"></i>

                <span class="text">Category</span>

            </div>

            <i data-lucide="chevron-down" class="arrow"></i>

        </button>

        <!-- SUBMENU -->
        <div class="dropdown-menu <?= in_array($current,
        ['add_category.php','view_category.php'])
        ? 'show' : '' ?>">

            <a href="add_category.php"
               class="submenu <?= $current=='add_category.php'
               ? 'active-sub' : '' ?>">
                Add Category
            </a>

            <a href="view_category.php"
               class="submenu <?= $current=='view_category.php'
               ? 'active-sub' : '' ?>">
                View Category
            </a>

        </div>

    </div>

    <!-- PRODUCTS -->
    <div class="dropdown-wrapper">

        <button type="button"
                class="dropdown-btn <?= in_array($current,
                ['add_product.php','admin_product.php','product_review.php'])
                ? 'active open' : '' ?>">

            <div class="menu-left">

                <i data-lucide="monitor-smartphone"></i>

                <span class="text">Products</span>

            </div>

            <i data-lucide="chevron-down" class="arrow"></i>

        </button>

        <!-- SUBMENU -->
        <div class="dropdown-menu <?= in_array($current,
        ['add_product.php','admin_product.php','product_review.php'])
        ? 'show' : '' ?>">

            <a href="add_product.php"
               class="submenu <?= $current=='add_product.php'
               ? 'active-sub' : '' ?>">
                Add Product
            </a>

            <a href="admin_product.php"
               class="submenu <?= $current=='admin_product.php'
               ? 'active-sub' : '' ?>">
                View Product
            </a>

            <a href="product_review.php"
               class="submenu <?= $current=='product_review.php'
               ? 'active-sub' : '' ?>">
                Product Review
            </a>

        </div>

    </div>

    <!-- ORDERS -->
    <a href="admin_orders.php"
       class="menu-item <?= $current=='admin_orders.php' ? 'active' : '' ?>">

        <div class="menu-left">
            <i data-lucide="shopping-cart"></i>
            <span class="text">Orders</span>
        </div>

    </a>

    <!-- CUSTOMERS -->
    <a href="admin_customer.php"
       class="menu-item <?= $current=='admin_customer.php' ? 'active' : '' ?>">

        <div class="menu-left">
            <i data-lucide="users"></i>
            <span class="text">Customers</span>
        </div>

    </a>

    <!-- VIEW REPORT -->
    <a href="view_report.php"
       class="menu-item <?= $current=='view_report.php' ? 'active' : '' ?>">

        <div class="menu-left">
            <i data-lucide="bar-chart-3"></i>
            <span class="text">View Report</span>
        </div>

    </a>

</div>

<!-- LUCIDE -->
<script src="https://unpkg.com/lucide@latest"></script>

<!-- SIDEBAR FUNCTION -->
<script>

document.addEventListener("DOMContentLoaded", function () {

    lucide.createIcons();

    const dropdownBtns =
    document.querySelectorAll(".dropdown-btn");

    dropdownBtns.forEach(btn => {

        btn.addEventListener("click", function () {

            this.classList.toggle("open");

            const menu =
            this.nextElementSibling;

            menu.classList.toggle("show");

        });

    });

});

</script>