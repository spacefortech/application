(function () {
    var cities = window.CITY_GUIDE_DATA || [];
    var form = document.querySelector('[data-city-search]');
    var input = document.querySelector('[data-city-input]');
    var cityOptions = document.querySelector('[data-city-options]');
    var cityStrip = document.querySelector('[data-city-strip]');
    var result = document.querySelector('[data-city-result]');
    var heading = document.querySelector('[data-results-heading]');
    var copy = document.querySelector('[data-results-copy]');
    var miniSearch = document.querySelector('[data-mini-search]');
    var miniInput = document.getElementById('mini-city-input');
    var categoryButtons = document.querySelectorAll('[data-category-filter]');
    var activeCategory = 'all';
    var activeOptionIndex = 0;
    var visibleOptions = [];

    function normalize(value) {
        var replacements = {
            'ä': 'ae',
            'ö': 'oe',
            'ü': 'ue',
            'ß': 'ss'
        };

        return String(value || '')
            .trim()
            .toLowerCase()
            .replace(/[äöüß]/g, function (match) {
                return replacements[match] || match;
            })
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function escapeHtml(value) {
        var node = document.createElement('span');
        node.textContent = value;
        return node.innerHTML;
    }

    function findCity(value) {
        var normalized = normalize(value);

        for (var i = 0; i < cities.length; i += 1) {
            var city = cities[i];
            var names = [city.slug, city.name, city.displayName].concat(city.aliases || []);

            for (var j = 0; j < names.length; j += 1) {
                if (normalize(names[j]) === normalized) {
                    return city;
                }
            }
        }

        return null;
    }

    function getCityOptions(value) {
        var normalized = normalize(value);

        if (!normalized) {
            return cities.slice();
        }

        return cities.filter(function (city) {
            var names = [city.slug, city.name, city.displayName].concat(city.aliases || []);

            return names.some(function (name) {
                return normalize(name).indexOf(normalized) !== -1;
            });
        });
    }

    function activateCity(slug) {
        var activeSlug = slug || '';
        var selectors = ['[data-city-button]', '[data-city-card]'];

        selectors.forEach(function (selector) {
            var nodes = document.querySelectorAll(selector);

            nodes.forEach(function (node) {
                node.classList.toggle('is-active', node.getAttribute('data-city-slug') === activeSlug);
            });
        });
    }

    function updateActiveOption() {
        var buttons = cityOptions.querySelectorAll('[data-city-option]');

        buttons.forEach(function (button, index) {
            var isActive = index === activeOptionIndex;

            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-selected', isActive ? 'true' : 'false');

            if (isActive) {
                input.setAttribute('aria-activedescendant', button.id);
            }
        });
    }

    function renderOptions(value) {
        visibleOptions = getCityOptions(value);
        activeOptionIndex = visibleOptions.length ? Math.max(0, Math.min(activeOptionIndex, visibleOptions.length - 1)) : -1;

        if (!visibleOptions.length) {
            cityOptions.innerHTML = '<div class="city-options-label">Ort</div>' +
                '<div class="city-option-empty">Keine Stadt gefunden</div>';
            input.removeAttribute('aria-activedescendant');
            return;
        }

        cityOptions.innerHTML = '<div class="city-options-label">Ort</div>' +
            visibleOptions.map(function (city, index) {
                return '<button class="city-option' + (index === activeOptionIndex ? ' is-active' : '') + '" id="city-option-' + escapeHtml(city.slug) + '" data-city-option data-city-slug="' + escapeHtml(city.slug) + '" type="button" role="option" aria-selected="' + (index === activeOptionIndex ? 'true' : 'false') + '">' +
                    '<span class="city-option-pin" aria-hidden="true"></span>' +
                    '<span><strong>' + escapeHtml(city.displayName) + '</strong></span>' +
                '</button>';
            }).join('');

        updateActiveOption();
    }

    function openOptions() {
        renderOptions(input.value);
        form.classList.add('is-options-open');
        input.setAttribute('aria-expanded', 'true');
    }

    function closeOptions() {
        form.classList.remove('is-options-open');
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
    }

    function selectOption(city) {
        if (!city) {
            return;
        }

        input.value = city.displayName;
        if (miniInput) {
            miniInput.value = city.displayName;
        }
        renderCity(city);
        closeOptions();
    }

    function renderCityStrip() {
        cityStrip.innerHTML = cities.map(function (city) {
            return '<button class="city-card" data-city-card data-city-slug="' + escapeHtml(city.slug) + '" type="button">' +
                '<strong>' + escapeHtml(city.displayName) + '</strong>' +
                '<span>' + escapeHtml(city.region) + '</span>' +
            '</button>';
        }).join('');

        var cards = cityStrip.querySelectorAll('[data-city-card]');

        cards.forEach(function (card) {
            card.addEventListener('click', function () {
                var city = findCity(card.getAttribute('data-city-slug'));
                if (city) {
                    input.value = city.displayName;
                    renderCity(city);
                    document.getElementById('places').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    function renderCity(city) {
        if (!city) {
            heading.textContent = 'Stadt nicht gefunden';
            copy.textContent = 'Versuche Berlin, Hamburg, M\u00fcnchen, K\u00f6ln, Frankfurt oder Dresden.';
            result.innerHTML = '<div class="empty-state"><strong>Keine Stadt gefunden.</strong><p>Versuche Berlin, Hamburg, M\u00fcnchen, K\u00f6ln, Frankfurt oder Dresden.</p></div>';
            activateCity('');
            return;
        }

        var spots = city.spots.filter(function (spot) {
            return activeCategory === 'all' || spot.type === activeCategory;
        });

        heading.textContent = 'Neue Orte in ' + city.displayName;
        copy.textContent = city.summary;
        activateCity(city.slug);

        var places = spots.map(function (spot) {
            return '<article class="place-card">' +
                '<div class="place-photo">' +
                    '<span class="place-label">' + escapeHtml(spot.type) + '</span>' +
                    '<span class="time-badge">' + escapeHtml(spot.time) + '</span>' +
                '</div>' +
                '<h3>' + escapeHtml(spot.name) + '</h3>' +
                '<p>' + escapeHtml(spot.note) + '</p>' +
                '<div class="place-foot">Bereich: ' + escapeHtml(spot.area) + '</div>' +
            '</article>';
        }).join('');

        if (!places) {
            places = '<div class="empty-state"><strong>Keine Ergebnisse in diesem Filter.</strong><p>W\u00e4hle eine andere Kategorie oder kehre zu Neue Orte zur\u00fcck.</p></div>';
        }

        var route = city.route.map(function (item) {
            return '<article class="route-card">' +
                '<span>' + escapeHtml(item.step) + '</span>' +
                '<div>' +
                    '<h4>' + escapeHtml(item.title) + '</h4>' +
                    '<p>' + escapeHtml(item.detail) + '</p>' +
                '</div>' +
            '</article>';
        }).join('');

        result.innerHTML = '<div class="city-summary">' +
            '<div>' +
                '<h3>' + escapeHtml(city.headline) + '</h3>' +
                '<p>' + escapeHtml(city.bestFor) + '</p>' +
            '</div>' +
            '<span class="summary-badge">' + escapeHtml(city.duration) + '</span>' +
        '</div>' +
        '<div class="places-grid">' + places + '</div>' +
        '<section class="route-panel" aria-label="Empfohlene Route">' +
            '<h3>Ein typischer Tag</h3>' +
            '<div class="route-list">' + route + '</div>' +
        '</section>';

        window.history.replaceState({}, '', '?city=' + encodeURIComponent(city.slug));
    }

    function searchFrom(value, scroll) {
        var city = findCity(value);
        input.value = city ? city.displayName : value;
        if (miniInput) {
            miniInput.value = city ? city.displayName : value;
        }
        renderCity(city);

        if (scroll) {
            document.getElementById('places').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function handleSearch(event, sourceInput) {
        event.preventDefault();
        closeOptions();
        searchFrom(sourceInput.value, true);
    }

    function handleOptionKeys(event) {
        if ((event.key === 'ArrowDown' || event.key === 'ArrowUp') && !form.classList.contains('is-options-open')) {
            openOptions();
        }

        if (!form.classList.contains('is-options-open')) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            activeOptionIndex = Math.min(activeOptionIndex + 1, visibleOptions.length - 1);
            updateActiveOption();
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            activeOptionIndex = Math.max(activeOptionIndex - 1, 0);
            updateActiveOption();
        }

        if (event.key === 'Enter' && visibleOptions.length) {
            event.preventDefault();
            selectOption(visibleOptions[activeOptionIndex]);
        }

        if (event.key === 'Escape') {
            closeOptions();
        }
    }

    function bindCategoryFilters() {
        categoryButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                categoryButtons.forEach(function (node) {
                    node.classList.remove('is-active');
                });
                button.classList.add('is-active');
                activeCategory = button.getAttribute('data-category-filter');
                renderCity(findCity(input.value) || cities[0]);
            });
        });
    }

    function initialize() {
        if (!cities.length) {
            return;
        }

        renderOptions('');
        renderCityStrip();
        bindCategoryFilters();

        var params = new URLSearchParams(window.location.search);
        var initialCity = findCity(params.get('city')) || cities[0];
        input.value = initialCity.displayName;
        if (miniInput) {
            miniInput.value = initialCity.displayName;
        }
        renderCity(initialCity);

        cityOptions.addEventListener('click', function (event) {
            var button = event.target.closest('[data-city-option]');

            if (!button) {
                return;
            }

            selectOption(findCity(button.getAttribute('data-city-slug')));
        });

        input.addEventListener('focus', openOptions);
        input.addEventListener('click', openOptions);
        input.addEventListener('input', function () {
            activeOptionIndex = 0;
            openOptions();
        });
        input.addEventListener('keydown', handleOptionKeys);

        document.addEventListener('click', function (event) {
            if (!form.contains(event.target)) {
                closeOptions();
            }
        });

        form.addEventListener('submit', function (event) {
            handleSearch(event, input);
        });

        if (miniSearch) {
            miniSearch.addEventListener('submit', function (event) {
                handleSearch(event, miniInput);
            });
        }
    }

    initialize();
}());
