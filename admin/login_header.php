<header class="login-header">
    <div class="logo-box">
         <img src="storelogo.jpeg" " class="logo-img">
        <span class="logo-text">LOZ PC STORE</span>
    </div>
</header>

<style>
.login-header{
    position:fixed;
    top:0;
    width:100%;
    height:70px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(90deg,#00c6ff,#0072ff);
    color:#fff;
    font-size:26px;
    font-weight:700;
    letter-spacing:2px;
    box-shadow:0 5px 20px rgba(0,0,0,0.2);
    z-index:999;
}

.logo-box{
    display:flex;
    align-items:center;
    gap:12px;
}

.logo-img{
    width:45px;
    height:45px;
    border-radius:10px;
    object-fit:cover;
    box-shadow:0 0 10px rgba(255,255,255,0.6);
}

.logo-text{
    text-shadow:0 0 8px rgba(255,255,255,0.8);
}
</style>