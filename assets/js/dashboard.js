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
 * Updates the Dashboard numbers, visuals, and resets the reminder timer
 * Handles both "New Log" events and "Date Change" updates
 */
function updateDashboardUI(data, dailyGoal) {
    const newTotal = parseInt(data.new_total);
    const reminderBanner = document.getElementById('reminder-banner');
    
    // 1. Reset Reminder Timer (Only if water was just added)
    if (data.added_amount !== undefined) {
        const body = document.querySelector('body');
        
        // Update timestamp to NOW
        body.dataset.lastSip = Date.now(); 
        console.log("Timer Reset! New Last Sip:", body.dataset.lastSip);

        // Force hide the banner immediately
        if(reminderBanner) reminderBanner.classList.add('hidden');
    }

    // 2. Update Total Display & Colors
    const display = document.getElementById('total-drunk-display');
    const successBanner = document.getElementById('goal-success-banner');

    if (display) display.innerText = newTotal;

    if (newTotal >= dailyGoal) {
        
        // A. Update Text Color (GREEN)
        if (display) {
            display.parentElement.classList.remove('text-slate-800');
            display.parentElement.classList.add('text-green-500');
        }

        // B. Show Success Banner
        if (successBanner) {
            successBanner.classList.remove('hidden');
            // Restart animation
            successBanner.classList.remove('animate-fade-in');
            void successBanner.offsetWidth; 
            successBanner.classList.add('animate-fade-in');
        }

        // C. Hide Reminder (Since goal is reached)
        if (reminderBanner) reminderBanner.classList.add('hidden');

    } else {
        
        // A. Update Text Color (GRAY)
        if (display) {
            display.parentElement.classList.remove('text-green-500');
            display.parentElement.classList.add('text-slate-800');
        }

        // B. Hide Success Banner
        if (successBanner) successBanner.classList.add('hidden');
    }
    
    // 3. Update Remaining Amount & Bottle Text
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
    
    // 4. Update Header & Progress Bar
    const headerTotal = document.getElementById('header-total-intake');
    if (headerTotal) headerTotal.innerText = newTotal.toLocaleString();

    const headerMsg = document.getElementById('header-status-msg');
    if (headerMsg && data.status_msg) headerMsg.innerText = data.status_msg;

    const fill = document.getElementById('water-level-fill');
    if (fill) {
        let percent = ((dailyGoal - newTotal) / dailyGoal) * 100;
        if (percent < 0) percent = 0; 
        if (percent > 100) percent = 100; 
        fill.style.height = percent + '%';
    }

    // 5. Update Log List (Only if adding a single log)
    const logContainer = document.querySelector('.space-y-3');
    if (logContainer && data.added_amount !== undefined) {
        const emptyState = logContainer.querySelector('.text-center.py-6');
        if (emptyState) emptyState.remove();

        // Use Helper Function to generate HTML
        const newLogHTML = generateLogHTML(
            data.added_amount, 
            data.time || 'Just now', 
            1 // Default sip count for new logs is 1
        );
        logContainer.insertAdjacentHTML('afterbegin', newLogHTML);
    }
}

/**
 * Checks if reminder should be shown based on time and date
 */
function checkHydrationReminder() {
    const body = document.querySelector('body');
    const reminderBanner = document.getElementById('reminder-banner');
    const successBanner = document.getElementById('goal-success-banner');
    const viewDateInput = document.getElementById('current-view-date');
    
    // 1. Hide Reminder if Success Banner is shown
    if (successBanner && !successBanner.classList.contains('hidden')) {
        if (reminderBanner) reminderBanner.classList.add('hidden');
        return;
    } 
    
    // 2. Hide Reminder if viewing a past date
    if (viewDateInput) {
        const viewDate = viewDateInput.value;
        const todayStr = getLocalTodayStr();
        
        if (viewDate !== todayStr) {
            if (reminderBanner) reminderBanner.classList.add('hidden');
            return;
        }
    }

    // 3. Check Time Limit
    const freqMinutes = parseInt(body.dataset.reminderFreq) || 60; 
    const lastSipTs = parseInt(body.dataset.lastSip) || 0;
    const now = Date.now();
    const timeSinceSip = now - lastSipTs; 
    const limit = freqMinutes * 60 * 1000;

    if (lastSipTs === 0 || timeSinceSip > limit) {
        if (reminderBanner && reminderBanner.classList.contains('hidden')) {
            console.log("Showing Reminder! Time since sip:", timeSinceSip);
            reminderBanner.classList.remove('hidden');
        }
    } else {
        if (reminderBanner) reminderBanner.classList.add('hidden');
    }
}

/**
 * Dismisses the reminder and snoozes it for 15 minutes
 */
function dismissReminder() {
    const banner = document.getElementById('reminder-banner');
    if(banner) banner.classList.add('hidden');
    
    // Snooze logic: Fake a sip 45 mins ago (assuming 60 min freq)
    const body = document.querySelector('body');
    const freqMinutes = parseInt(body.dataset.reminderFreq) || 60;
    const snoozeTime = 15 * 60 * 1000; 
    
    body.dataset.lastSip = Date.now() - ((freqMinutes * 60 * 1000) - snoozeTime);
    console.log("Reminder Snoozed.");
}

/**
 * Navigates to a new date based on offset (-1 or +1)
 */
function changeDate(offset) {
    const currentDateInput = document.getElementById('current-view-date');
    
    // 1. Calculate Target Date
    let current = new Date(currentDateInput.value);
    current.setDate(current.getDate() + offset);
    
    // 2. Format to YYYY-MM-DD
    const newDateStr = current.toISOString().split('T')[0];

    // 3. Block Future Dates
    const todayStr = getLocalTodayStr();
    if (newDateStr > todayStr) {
        console.log("Cannot navigate to future: " + newDateStr);
        return; 
    }

    // 4. Fetch Data
    fetchDayData(newDateStr);
}

/**
 * Jumps specifically to Today's date
 */
function jumpToToday() {
    const todayStr = getLocalTodayStr();
    fetchDayData(todayStr);
}

/**
 * Fetch logs and data for a specific day via AJAX
 */
function fetchDayData(dateStr) {
    const logContainer = document.getElementById('log-list-container');
    if(logContainer) logContainer.style.opacity = '0.5';

    fetch(`actions/get_day_data.php?date=${dateStr}`)
    .then(res => res.json())
    .then(data => {
        if(logContainer) logContainer.style.opacity = '1';
        if(data.status === 'success') {
            renderDay(data);
        }
    })
    .catch(err => console.error(err));
}

/**
 * Renders the entire dashboard state based on JSON data
 */
function renderDay(data) {
    // 1. Update Date Inputs & Labels
    document.getElementById('current-view-date').value = data.date;
    document.getElementById('nav-date-display').innerText = data.formatted_date;
    
    const historyLabel = document.getElementById('nav-history-label');
    const nextBtn = document.getElementById('nav-next-btn');
    const returnBanner = document.getElementById('return-to-today-banner');

    if (data.is_today) {
        if(historyLabel) historyLabel.classList.add('hidden');
        if(nextBtn) nextBtn.classList.add('invisible');
        if(returnBanner) returnBanner.classList.add('hidden');
    } else {
        if(historyLabel) historyLabel.classList.remove('hidden');
        if(nextBtn) nextBtn.classList.remove('invisible');
        if(returnBanner) returnBanner.classList.remove('hidden');
    }

    // 2. Update Bottle, Header & Totals (Reuse existing function)
    updateDashboardUI({
        new_total: data.total_intake,
        status_msg: data.status_msg
    }, data.daily_goal);
    
    // 3. Render Log List
    const container = document.getElementById('log-list-container');
    container.innerHTML = ''; 
    
    if (data.logs.length === 0) {
        container.innerHTML = `<div class="text-center py-6 text-slate-400 italic">${data.is_today ? 'No water drank yet today.<br>Take a sip!' : 'No records found for this date.'}</div>`;
    } else {
        let htmlBuilder = '';
        data.logs.forEach(log => {
            // Use Helper Function
            htmlBuilder += generateLogHTML(log.amount_ml, log.time, log.sip_count);
        });
        container.innerHTML = htmlBuilder;
    }
}

/**
 * HELPER: Returns today's date in YYYY-MM-DD format (Local Time)
 */
function getLocalTodayStr() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

/**
 * HELPER: Generates the HTML string for a single log entry
 */
function generateLogHTML(amount, time, sipCount) {
    const iconClass = amount >= 250 ? 'fa-glass-water text-blue-500' : 'fa-droplet text-blue-400';
    const sipBadge = sipCount > 1 ? `<span class="text-blue-500 text-sm ml-1">x${sipCount}</span>` : '';
    
    return `
        <div class="flex justify-between items-center bg-white border-2 border-slate-100 px-4 py-3 rounded-2xl shadow-sm hover:border-blue-200 transition-colors animate-fade-in">
            <div class="flex items-center gap-3">
                <i class="fa-solid ${iconClass}"></i>
                <span class="font-bold text-slate-700">Drank ${amount} ml ${sipBadge}</span>
            </div>
            <span class="font-mono text-sm text-slate-400 bg-slate-50 px-2 py-1 rounded-md">${time}</span>
        </div>`;
}

// --- MODAL UTILS ---
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