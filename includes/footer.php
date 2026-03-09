<?php
$padding = isset($footer_padding) ? $footer_padding : 'py-16';
?>

<footer id="about" class="<?php echo $padding; ?> <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'border-t border-slate-100' : ''; ?> text-center">
    <div class="flex items-center justify-center gap-2 mb-2">
        <i class="fa-solid fa-droplet text-blue-500"></i>
        <span class="brand-font font-bold text-blue-600">HydroTracker</span>
    </div>
    <p class="description-font text-slate-400 mb-6">A simple water tracking app</p>
    <p class="text-sm font-bold text-slate-600">Designed and Developed by Staz0r</p>
    <p class="text-xs text-slate-400">Last Edited: <?php echo date("Y/m/d"); ?></p>
    <p class="text-xs text-slate-400">
        &copy; <?php echo date('Y'); ?> HydroTracker. All rights reserved.
    </p>
</footer>