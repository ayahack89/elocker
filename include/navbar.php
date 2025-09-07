<nav class="custom-navbar">
    <div class="navbar-container">
        <a class="navbar-brand" href="userAccount.php">
            <i class="ri-door-lock-line"></i>Elocker
        </a>

        <button class="navbar-toggle" id="navbar-toggle" aria-controls="navbar-menu" aria-expanded="false">
            <i class="ri-menu-line"></i>
        </button>

        <div class="navbar-menu" id="navbar-menu">
            <div class="navbar-user">
                <i class="ri-user-line"></i>
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
            <a href="logout.php" class="navbar-logout">
                <i class="ri-logout-circle-line"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</nav>

<script>
// Simple JavaScript to toggle the mobile menu
const navbarToggle = document.getElementById('navbar-toggle');
const navbarMenu = document.getElementById('navbar-menu');

if (navbarToggle && navbarMenu) {
    navbarToggle.addEventListener('click', () => {
        navbarMenu.classList.toggle('is-active');
        navbarToggle.setAttribute('aria-expanded', navbarMenu.classList.contains('is-active'));
        
        // Bonus: Change hamburger icon to close icon
        const icon = navbarToggle.querySelector('i');
        if (navbarMenu.classList.contains('is-active')) {
            icon.classList.remove('ri-menu-line');
            icon.classList.add('ri-close-line');
        } else {
            icon.classList.remove('ri-close-line');
            icon.classList.add('ri-menu-line');
        }
    });
}
</script>