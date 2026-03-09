(function() {
    'use strict';

    function colorLicenseRows() {
        // Find the license expiry card by its gridstack id
        var card = document.querySelector('[gs-id*="plugin_licenseexpiry_table"]');
        if (!card) return;

        var today = new Date();
        today.setHours(0, 0, 0, 0);

        // Get alert days from data attribute or default to 30
        var alertDays = 30;
        var orangeDate = new Date(today);
        orangeDate.setDate(orangeDate.getDate() + alertDays);

        // Find all rows in the search results table within this card
        var rows = card.querySelectorAll('.search-results tbody tr');
        rows.forEach(function(row) {
            var cells = row.querySelectorAll('td');
            // Look for date cells (format YYYY-MM-DD)
            cells.forEach(function(cell) {
                var text = cell.textContent.trim();
                var match = text.match(/^(\d{4}-\d{2}-\d{2})$/);
                if (match) {
                    var expDate = new Date(match[1] + 'T00:00:00');
                    if (expDate < today) {
                        row.style.backgroundColor = '#ffcdd2';
                        row.style.color = '#b71c1c';
                    } else if (expDate <= orangeDate) {
                        row.style.backgroundColor = '#ffe0b2';
                        row.style.color = '#e65100';
                    } else {
                        row.style.backgroundColor = '#c8e6c9';
                        row.style.color = '#1b5e20';
                    }
                    // Make the date cell bold
                    cell.style.fontWeight = 'bold';
                }
            });
        });
    }

    // Run on page load and on AJAX card refresh
    function init() {
        colorLicenseRows();

        // Observe DOM changes for AJAX-loaded cards
        var observer = new MutationObserver(function(mutations) {
            var shouldColor = false;
            mutations.forEach(function(m) {
                if (m.addedNodes.length > 0) {
                    shouldColor = true;
                }
            });
            if (shouldColor) {
                setTimeout(colorLicenseRows, 200);
            }
        });

        var dashboard = document.querySelector('.grid-stack');
        if (dashboard) {
            observer.observe(dashboard, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
