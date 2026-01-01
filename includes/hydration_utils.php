<?php
function get_hydration_message($current, $goal)
{
    if ($goal <= 0)
        return "Set a goal to get started!";

    $pct = ($current / $goal) * 100;

    if ($pct >= 100)
        return "You are hydrated and awesome! 🎉";
    if ($pct >= 75)
        return "Almost there, finish strong! 🚀";
    if ($pct >= 50)
        return "Halfway through. Keep it up! 💪";
    if ($pct >= 25)
        return "Good start! Keep sipping. 💧";

    return "Time to start your hydration streak! ☀️";
}
?>