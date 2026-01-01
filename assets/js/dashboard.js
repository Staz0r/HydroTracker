document.addEventListener('DOMContentLoaded', function() {
    
    const bodyElement = document.querySelector('body');
    const dailyGoal = parseInt(bodyElement.dataset.dailyGoal) || 2000;
    const forms = document.querySelectorAll('.ajax-form');

    // --- TIMING LOGIC ---
    // 1. Check immediately on load
    checkHydrationReminder();
    
    // 2. Start the timer loop (Every 5 seconds for testing)
    setInterval(checkHydrationReminder, 5000); 

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // STOP RELOAD

            const formData = new FormData(this);
            const action = this.getAttribute('action');

            fetch(action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => {
                if (response.status === 401) {
                    alert('You are not logged in. Please log in to continue.');
                    window.location.href = 'login.php';
                    return;
                }
                return response.json();
            })
            .then(data => {
                if (!data) return;

                if(data.status === 'success') {
                    // Update the UI with new data
                    updateDashboardUI(data, dailyGoal);
                    
                    // Reset the form
                    closeManualModal();
                    const manualInput = document.getElementById('custom-amount');
                    if(manualInput) manualInput.value = '';
                    
                    console.log("Hydration Updated!");
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Something went wrong. Check the console.");
            });
        });
    });
});

/**
 * Main function to update the screen after logging water
 */
function updateDashboardUI(data, dailyGoal) {
    const newTotal = parseInt(data.new_total);
    
    // --- 1. RESET REMINDER TIMER ---
    // User just drank water, so we reset the "Last Sip" time to NOW
    const body = document.querySelector('body');
    body.dataset.lastSip = Date.now(); 
    
    // Hide reminder immediately
    const reminderBanner = document.getElementById('reminder-banner');
    if(reminderBanner) reminderBanner.classList.add('hidden');

    // --- 2. UPDATE NUMBERS ---
    const display = document.getElementById('total-drunk-display');
    if(display) display.innerText = newTotal;
    
    const left = Math.max(0, dailyGoal - newTotal);
    const leftText = document.getElementById('ml-left-display');
    const leftLabel = document.getElementById('ml-left-label');

    if(leftText) {
        leftText.innerText = left;
        leftText.classList.remove('text-blue-600', 'text-slate-800');
        
        if(leftLabel) {
            leftLabel.classList.remove('text-blue-500', 'text-slate-600', 'animate-pulse');
            leftLabel.innerText = "ml left"; // Default
        }

        // Motivation Logic
        if (left > 0 && left <= 150) {
            leftText.classList.add('text-blue-600');
            if(leftLabel) {
                leftLabel.innerText = "Take the last sip!";
                leftLabel.classList.add('text-blue-500', 'animate-pulse');
            }
        } else {
            leftText.classList.add('text-slate-800');
            if(leftLabel) leftLabel.classList.add('text-slate-600');
        }
    }
    
    // --- 3. UPDATE HEADER ---
    const headerTotal = document.getElementById('header-total-intake');
    if (headerTotal) headerTotal.innerText = newTotal.toLocaleString();

    const headerMsg = document.getElementById('header-status-msg');
    if (headerMsg && data.status_msg) headerMsg.innerText = data.status_msg;

    // --- 4. VISUAL UPDATES ---
    // Turn text green if goal reached
    if (display && newTotal >= dailyGoal) {
        display.parentElement.classList.remove('text-slate-800');
        display.parentElement.classList.add('text-green-500');
    }

    // Update Water Level (Draining Effect)
    const fill = document.getElementById('water-level-fill');
    if (fill) {
        let percent = ((dailyGoal - newTotal) / dailyGoal) * 100;
        if (percent < 0) percent = 0; 
        if (percent > 100) percent = 100; 
        fill.style.height = percent + '%';
    }

    // Toggle Congratulations Banner
    const successBanner = document.getElementById('goal-success-banner');
    if (successBanner) {
        if (newTotal >= dailyGoal) {
            successBanner.classList.remove('hidden');
            successBanner.classList.remove('animate-fade-in');
            void successBanner.offsetWidth; // Restart animation
            successBanner.classList.add('animate-fade-in');
        } else {
            successBanner.classList.add('hidden');
        }
    }
    
    // Update Bottle Button Values
    const sipAmount = (left > 0 && left < 100) ? left : 100;
    const gulpAmount = (left > 0 && left < 250) ? left : 250;

    const sipInput = document.getElementById('sip-input');
    if(sipInput) sipInput.value = sipAmount;
    
    const sipTooltip = document.getElementById('sip-tooltip');
    if(sipTooltip) {
        sipTooltip.innerHTML = sipTooltip.innerHTML.replace(/\(\d+ml\)/, `(${sipAmount}ml)`);
    }

    const gulpInput = document.getElementById('gulp-input');
    if(gulpInput) gulpInput.value = gulpAmount;

    // --- 5. ADD NEW LOG TO LIST ---
    // Only run this if we actually added water (prevents issues when changing dates)
    const logContainer = document.querySelector('.space-y-3');
    if (logContainer && data.added_amount !== undefined) {
        const emptyState = logContainer.querySelector('.text-center.py-6');
        if (emptyState) emptyState.remove();

        const iconClass = data.added_amount >= 250 
            ? 'fa-glass-water text-blue-500' 
            : 'fa-droplet text-blue-400';

        const newLogHTML = `
            <div class="flex justify-between items-center bg-white border-2 border-slate-100 px-4 py-3 rounded-2xl shadow-sm hover:border-blue-200 transition-colors animate-fade-in">
                <div class="flex items-center gap-3">
                    <i class="fa-solid ${iconClass}"></i>
                    <span class="font-bold text-slate-700">
                        Drank ${data.added_amount} ml
                    </span>
                </div>
                <span class="font-mono text-sm text-slate-400 bg-slate-50 px-2 py-1 rounded-md">
                    ${data.time || 'Just now'}
                </span>
            </div>
        `;
        logContainer.insertAdjacentHTML('afterbegin', newLogHTML);
    }
}

/**
 * Checks if we need to show the reminder
 */
function checkHydrationReminder() {
    const body = document.querySelector('body');
    const reminderBanner = document.getElementById('reminder-banner');
    const successBanner = document.getElementById('goal-success-banner');
    
    // --- CONFIGURATION ---
    const freqMinutes = parseInt(body.dataset.reminderFreq) || 60; 
    const lastSipTs = parseInt(body.dataset.lastSip) || 0;
    
    // Don't show reminder if goal is reached
    if (successBanner && !successBanner.classList.contains('hidden')) {
        reminderBanner.classList.add('hidden');
        return;
    }

    const now = Date.now();
    const timeSinceSip = now - lastSipTs; 

    // --- TESTING MODE ---
    // Change this to 'true' to force the banner to appear after 5 seconds
    const TEST_MODE = false; 

    let limit;
    if (TEST_MODE) {
        limit = 5000; // 5 seconds for testing
        console.log(`Time since sip: ${timeSinceSip}ms | Limit: ${limit}ms`);
    } else {
        limit = freqMinutes * 60 * 1000; // Real limit (e.g., 60 mins)
    }

    if (lastSipTs === 0 || timeSinceSip > limit) {
        reminderBanner.classList.remove('hidden');
    } else {
        reminderBanner.classList.add('hidden');
    }
}

/**
 * Dismisses the reminder and snoozes it
 */
function dismissReminder() {
    const banner = document.getElementById('reminder-banner');
    banner.classList.add('hidden');
    
    // Snooze logic: Fake the "last sip" time so the banner doesn't reappear instantly
    // We set "Last Sip" to (Now - Frequency + 15 mins)
    // This effectively resets the timer to remind you again in 15 mins
    const body = document.querySelector('body');
    const freqMinutes = parseInt(body.dataset.reminderFreq) || 60;
    
    // Calculate 15 minutes in milliseconds
    const snoozeTime = 15 * 60 * 1000; 
    
    // If the frequency is 60 mins, we want to remind them in 15 mins.
    // So we pretend they drank water 45 mins ago.
    body.dataset.lastSip = Date.now() - ((freqMinutes * 60 * 1000) - snoozeTime);
}

// ... (Keep openManualModal, closeManualModal, setAmount, changeDate, fetchDayData, renderDay as they were) ...
// The rest of your functions below here are fine.
function openManualModal() {
    const modal = document.getElementById('manual-modal');
    modal.classList.remove('hidden');
    setTimeout(() => document.getElementById('custom-amount').focus(), 100);
}
function closeManualModal() {
    const modal = document.getElementById('manual-modal');
    modal.classList.add('hidden');
}
function setAmount(amount) {
    document.getElementById('custom-amount').value = amount;
}
function changeDate(offset) {
    const currentDateInput = document.getElementById('current-view-date');
    let current = new Date(currentDateInput.value);
    current.setDate(current.getDate() + offset);
    const newDateStr = current.toISOString().split('T')[0];
    const today = new Date().toISOString().split('T')[0];
    if (newDateStr > today) return;
    fetchDayData(newDateStr);
}
function fetchDayData(dateStr) {
    fetch(`actions/get_day_data.php?date=${dateStr}`)
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            renderDay(data);
        }
    });
}
function renderDay(data) {
    // ... (Your existing renderDay function is fine, paste it here) ...
    // Just make sure it calls the SINGLE updateDashboardUI function we defined above
    document.getElementById('current-view-date').value = data.date;
    document.getElementById('nav-date-display').innerText = data.formatted_date;
    const historyLabel = document.getElementById('nav-history-label');
    const nextBtn = document.getElementById('nav-next-btn');
    if (data.is_today) {
        if(historyLabel) historyLabel.classList.add('hidden');
        if(nextBtn) nextBtn.classList.add('invisible');
    } else {
        if(historyLabel) historyLabel.classList.remove('hidden');
        if(nextBtn) nextBtn.classList.remove('invisible');
    }
    updateDashboardUI({
        new_total: data.total_intake,
        status_msg: data.status_msg
    }, data.daily_goal);
    const container = document.getElementById('log-list-container');
    container.innerHTML = ''; 
    if (data.logs.length === 0) {
        container.innerHTML = `<div class="text-center py-6 text-slate-400 italic">${data.is_today ? 'No water drank yet today.<br>Take a sip!' : 'No records found for this date.'}</div>`;
    } else {
        data.logs.forEach(log => {
            const iconClass = log.amount_ml >= 250 ? 'fa-glass-water text-blue-500' : 'fa-droplet text-blue-400';
            const sipBadge = log.sip_count > 1 ? `<span class="text-blue-500 text-sm ml-1">x${log.sip_count}</span>` : '';
            const html = `<div class="flex justify-between items-center bg-white border-2 border-slate-100 px-4 py-3 rounded-2xl shadow-sm hover:border-blue-200 transition-colors"><div class="flex items-center gap-3"><i class="fa-solid ${iconClass}"></i><span class="font-bold text-slate-700">Drank ${log.amount_ml} ml ${sipBadge}</span></div><span class="font-mono text-sm text-slate-400 bg-slate-50 px-2 py-1 rounded-md">${log.time}</span></div>`;
            container.insertAdjacentHTML('beforeend', html);
        });
    }
}