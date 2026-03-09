<?php
require_once 'config/init.php';

// Check user login state
// Use the constant BASE_URL for safe redirects
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/dashboard.php");
    exit();
}

$page_title = "Home - HydroTracker";
$head_path = ROOT_PATH . '/includes/head.php';
$footer_path = ROOT_PATH . '/includes/footer.php';
?>

<!DOCTYPE html>
<html lang="en">

<?php include $head_path; ?>

<body class="bg-slate-50 text-slate-900 selection:bg-blue-100">

    <?php include ROOT_PATH . '/includes/nav.php'; ?>

    <section
        class="max-w-7xl mx-auto px-6 py-16 md:py-28 flex flex-col md:flex-row items-center gap-16 overflow-hidden">
        <div class="flex-1 space-y-8">
            <h1 class="hero-font text-[clamp(2.5rem,11vw,3rem)] md:text-7xl leading-tight">
                Drink to your heart and track it with <span
                    class="brand-font font-bold marker-highlight decoration-4 underline-offset-8">HydroTracker</span>
            </h1>
            <p class="text-xl text-slate-500 leading-relaxed max-w-lg">
                Turn your daily water intake into a challenge. Empty the bottle, track your stats, and stay healthy with
                HydroTracker.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="login.php?mode=register"
                    class="px-8 py-4 bg-blue-600 text-white rounded-2xl font-bold text-lg hover:scale-105 transition-transform shadow-xl shadow-blue-200">
                    Start Tracking Free
                </a>
                <a href="about.php"
                    class="group px-8 py-4 bg-white border border-slate-200 rounded-2xl font-bold text-lg text-slate-600 hover:text-blue-600 hover:border-blue-200 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex items-center">
                    Learn More
                    <i
                        class="fa-solid fa-arrow-right w-0 overflow-hidden opacity-0 group-hover:w-4 group-hover:opacity-100 group-hover:ml-2 transition-all duration-300"></i>
                </a>
            </div>
        </div>

        <div class="flex-1 relative w-full max-w-sm md:max-w-none mx-auto mt-12 md:mt-0">
            <div
                class="w-full aspect-square bg-white border-2 border-slate-100 rounded-[2rem] md:rounded-[3rem] hero-pattern relative overflow-hidden shadow-2xl shadow-blue-100 transform rotate-2">

                <div
                    class="floating-icon absolute bottom-4 right-4 md:bottom-8 md:right-8 w-24 h-24 md:w-40 md:h-40 bg-blue-500 rounded-2xl md:rounded-3xl flex items-center justify-center shadow-2xl border-4 md:border-8 border-white">

                    <i class="fa-solid fa-droplet text-white text-4xl md:text-6xl"></i>
                </div>
            </div>
        </div>
    </section>


    <section id="features" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h2 class="brand-font text-slate-800 font-bold text-3xl mb-4">Why <span
                    class="text-blue-500">HydroTracker?</span></h2>
            <p class="description-font text-slate-400 mb-16">We gamify the process of tracking water to keep users
                motivated.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-16">
                <?php
                $features = [
                    ['icon' => 'fa-chart-simple', 'title' => 'Visual Tracking', 'desc' => 'Watch your bottle empty as you progress throughout the day.'],
                    ['icon' => 'fa-bolt', 'title' => 'Streaks', 'desc' => 'Keep yourself going and earn badges through daily goals.'],
                    ['icon' => 'fa-ranking-star', 'title' => 'Leaderboard & Stats', 'desc' => 'Compete with others in your journey to maximize the path of water.']
                ];
                foreach ($features as $f): ?>
                    <div
                        class="group p-10 rounded-[2.5rem] bg-slate-50 border border-transparent hover:border-blue-200 hover:bg-white transition-all duration-300">
                        <div
                            class="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center mb-6 mx-auto group-hover:bg-blue-600 transition-colors">
                            <i class="fa-solid <?= $f['icon'] ?> text-blue-500 text-2xl group-hover:text-white"></i>
                        </div>
                        <h3 class="brand-font font-bold text-xl mb-3"><?= $f['title'] ?></h3>
                        <p class="description-font text-slate-500 leading-relaxed"><?= $f['desc'] ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pt-10 border-t border-slate-50">
                <p class="text-slate-400 font-bold uppercase tracking-widest text-xs mb-6">Ready to get hydrated?</p>

                <a href="login.php?mode=register"
                    class="group inline-flex items-center px-10 py-5 bg-blue-600 text-white rounded-2xl font-bold text-lg hover:bg-blue-700 hover:scale-105 hover:shadow-xl hover:shadow-blue-200 transition-all duration-300">

                    Create Free Account

                    <i
                        class="fa-solid fa-arrow-right w-0 overflow-hidden opacity-0 group-hover:w-4 group-hover:opacity-100 group-hover:ml-2 transition-all duration-300"></i>
                </a>
            </div>

        </div>
    </section>

    <?php include $footer_path; ?>

</body>

</html>