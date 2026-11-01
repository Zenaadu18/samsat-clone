document.addEventListener('DOMContentLoaded', function() {
        
        // --- LOGIKA TABS ---
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault(); 
                tabBtns.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                this.classList.add('active');
                document.getElementById(this.getAttribute('data-target')).classList.add('active');
            });
        });

        // --- LOGIKA TOGGLE & MASTER SWITCH AMNESTI ---
        const masterSwitchName = 'config[amnesti_aktif]';
        const allSwitches = document.querySelectorAll('.switch input.master-toggle-item');

        function updateVisual(toggle) {
            const label = toggle.closest('.switch-wrapper').querySelector('.switch-label');
            if (toggle.checked) {
                label.textContent = 'AKTIF';
                label.style.color = '#10b981';
            } else {
                label.textContent = 'NON-AKTIF';
                label.style.color = '#475569';
            }
        }

        allSwitches.forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                updateVisual(this);
                
                if (this.name === masterSwitchName && !this.checked) {
                    allSwitches.forEach(function(childToggle) {
                        if (childToggle.name.includes('amnesti_') && childToggle.name !== masterSwitchName) {
                            childToggle.checked = false;
                            updateVisual(childToggle);
                        }
                    });
                }
            });
        });
    });