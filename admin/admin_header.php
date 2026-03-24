<?php
if(session_status() === PHP_SESSION_NONE) session_start();
?>

<header>
    <h2>💻 LOZ PC Store</h2>

    <div class="user-box">
        <?= $_SESSION['admin'] ?? 'Admin' ?> ▼
        <div class="dropdown">
            <img src="storelogo.jpeg" alt="LOZ PC STORE">Logout</a>
        </div>
    </div>
</header>

<style>
header{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:60px;
    background:linear-gradient(90deg,#0f2027,#203a43,#2c5364);
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:0 30px;
    color:#fff;
    z-index:1000;
    box-shadow:0 5px 15px rgba(0,0,0,0.4);
}

.user-box{
    position:relative;
    cursor:pointer;
}

.dropdown{
    display:none;
    position:absolute;
    right:0;
    top:30px;
    background:#1e3c72;
    border-radius:10px;
}

.dropdown a{
    display:block;
    padding:10px 20px;
    color:#fff;
    text-decoration:none;
}

.user-box:hover .dropdown{
    display:block;
}
</style>