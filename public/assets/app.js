(function () {
    function each(nodes, callback) {
        Array.prototype.forEach.call(nodes, callback);
    }

    function updateSearchForm(form, mode, keepValue) {
        var input = form.querySelector('[data-city-input]');
        var tabs = form.querySelectorAll('[data-search-tab]');
        var label = form.querySelector('[data-search-field-label]');
        var options = form.querySelector('[data-city-options]');
        var cityAction = form.getAttribute('data-city-action') || '/';
        var spotAction = form.getAttribute('data-spot-action') || '/einzelattraktion';
        var isCityMode = mode === 'city';

        if (!input) {
            return;
        }

        form.setAttribute('data-search-mode', isCityMode ? 'city' : 'spot');
        form.setAttribute('action', isCityMode ? cityAction : spotAction);

        input.name = isCityMode ? 'city' : 'q';
        input.placeholder = isCityMode ? 'Stadt oder Erlebnis suchen' : 'Attraktion oder Spot suchen';

        if (!keepValue) {
            input.value = '';
        }

        if (label) {
            label.textContent = isCityMode ? 'Wohin?' : 'Attraktion';
        }

        if (options) {
            options.setAttribute('aria-hidden', 'true');
        }

        each(tabs, function (tab) {
            var isActive = tab.getAttribute('data-search-tab') === (isCityMode ? 'city' : 'spot');

            tab.classList.toggle('is-active', isActive);
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
    }

    each(document.querySelectorAll('[data-city-search]'), function (form) {
        var input = form.querySelector('[data-city-input]');
        var tabs = form.querySelectorAll('[data-search-tab]');
        var initialMode = form.getAttribute('data-search-mode') || 'spot';

        updateSearchForm(form, initialMode, true);

        each(tabs, function (tab) {
            tab.addEventListener('click', function () {
                updateSearchForm(form, tab.getAttribute('data-search-tab'), false);

                if (input) {
                    input.focus();
                }
            });
        });

        form.addEventListener('submit', function () {
            if (input) {
                input.value = input.value.trim();
            }
        });
    });
}());
