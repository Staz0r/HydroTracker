document.addEventListener('DOMContentLoaded', function() {
    
    const bodyElement = document.querySelector('body');
    const dailyGoal = parseInt(bodyElement.dataset.dailyGoal) || 2000;

    const forms = document.querySelectorAll('.ajax-form');

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // STOP RELOAD

            const formData = new FormData(this);
            const action = this.getAttribute('action');

            // Send data via Fetch (AJAX)
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
                    
                    updateDashboardUI(data, dailyGoal);
                    
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

function updateDashboardUI(data, dailyGoal) {
    const newTotal = parseInt(data.new_total);
    
    // 1. Update Numbers
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

        // APPLY Logic
        if (left > 0 && left <= 150) {
            // --- MOTIVATION MODE ---
            leftText.classList.add('text-blue-600');
            if(leftLabel) {
                leftLabel.innerText = "Take the last sip!";
                leftLabel.classList.add('text-blue-500', 'animate-pulse');
            }
        } else {
            // --- STANDARD MODE ---
            leftText.classList.add('text-slate-800');
            if(leftLabel) {
                leftLabel.classList.add('text-slate-600');
            }
        }
    }

    // 2. Color Update (Green if goal reached)
    if (display && newTotal >= dailyGoal) {
        display.parentElement.classList.remove('text-slate-800');
        display.parentElement.classList.add('text-green-500');
    }

    // 3. Update Water Level (Draining Effect)
    const fill = document.getElementById('water-level-fill');
    if (fill) {
        let percent = ((dailyGoal - newTotal) / dailyGoal) * 100;
        if (percent < 0) percent = 0; 
        if (percent > 100) percent = 100; 
        fill.style.height = percent + '%';
    }

    // 4. NEW: Toggle Congratulations Banner
    const banner = document.getElementById('goal-success-banner');
    if (banner) {
        if (newTotal >= dailyGoal) {
            banner.classList.remove('hidden');
            // Little trick to restart animation
            banner.classList.remove('animate-fade-in');
            void banner.offsetWidth; 
            banner.classList.add('animate-fade-in');
        } else {
            banner.classList.add('hidden');
        }
    }
    
    const sipAmount = (left > 0 && left < 100) ? left : 100;
    const gulpAmount = (left > 0 && left < 250) ? left : 250;

    // Update Bottle (Sip) Button
    const sipInput = document.getElementById('sip-input');
    if(sipInput) sipInput.value = sipAmount;
    
    const sipTooltip = document.getElementById('sip-tooltip');
    if(sipTooltip) {
        // Updates the text "Tap to sip (100ml)" -> "Tap to sip (45ml)"
        // We use regex to safely replace just the number part
        sipTooltip.innerHTML = sipTooltip.innerHTML.replace(/\(\d+ml\)/, `(${sipAmount}ml)`);
    }

    // Update Big Gulp Button
    const gulpInput = document.getElementById('gulp-input');
    if(gulpInput) gulpInput.value = gulpAmount;

    const gulpLabel = document.getElementById('gulp-label');
    const gulpSub = document.getElementById('gulp-sublabel');
    
    if(gulpLabel && gulpSub) {
        if(gulpAmount < 250) {
            // Change text to motivate finishing
            gulpLabel.innerText = "Finish It!";
            gulpSub.innerText = `${gulpAmount}ml`;
        } else {
            // Revert to normal
            gulpLabel.innerText = "Big Gulp";
            gulpSub.innerText = "250ml";
        }
    }

    // 5. Update List Logic (Add new row)
    const logContainer = document.querySelector('.space-y-3');
    if (logContainer) {
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
                    ${data.time}
                </span>
            </div>
        `;
        logContainer.insertAdjacentHTML('afterbegin', newLogHTML);
    }
}

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