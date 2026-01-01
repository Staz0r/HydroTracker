<?php
$current_page = basename($_SERVER['PHP_SELF']);
$login_page = BASE_URL . "/login.php";
$display_name = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

$position_class = ($current_page === 'dashboard.php') ? 'relative' : 'sticky top-0';
?>

<nav
    class="flex justify-between items-center px-6 md:px-12 py-4 <?php echo $position_class; ?> bg-white/70 backdrop-blur-lg z-50">
    <div>
        <a href="<?php echo BASE_URL; ?>/index.php" class="flex items-center gap-2">

            <i class="fa-solid fa-droplet text-blue-500 text-2xl"></i>
            <span class="brand-font text-xl font-bold text-blue-600 tracking-tight"> HydroTracker</span>

        </a>
    </div>

    <div class="hidden md:flex gap-8 items-center font-medium text-slate-600">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="<?php echo BASE_URL; ?>/index.php#features" class="hover:text-blue-600 transition">Features</a>
            <a href="<?php echo BASE_URL; ?>/index.php#about" class="hover:text-blue-600 transition">About</a>

            <a href="<?php echo $login_page; ?>?mode=login"
                class="px-5 py-2.5 rounded-xl border border-blue-600 text-blue-600 hover:bg-blue-50 transition">
                Sign In
            </a>

            <a href="<?php echo $login_page; ?>?mode=register"
                class="px-5 py-2.5 rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-200 hover:bg-blue-700 transition">
                Sign Up
            </a>

        <?php elseif (isset($_SESSION['user_id'])): ?>
            <div class="flex items-center gap-2">
                <a href="dashboard.php"
                    class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-slate-50 hover:text-blue-600 transition 
            <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'text-blue-600 bg-blue-50' : 'text-slate-400'; ?>">
                    <i class="fa-solid fa-chart-pie text-lg"></i>
                </a>

                <a href="leaderboard.php"
                    class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-slate-50 hover:text-blue-600 transition 
            <?php echo (basename($_SERVER['PHP_SELF']) == 'leaderboard.php') ? 'text-blue-600 bg-blue-50' : 'text-slate-400'; ?>">
                    <i class="fa-solid fa-chart-simple text-lg"></i>
                </a>

                <a href="settings.php"
                    class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-slate-50 hover:text-blue-600 transition 
            <?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'text-blue-600 bg-blue-50' : 'text-slate-400'; ?>">
                    <i class="fa-solid fa-gear text-lg"></i>
                </a>

                <span class="text-sm text-gray-500 hidden md:inline">Hello,
                    <b><?php echo htmlspecialchars($display_name); ?></b></span>
            </div>

        <?php endif; ?>
    </div>

    <button id="mobile-menu-btn"
        class="md:hidden text-slate-600 text-2xl focus:outline-none p-2 rounded-lg hover:bg-slate-100 transition">
        <i class="fa-solid fa-bars"></i>
    </button>

    <div id="mobile-menu"
        class="hidden absolute top-full left-0 w-full bg-white border-t border-slate-100 shadow-xl p-4 flex flex-col gap-4 md:hidden">

        <?php if (isset($_SESSION['user_id'])): ?>
            <div>
    <a href="<?php echo BASE_URL; ?>/settings.php"
        class="flex items-center gap-3 p-3 bg-blue-50 active:bg-blue-100 rounded-xl block font-medium text-slate-700">
        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-500 shrink-0">
            <i class="fa-solid fa-user"></i>
        </div>
        <div>
            <p class="text-xs text-blue-400 font-bold uppercase">Signed in as</p>
            <p class="font-bold text-slate-700"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
        </div>
    </a>
</div>

<a href="<?php echo BASE_URL; ?>/dashboard.php"
   class="flex items-center gap-3 w-full py-3 px-3 rounded-xl hover:bg-slate-50 font-medium transition-colors <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'text-blue-600 font-bold' : 'text-slate-600'; ?>">
    
    <div class="w-10 flex justify-center shrink-0">
        <i class="fa-solid fa-chart-pie text-xl text-blue-500"></i>
    </div>
    
    <span>Dashboard</span>
</a>

<a href="<?php echo BASE_URL; ?>/leaderboard.php"
   class="flex items-center gap-3 w-full py-3 px-3 rounded-xl hover:bg-slate-50 font-medium transition-colors <?php echo (basename($_SERVER['PHP_SELF']) == 'leaderboard.php') ? 'text-blue-600 font-bold' : 'text-slate-600'; ?>">
    
    <div class="w-10 flex justify-center shrink-0">
        <i class="fa-solid fa-chart-simple text-xl text-blue-500"></i>
    </div>
    
    <span>Leaderboard</span>
</a>

<a href="<?php echo BASE_URL; ?>/actions/logout.php"
   class="flex items-center gap-3 w-full py-3 px-3 rounded-xl text-red-500 hover:bg-red-50 font-medium transition-colors">
   
    <div class="w-10 flex justify-center shrink-0">
        <i class="fa-solid fa-right-from-bracket text-xl"></i>
    </div>
    
    <span>Logout</span>
</a>

        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>/index.php#features" class="block py-2 text-slate-600 font-medium">Features</a>

            <div class="flex flex-col gap-3 mt-2">
                <a href="<?php echo BASE_URL; ?>/login.php?mode=login"
                    class="w-full text-center py-3 rounded-xl border border-blue-600 text-blue-600 font-bold">
                    Sign In
                </a>
                <a href="<?php echo BASE_URL; ?>/login.php?mode=register"
                    class="w-full text-center py-3 rounded-xl bg-blue-600 text-white font-bold shadow-lg shadow-blue-200">
                    Sign Up
                </a>
            </div>
        <?php endif; ?>
    </div>
</nav>

<script>
    // Simple toggle logic for the mobile menu
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');

        if (!btn || !menu) return;

        btn.addEventListener('click', () => {
            menu.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            // Check if the menu is currently OPEN
            if (!menu.classList.contains('hidden')) {

                // If the click was NOT inside the menu AND NOT on the button...
                if (!menu.contains(e.target) && !btn.contains(e.target)) {
                    menu.classList.add('hidden'); // ...then close it.
                }
            }
        });
    });
</script>