(function () {
    'use strict';
    function initializePdfExport() {
        var button = document.querySelector('.pdf[data-url]');
        if (!button) return;
        var busy = false;
        button.addEventListener('click', async function (event) {
            event.preventDefault();
            if (busy) return;
            var yearSelect = document.getElementById('cwis-year-select');
            var year = (yearSelect && yearSelect.value) || button.dataset.year;
            function showError(message) {
                if (window.toastr) {
                    window.toastr.error(message);
                } else {
                    var error = document.createElement('div');
                    error.className = 'alert alert-danger';
                    error.setAttribute('role', 'alert');
                    error.textContent = message;
                    button.parentNode.insertBefore(error, button);
                }
            }
            if (!/^[1-9][0-9]{0,3}$/.test(String(year || ''))) {
                showError('Please select a reporting year before generating the PDF.');
                return;
            }
            var loader = document.getElementById('loader-overlay');
            var csrf = document.querySelector('meta[name="csrf-token"]');
            busy = true;
            button.disabled = true;
            if (loader) loader.style.display = 'flex';
            try {
                var response = await fetch(button.dataset.url, {
                    method: 'POST', credentials: 'same-origin',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf ? csrf.content : ''},
                    body: JSON.stringify({year: Number(year), country_code: button.dataset.country})
                });
                if (!response.ok) {
                    var message = 'Unable to generate the CWIS PDF. Please try again or contact the administrator.';
                    if (response.status === 419 || response.status === 401) {
                        message = 'Your session has expired. Refresh the page and sign in again.';
                    } else if ((response.status === 422 || response.status === 503) &&
                        (response.headers.get('Content-Type') || '').includes('application/json')) {
                        var result = await response.json();
                        var errors = result.errors && Object.values(result.errors).flat();
                        message = errors && errors.length ? errors[0] : (result.message || message);
                    }
                    throw new Error(message);
                }
                if (!(response.headers.get('Content-Type') || '').includes('application/pdf')) {
                    throw new Error('The server did not return a PDF. Refresh the page and try again.');
                }
                var blob = await response.blob();
                var exportedCountry = response.headers.get('X-CWIS-Country');
                if (!['ALL', 'NP', 'BD'].includes(exportedCountry)) exportedCountry = button.dataset.country;
                var url = window.URL.createObjectURL(blob);
                var link = document.createElement('a');
                link.href = url;
                link.download = 'CWIS_Report_' + exportedCountry + '_' + year + '.pdf';
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.setTimeout(function () { window.URL.revokeObjectURL(url); }, 1000);
            } catch (error) {
                showError(error.message || 'Unable to generate the CWIS PDF. Please try again.');
            } finally {
                busy = false;
                button.disabled = false;
                if (loader) loader.style.display = 'none';
            }
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializePdfExport);
    } else {
        initializePdfExport();
    }
}());
