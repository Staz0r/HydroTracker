<?php
require_once 'config/init.php';

$page_title = "About - HydroTracker";
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>

<body class="bg-blue-50 min-h-screen">

    <?php include 'includes/nav.php'; ?>

    <div class="max-w-2xl mx-auto p-6 mt-6">

        <div class="text-center mb-10">
            <div
                class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-2xl shadow-sm mb-4 text-blue-500">
                <i class="fa-solid fa-droplet text-3xl animate-bounce"></i>
            </div>
            <h1 class="text-3xl font-bold text-slate-800 brand-font">About <span
                    class="text-blue-600">HydroTracker</span></h1>
            <p class="description-font text-slate-400 text-sm mt-2">Gamifying hydration, one sip at a time.</p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-8">
            <div class="p-8">
                <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-bullseye text-blue-500"></i> The Mission
                </h2>
                <p class="text-slate-600 leading-relaxed mb-6">
                    Staying hydrated is crucial for health, but tracking water intake can be boring.
                    <strong>HydroTracker</strong> solves this by turning hydration into a habit-forming game.
                </p>
                <p class="text-slate-600 leading-relaxed">
                    With features like <strong>Smart Reminders</strong>, <strong>Daily Streaks</strong>, and a
                    <strong>Global Leaderboard</strong>, we help users reach their daily goals in a fun and interactive
                    way.
                </p>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 mb-8">
            <div class="p-8">
                <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-code text-blue-500"></i> Tech Stack
                </h2>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">

                    <div
                        class="group relative p-4 bg-slate-50 rounded-xl border border-slate-100 hover:bg-indigo-50 transition-colors cursor-help">
                        <i class="fa-brands fa-php text-3xl text-indigo-500 mb-2"></i>
                        <p class="text-xs font-bold text-slate-600 uppercase">Backend</p>

                        <div
                            class="absolute left-1/2 -translate-x-1/2 top-full mt-3 w-48 p-3 bg-slate-800 text-white text-[10px] leading-tight rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">
                            <div class="absolute -top-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-800 rotate-45"></div>
                            PHP handles all server-side logic and authentication.
                        </div>
                    </div>

                    <div
                        class="group relative p-4 bg-slate-50 rounded-xl border border-slate-100 hover:bg-orange-50 transition-colors cursor-help">
                        <i class="fa-solid fa-database text-3xl text-orange-500 mb-2"></i>
                        <p class="text-xs font-bold text-slate-600 uppercase">Database</p>

                        <div
                            class="absolute left-1/2 -translate-x-1/2 top-full mt-3 w-48 p-3 bg-slate-800 text-white text-[10px] leading-tight rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">
                            <div class="absolute -top-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-800 rotate-45"></div>
                            MySQL stores user data, logs, and settings securely.
                        </div>
                    </div>

                    <div
                        class="group relative p-4 bg-slate-50 rounded-xl border border-slate-100 hover:bg-cyan-50 transition-colors cursor-help">
                        <i class="fa-solid fa-wind text-3xl text-cyan-500 mb-2"></i>
                        <p class="text-xs font-bold text-slate-600 uppercase">Tailwind</p>

                        <div
                            class="absolute left-1/2 -translate-x-1/2 top-full mt-3 w-48 p-3 bg-slate-800 text-white text-[10px] leading-tight rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">
                            <div class="absolute -top-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-800 rotate-45"></div>
                            Tailwind CSS ensures the UI is responsive and beautiful.
                        </div>
                    </div>

                    <div
                        class="group relative p-4 bg-slate-50 rounded-xl border border-slate-100 hover:bg-yellow-50 transition-colors cursor-help">
                        <i class="fa-brands fa-js text-3xl text-yellow-400 mb-2"></i>
                        <p class="text-xs font-bold text-slate-600 uppercase">JavaScript</p>

                        <div
                            class="absolute left-1/2 -translate-x-1/2 top-full mt-3 w-48 p-3 bg-slate-800 text-white text-[10px] leading-tight rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 pointer-events-none">
                            <div class="absolute -top-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-800 rotate-45"></div>
                            JavaScript handles math, modals, and dynamic updates.
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="text-center">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Project Information</p>
            <div class="inline-block bg-white px-6 py-3 rounded-full shadow-sm border border-slate-100">
                <p class="text-sm font-bold text-slate-600">
                    <i class="fa-solid fa-graduation-cap text-blue-500 mr-2"></i>
                    Multimedia Technology 2026
                </p>
            </div>
        </div>

    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>