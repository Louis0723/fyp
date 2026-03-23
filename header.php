<?php session_start(); ?>
<header>
    <h2>💻 PC STORE</h2>
    <nav>
        <a href="products.php">Products</a>

        <?php if (isset($_SESSION['user'])): ?>
            <span>👋 <?= $_SESSION['user']['name']; ?></span>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<style>
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 50px;
    background: #111;
    color: white;
}
nav a {
    margin-left: 20px;
    color: white;
    text-decoration: none;
}
nav a:hover { color: #00c3ff; }
</style>