document.addEventListener('DOMContentLoaded', function() {
    
    const bodyElement = document.querySelector('body');
    const dailyGoal = parseInt(bodyElement.dataset.dailyGoal) || 2000;
    const forms = document.querySelectorAll('.ajax-form');

    // --- TIMING LOGIC ---
    // 1. Check immediately on load
    checkHydrationReminder();
    
    // 2. Run the check every 5 seconds
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
                    alert('You are not logged in.');
                    window.location.href = 'login.php';
                    return;
                }
                return response.json();
            })
            .then(data => {
                if (!data) return;

                if(data.status === 'success') {
                    // Update UI and Reset Timer
                    updateDashboardUI(data, dailyGoal);
                    
                    // Reset inputs
                    closeManualModal();
                    const manualInput = document.getElementById('custom-amount');
                    if(manualInput) manualInput.value = '';
                    
                    console.log("Water Logged Successfully!");
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});

/**
 * Updates the Dashboard numbers and RESETS the reminder timer
 */
function updateDashboardUI(data, dailyGoal) {
    const newTotal = parseInt(data.new_total);
    
    // --- CRITICAL FIX: RESET REMINDER TIMER ---
    // We only reset the timer if water was actually ADDED (prevents resetting when changing dates)
    if (data.added_amount !== undefined) {
        const body = document.querySelector('body');
        
        // 1. Update the timestamp to NOW
        body.dataset.lastSip = Date.now(); 
        console.log("Timer Reset! New Last Sip:", body.dataset.lastSip);

        // 2. Force hide the banner immediately
        const reminderBanner = document.getElementById('reminder-banner');
        if(reminderBanner) {
            reminderBanner.classList.add('hidden');
        }
    }

    // --- UPDATE NUMBERS ---
    const display = document.getElementById('total-drunk-display');
    if(display) display.innerText = newTotal;
    
    const left = Math.max(0, dailyGoal - newTotal);
    const leftText = document.getElementById('ml-left-display');
    const leftLabel = document.getElementById('ml-left-label');

    if(leftText) {
        leftText.innerText = left;
        leftText.classList.remove('text-blue-600', 'text-slate-800');
        
        if(leftLabel) {
            leftLabel.innerText = "ml left";
            leftLabel.classList.remove('text-blue-500', 'text-slate-600', 'animate-pulse');
        }

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
    
    // --- UPDATE HEADER ---
    const headerTotal = document.getElementById('header-total-intake');
    if (headerTotal) headerTotal.innerText = newTotal.toLocaleString();

    const headerMsg = document.getElementById('header-status-msg');
    if (headerMsg && data.status_msg) headerMsg.innerText = data.status_msg;

    // --- PROGRESS BAR ---
    const fill = document.getElementById('water-level-fill');
    if (fill) {
        let percent = ((dailyGoal - newTotal) / dailyGoal) * 100;
        if (percent < 0) percent = 0; 
        if (percent > 100) percent = 100; 
        fill.style.height = percent + '%';
    }

    // --- SUCCESS BANNER ---
    const successBanner = document.getElementById('goal-success-banner');
    if (successBanner) {
        if (newTotal >= dailyGoal) {
            successBanner.classList.remove('hidden');
            // Hide reminder if success is shown
            const reminderBanner = document.getElementById('reminder-banner');
            if(reminderBanner) reminderBanner.classList.add('hidden');
        } else {
            successBanner.classList.add('hidden');
        }
    }

    // --- LOG LIST ---
    const logContainer = document.querySelector('.space-y-3');
    if (logContainer && data.added_amount !== undefined) {
        const emptyState = logContainer.querySelector('.text-center.py-6');
        if (emptyState) emptyState.remove();

        const iconClass = data.added_amount >= 250 ? 'fa-glass-water text-blue-500' : 'fa-droplet text-blue-400';
        
        const newLogHTML = `
            <div class="flex justify-between items-center bg-white border-2 border-slate-100 px-4 py-3 rounded-2xl shadow-sm animate-fade-in">
                <div class="flex items-center gap-3">
                    <i class="fa-solid ${iconClass}"></i>
                    <span class="font-bold text-slate-700">Drank ${data.added_amount} ml</span>
                </div>
                <span class="font-mono text-sm text-slate-400 bg-slate-50 px-2 py-1 rounded-md">${data.time || 'Just now'}</span>
            </div>
        `;
        logContainer.insertAdjacentHTML('afterbegin', newLogHTML);
    }
}

/**
 * Checks if reminder should be shown
 */
function checkHydrationReminder() {
    const body = document.querySelector('body');
    const reminderBanner = document.getElementById('reminder-banner');
    const successBanner = document.getElementById('goal-success-banner');
    const viewDateInput = document.getElementById('current-view-date');
    
    /* Hide Reminders in these cases:
       1. Success banner is shown
       2. Viewing a date other than today
    */
    if (successBanner && !successBanner.classList.contains('hidden')) {
        reminderBanner.classList.add('hidden');
        return;
    } else if (viewDateInput) {
        const viewDate = viewDateInput.value;

        const todayDate = new Date();
        const todayStr = todayDate.getFullYear() + '-' + 
                         String(d.getMonth() + 1).padStart(2, '0') + '-' + 
                         String(d.getDate()).padStart(2, '0');
        
        if (viewDate !== todayStr) {
            if (reminderBanner) reminderBanner.classList.add('hidden');
            return;
        }
    }

    /* Show Reminder Logic */
    const freqMinutes = parseInt(body.dataset.reminderFreq) || 60; 
    const lastSipTs = parseInt(body.dataset.lastSip) || 0;
    const now = Date.now();
    const timeSinceSip = now - lastSipTs; 
    const limit = freqMinutes * 60 * 1000;

    // If lastSip is 0 (never drank) OR time difference > limit
    if (lastSipTs === 0 || timeSinceSip > limit) {
        if (reminderBanner.classList.contains('hidden')) {
            console.log("Showing Reminder! Time since sip:", timeSinceSip);
            reminderBanner.classList.remove('hidden');
        }
    } else {
        // Otherwise ensure it's hidden
        reminderBanner.classList.add('hidden');
    }
}

function dismissReminder() {
    const banner = document.getElementById('reminder-banner');
    banner.classList.add('hidden');
    
    // Snooze for 15 mins (fake a sip 45 mins ago)
    const body = document.querySelector('body');
    const freqMinutes = parseInt(body.dataset.reminderFreq) || 60;
    const snoozeTime = 15 * 60 * 1000; 
    
    body.dataset.lastSip = Date.now() - ((freqMinutes * 60 * 1000) - snoozeTime);
    console.log("Reminder Snoozed.");
}

// ... Keep your existing changeDate, fetchDayData, renderDay functions ...
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
        if(data.status === 'success') renderDay(data);
    });
}
function renderDay(data) {
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
            const html = `<div class="flex justify-between items-center bg-white border-2 border-slate-100 px-4 py-3 rounded-2xl shadow-sm hover:border-blue-200 transition-colors"><div class="flex items-center gap-3"><i class="fa-solid ${iconClass}"></i><span class="font-bold text-slate-700">Drank ${log.amount_ml} ml</span></div><span class="font-mono text-sm text-slate-400 bg-slate-50 px-2 py-1 rounded-md">${log.time}</span></div>`;
            container.insertAdjacentHTML('beforeend', html);
        });
    }
}
function openManualModal() {
    document.getElementById('manual-modal').classList.remove('hidden');
    setTimeout(() => document.getElementById('custom-amount').focus(), 100);
}
function closeManualModal() {
    document.getElementById('manual-modal').classList.add('hidden');
}
function setAmount(amount) {
    document.getElementById('custom-amount').value = amount;
}