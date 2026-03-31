/**
 * AT Health — Home Page JS
 * Weight loss calculator with unit conversion.
 */
(function () {
    'use strict';

    var SURMOUNT_PERCENT = 22.5;
    var currentUnit = 'kg';
    var form = document.getElementById('weightLossForm');
    var resultsEl = document.getElementById('calcResults');

    if (!form || !resultsEl) return;

    // Unit toggle
    document.querySelectorAll('.calc-unit-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.calc-unit-btn').forEach(function (b) {
                b.classList.remove('active-unit');
                b.classList.add('text-gray-500');
            });
            btn.classList.add('active-unit');
            btn.classList.remove('text-gray-500');
            currentUnit = btn.getAttribute('data-unit');

            var label = document.getElementById('calcUnitLabel');
            var input = document.getElementById('calcWeight');
            if (currentUnit === 'kg') {
                label.textContent = 'kg';
                input.placeholder = 'e.g. 95';
                input.min = '30'; input.max = '300';
            } else if (currentUnit === 'stone') {
                label.textContent = 'stone';
                input.placeholder = 'e.g. 15';
                input.min = '5'; input.max = '50';
            } else {
                label.textContent = 'lbs';
                input.placeholder = 'e.g. 210';
                input.min = '66'; input.max = '660';
            }
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var rawVal = parseFloat(document.getElementById('calcWeight').value);
        if (isNaN(rawVal) || rawVal <= 0) return;

        // Convert to kg
        var weightKg;
        if (currentUnit === 'kg') weightKg = rawVal;
        else if (currentUnit === 'stone') weightKg = rawVal * 6.35029;
        else weightKg = rawVal * 0.453592;

        var lossKg = weightKg * (SURMOUNT_PERCENT / 100);
        var newWeightKg = weightKg - lossKg;
        var lossStone = lossKg / 6.35029;

        // Show results
        resultsEl.classList.remove('hidden');

        document.getElementById('resLossKg').textContent = lossKg.toFixed(1);
        document.getElementById('resLossStone').textContent = lossStone.toFixed(1);

        if (currentUnit === 'stone') {
            var newStone = newWeightKg / 6.35029;
            document.getElementById('resNewWeight').textContent = newStone.toFixed(1);
            document.getElementById('resNewWeightUnit').textContent = 'stone new weight';
            document.getElementById('resStartLabel').textContent = rawVal.toFixed(1) + ' stone';
            document.getElementById('resGoalLabel').textContent = newStone.toFixed(1) + ' stone';
        } else if (currentUnit === 'lbs') {
            var newLbs = newWeightKg * 2.20462;
            document.getElementById('resNewWeight').textContent = Math.round(newLbs);
            document.getElementById('resNewWeightUnit').textContent = 'lbs new weight';
            document.getElementById('resStartLabel').textContent = Math.round(rawVal) + ' lbs';
            document.getElementById('resGoalLabel').textContent = Math.round(newLbs) + ' lbs';
        } else {
            document.getElementById('resNewWeight').textContent = newWeightKg.toFixed(1);
            document.getElementById('resNewWeightUnit').textContent = 'kg new weight';
            document.getElementById('resStartLabel').textContent = weightKg.toFixed(1) + ' kg';
            document.getElementById('resGoalLabel').textContent = newWeightKg.toFixed(1) + ' kg';
        }

        document.getElementById('resPercent').textContent = '-' + SURMOUNT_PERCENT + '%';

        // Animate bar
        setTimeout(function () {
            document.getElementById('resBar').style.width = SURMOUNT_PERCENT + '%';
        }, 100);

        // Scroll to results
        resultsEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
})();
