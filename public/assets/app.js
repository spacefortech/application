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
    var searchTabs = form ? form.querySelectorAll('[data-search-tab]') : [];
    var searchFieldLabel = form ? form.querySelector('[data-search-field-label]') : null;
    var activeSearchMode = form ? (form.getAttribute('data-search-mode') || 'spot') : 'spot';
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

    function setSearchCity(city) {
        input.value = city ? city.displayName : '';
        if (miniInput) {
            miniInput.value = city ? city.displayName : '';
        }
    }

    function setSearchCopy() {
        var isCityMode = activeSearchMode === 'city';

        if (searchFieldLabel) {
            searchFieldLabel.textContent = isCityMode ? 'Wohin?' : 'Attraktion';
        }

        input.name = isCityMode ? 'city' : 'q';
        input.placeholder = isCityMode ? 'Stadt oder Erlebnis suchen' : 'Attraktion oder Spot suchen';
        cityOptions.setAttribute('aria-label', isCityMode ? 'Städte' : 'Einzelattraktionen');
    }

    function setSearchMode(mode, keepValue) {
        activeSearchMode = mode === 'city' ? 'city' : 'spot';
        form.setAttribute('data-search-mode', activeSearchMode);

        searchTabs.forEach(function (tab) {
            var isActive = tab.getAttribute('data-search-tab') === activeSearchMode;
            tab.classList.toggle('is-active', isActive);
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        if (!keepValue) {
            input.value = '';
        }

        setSearchCopy();
        activeOptionIndex = 0;

        if (form.classList.contains('is-options-open')) {
            renderOptions(input.value);
        }
    }

    function getSpritePosition(city, fallbackIndex) {
        var imageIndex = typeof city.imageIndex === 'number' ? city.imageIndex : fallbackIndex;
        var column = imageIndex % 4;
        var row = Math.floor(imageIndex / 4);

        return {
            x: column === 3 ? 100 : column * 33.3333,
            y: row === 3 ? 100 : row * 33.3333
        };
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
            return cities.slice().map(function (city) {
                return {
                    mode: 'city',
                    city: city
                };
            });
        }

        return cities.filter(function (city) {
            var names = [city.slug, city.name, city.displayName].concat(city.aliases || []);

            return names.some(function (name) {
                return normalize(name).indexOf(normalized) !== -1;
            });
        }).map(function (city) {
            return {
                mode: 'city',
                city: city
            };
        });
    }

    function getSpotOptions(value) {
        var normalized = normalize(value);
        var items = [];

        cities.forEach(function (city, cityIndex) {
            var spots = Array.isArray(city.spots) ? city.spots : [];

            spots.forEach(function (spot, spotIndex) {
                var searchable = [
                    city.slug,
                    city.name,
                    city.displayName,
                    city.region,
                    spot.type,
                    spot.name,
                    spot.area,
                    spot.note,
                    spot.time
                ];

                if (!normalized || searchable.some(function (value) {
                    return normalize(value).indexOf(normalized) !== -1;
                })) {
                    items.push({
                        mode: 'spot',
                        city: city,
                        cityIndex: cityIndex,
                        spot: spot,
                        spotIndex: spotIndex
                    });
                }
            });
        });

        return items.slice(0, 12);
    }

    function getSearchOptions(value) {
        return activeSearchMode === 'city' ? getCityOptions(value) : getSpotOptions(value);
    }

    function activateCity(slug) {
        var activeSlug = slug || '';
        var selectors = ['[data-city-button]', '[data-city-card]', '[data-destination-card]', '[data-home-experience-card]'];

        selectors.forEach(function (selector) {
            var nodes = document.querySelectorAll(selector);

            nodes.forEach(function (node) {
                node.classList.toggle('is-active', node.getAttribute('data-city-slug') === activeSlug);
            });
        });
    }

    function updateActiveOption() {
        var buttons = cityOptions.querySelectorAll('[data-search-option]');

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
        var isCityMode = activeSearchMode === 'city';
        visibleOptions = getSearchOptions(value);
        activeOptionIndex = visibleOptions.length ? Math.max(0, Math.min(activeOptionIndex, visibleOptions.length - 1)) : -1;

        if (!visibleOptions.length) {
            cityOptions.innerHTML = '<div class="city-options-label">' + (isCityMode ? 'Ort' : 'Einzelattraktion') + '</div>' +
                '<div class="city-option-empty">' + (isCityMode ? 'Keine Stadt gefunden' : 'Keine Einzelattraktion gefunden') + '</div>';
            input.removeAttribute('aria-activedescendant');
            return;
        }

        cityOptions.innerHTML = '<div class="city-options-label">' + (isCityMode ? 'Ort' : 'Einzelattraktion') + '</div>' +
            visibleOptions.map(function (option, index) {
                var city = option.city;
                var spot = option.spot || {};
                var id = 'search-option-' + index + '-' + escapeHtml(city.slug);
                var meta = isCityMode
                    ? [city.region, Array.isArray(city.spots) ? (city.spots.length + ' Spots') : null].filter(Boolean).join(' · ')
                    : [city.displayName, spot.type, spot.area].filter(Boolean).join(' · ');
                var title = isCityMode ? city.displayName : (spot.name || city.displayName);

                return '<button class="city-option' + (isCityMode ? ' is-city-option' : ' is-spot-option') + (index === activeOptionIndex ? ' is-active' : '') + '" id="' + id + '" data-search-option data-option-index="' + index + '" type="button" role="option" aria-selected="' + (index === activeOptionIndex ? 'true' : 'false') + '">' +
                    '<span class="city-option-pin" aria-hidden="true"></span>' +
                    '<span class="city-option-copy"><strong>' + escapeHtml(title) + '</strong>' +
                    (meta ? '<small>' + escapeHtml(meta) + '</small>' : '') +
                    '</span>' +
                '</button>';
            }).join('');

        updateActiveOption();
    }

    function openOptions() {
        renderOptions(input.value);
        form.classList.add('is-options-open');
        document.body.classList.add('is-city-search-open');
        input.setAttribute('aria-expanded', 'true');
    }

    function closeOptions() {
        form.classList.remove('is-options-open');
        document.body.classList.remove('is-city-search-open');
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
    }

    function getAttractionSearchHref(value, city) {
        var params = new URLSearchParams();

        if (city && city.slug) {
            params.set('city', city.slug);
        }

        if (normalize(value)) {
            params.set('q', value);
        }

        return '/einzelattraktion' + (params.toString() ? ('?' + params.toString()) : '');
    }

    function selectOption(option) {
        if (!option) {
            return;
        }

        if (option.mode === 'spot') {
            input.value = option.spot && option.spot.name ? option.spot.name : '';
            closeOptions();
            window.location.href = getAttractionSearchHref(input.value, option.city);
            return;
        }

        setSearchCity(option.city);
        renderCity(option.city);
        closeOptions();
    }

    function renderCityStrip() {
        var visibleCities = cities.slice(0, 8);

        cityStrip.innerHTML = visibleCities.map(function (city, index) {
            var position = getSpritePosition(city, 0);
            var spotsCount = Array.isArray(city.spots) ? city.spots.length : (typeof city.spotsCount === 'number' ? city.spotsCount : null);
            var meta = [city.region, spotsCount !== null ? (spotsCount + ' Spots') : null].filter(Boolean).join(' · ');
            var tags = [
                city.duration || null,
                Array.isArray(city.neighborhoods) && city.neighborhoods.length ? city.neighborhoods[0] : null
            ].filter(Boolean);
            var cardClass = 'city-card' + (index === 0 || index === 5 || index === 12 ? ' is-featured' : '');

            return '<button class="' + cardClass + '" data-city-card data-city-slug="' + escapeHtml(city.slug) + '" type="button">' +
                '<span class="city-tile-media" aria-hidden="true" style="--sprite-x: ' + position.x + '%; --sprite-y: ' + position.y + '%; --city-accent: ' + escapeHtml(city.accent || '#ff7a1a') + ';"></span>' +
                '<span class="city-tile-content">' +
                    '<span class="city-tile-kicker">Urlaubsregion</span>' +
                    '<strong>' + escapeHtml(city.displayName) + '</strong>' +
                    '<span class="city-tile-meta">' + escapeHtml(meta) + '</span>' +
                    '<span class="city-tile-copy">' + escapeHtml(city.headline || city.bestFor || city.summary || '') + '</span>' +
                    '<span class="city-tile-tags">' + tags.map(function (tag) {
                        return '<span>' + escapeHtml(tag) + '</span>';
                    }).join('') + '</span>' +
                '</span>' +
            '</button>';
        }).join('');

        var cards = cityStrip.querySelectorAll('[data-city-card]');

        cards.forEach(function (card) {
            card.addEventListener('click', function () {
                var city = findCity(card.getAttribute('data-city-slug'));
                if (city) {
                    if (city.slug === 'duisburg') {
                        window.location.href = '/cool-places?city=duisburg';
                        return;
                    }

                    setSearchMode('city', false);
                    setSearchCity(city);
                    renderCity(city);
                    
                    var targetCard = result.querySelector('[data-home-experience-card][data-city-slug="' + city.slug + '"], [data-destination-card][data-city-slug="' + city.slug + '"]');
                    if (targetCard) {
                        targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        });
    }

    function getExperienceMeta(city, index) {
        var spots = city.spots || [];
        var spot = spots[index % Math.max(spots.length, 1)] || spots[0] || {};
        var labels = ['Highlight', 'Besonders gefragt', 'Bald ausverkauft', 'Neu'];
        var categories = ['Attraktionen und Führungen', 'Aktivitäten', 'Ausflüge und Tagestouren', 'Tickets und Events', 'Museen'];
        var ratings = ['4,69/5', '4,56/5', '4,72/5', '4,51/5', '4,84/5', '4,47/5'];
        var reviews = ['94', '20', '235', '141', '69', '31', '612'];
        var prices = ['0 €', '11,90 €', '16,50 €', '22,50 €', '28,50 €', '33,80 €'];
        var perk = index % 2 === 0 ? 'kostenlose Planung' : 'Digitale Route';

        return {
            badge: labels[index % labels.length],
            category: spot.type || categories[index % categories.length],
            title: city.displayName + ' - ' + (spot.name || city.region),
            description: spot.note || city.bestFor || city.summary,
            rating: ratings[index % ratings.length],
            reviews: reviews[index % reviews.length],
            price: prices[index % prices.length],
            perks: [perk, city.duration || 'Wochenende', 'Sofort verfügbar']
        };
    }

    function getSpotItems(sourceCities, limit) {
        var items = [];

        sourceCities.forEach(function (city, cityIndex) {
            var spots = Array.isArray(city.spots) ? city.spots : [];

            spots.forEach(function (spot, spotIndex) {
                items.push({
                    city: city,
                    cityIndex: cityIndex,
                    spot: spot,
                    spotIndex: spotIndex
                });
            });
        });

        return items.slice(0, limit);
    }

    function getPackageHref(city) {
        return '/highlights-paket?city=' + encodeURIComponent(city.slug);
    }

    function renderHomePackageCard(city, index, activeSlug, featured) {
        var position = getSpritePosition(city, index);
        var isActive = city.slug === activeSlug;
        var spotsCount = Array.isArray(city.spots) ? city.spots.length : 0;
        var title = city.displayName + ' Highlights-Paket';
        var details = [
            city.duration || 'Wochenende',
            spotsCount ? (spotsCount + ' Stopps') : null,
            city.region || null
        ].filter(Boolean);

        return '<a class="home-experience-card is-package' + (featured ? ' is-featured' : '') + (isActive ? ' is-active' : '') + '" href="' + getPackageHref(city) + '" data-home-experience-card data-city-slug="' + escapeHtml(city.slug) + '" aria-label="' + escapeHtml(title) + ' ansehen" style="--sprite-x: ' + position.x + '%; --sprite-y: ' + position.y + '%; --city-accent: ' + escapeHtml(city.accent || '#ff7a1a') + ';">' +
            '<span class="home-card-media" aria-hidden="true"></span>' +
            '<span class="home-card-body">' +
                '<span class="home-card-label">Highlights-Paket</span>' +
                '<strong>' + escapeHtml(title) + '</strong>' +
                '<span class="home-card-copy">' + escapeHtml(city.summary || city.headline || city.bestFor || '') + '</span>' +
                '<span class="home-card-details">' + details.map(function (detail) {
                    return '<span>' + escapeHtml(detail) + '</span>';
                }).join('') + '</span>' +
                '<span class="home-card-action">Zum Paket</span>' +
            '</span>' +
        '</a>';
    }

    function renderHomeSpotCard(item, index, activeSlug) {
        var city = item.city;
        var spot = item.spot || {};
        var position = getSpritePosition(city, item.cityIndex + index);
        var isActive = city.slug === activeSlug;
        var meta = [city.displayName, spot.area].filter(Boolean).join(' · ');
        var details = [spot.type, spot.time].filter(Boolean);

        return '<a class="home-experience-card is-spot' + (isActive ? ' is-active' : '') + '" href="/einzelattraktion?city=' + encodeURIComponent(city.slug) + '" data-home-experience-card data-city-slug="' + escapeHtml(city.slug) + '" aria-label="' + escapeHtml(spot.name || city.displayName) + ' ansehen" style="--sprite-x: ' + position.x + '%; --sprite-y: ' + position.y + '%; --city-accent: ' + escapeHtml(city.accent || '#ff7a1a') + ';">' +
            '<span class="home-spot-media">' +
                '<span class="home-spot-thumb" aria-hidden="true"></span>' +
                '<span class="home-spot-overlay">' +
                    '<span class="home-card-label">Einzelattraktion</span>' +
                    '<span class="home-spot-city">' + escapeHtml(meta) + '</span>' +
                '</span>' +
            '</span>' +
            '<span class="home-card-body">' +
                '<strong>' + escapeHtml(spot.name || city.displayName) + '</strong>' +
                '<span class="home-card-copy">' + escapeHtml(spot.note || city.summary || '') + '</span>' +
                '<span class="home-card-details">' + details.map(function (detail) {
                    return '<span>' + escapeHtml(detail) + '</span>';
                }).join('') + '</span>' +
                '<span class="home-card-action">Spot ansehen</span>' +
            '</span>' +
        '</a>';
    }

    function renderHighlightPackageCard(city, index) {
        var position = getSpritePosition(city, index);
        var spotsCount = Array.isArray(city.spots) ? city.spots.length : 0;
        var title = city.displayName + ' Highlights-Paket';
        var duration = city.duration || 'Wochenende';
        var summary = city.summary || city.headline || city.bestFor || 'Kuratierte Stopps, klare Reihenfolge und starke Orte für deinen Trip.';

        return '<a class="cool-place-card spot-card highlight-package-card has-photo" href="' + getPackageHref(city) + '" data-city-slug="' + escapeHtml(city.slug) + '" aria-label="' + escapeHtml(title) + ' ansehen" style="--sprite-x: ' + position.x + '%; --sprite-y: ' + position.y + '%; --city-accent: ' + escapeHtml(city.accent || '#ff7a1a') + ';">' +
            '<div class="cool-place-photo">' +
                '<span class="spot-city-pill">' + escapeHtml(city.displayName) + '</span>' +
            '</div>' +
            '<div class="cool-place-card-body">' +
                '<div class="cool-place-meta">' +
                    '<span>Highlights-Paket</span>' +
                    '<span>' + escapeHtml(spotsCount ? (spotsCount + ' Stopps') : duration) + '</span>' +
                '</div>' +
                '<h3>' + escapeHtml(title) + '</h3>' +
                '<p>' + escapeHtml(summary) + '</p>' +
                '<div class="cool-place-foot">' +
                    '<span>' + escapeHtml(duration) + '</span>' +
                    '<small>' + escapeHtml(city.region || 'Kuratierte Route') + '</small>' +
                '</div>' +
            '</div>' +
        '</a>';
    }

    function renderHighlightPackageGrid(activeCity, notice) {
        var packageCards = cities.slice(0, 20).map(function (city, index) {
            return renderHighlightPackageCard(city, index);
        }).join('');

        activateCity(activeCity ? activeCity.slug : '');
        result.innerHTML = (notice ? '<div class="empty-state"><strong>Keine passende Stadt gefunden.</strong><p>Wähle eine der kuratierten Städte oder suche nach einer anderen Schreibweise.</p></div>' : '') +
            '<section class="cool-place-grid spot-grid highlight-package-grid" aria-label="Highlights-Pakete">' + packageCards + '</section>';
    }

    function renderHomeMixedGrid(activeCity, notice) {
        var activeSlug = activeCity ? activeCity.slug : '';
        var packageCities = activeCity ? [activeCity] : cities.slice(0, 8);
        var spotCities = activeCity ? [activeCity] : cities;
        var spotItems = getSpotItems(spotCities, activeCity ? 9 : 12);
        var packageCards = [];
        var spotCards = [];

        packageCities.forEach(function (city, index) {
            packageCards.push(renderHomePackageCard(city, index, activeSlug, false));
        });

        spotItems.forEach(function (item, index) {
            spotCards.push(renderHomeSpotCard(item, index, activeSlug));
        });

        var packagesHref = activeCity ? getPackageHref(activeCity) : '/highlights-paket';
        var spotsHref = activeCity ? ('/einzelattraktion?city=' + encodeURIComponent(activeSlug)) : '/einzelattraktion';

        if (heading) {
            heading.textContent = notice ? 'Stadt nicht gefunden' : (activeCity ? (activeCity.displayName + ' als Kurztrip') : 'Dein Kurztrip-Mix');
        }
        if (copy) {
            copy.textContent = notice || (activeCity
                ? 'Starte mit einem Highlights‑Paket und ergänze es mit passenden Einzelattraktionen.'
                : 'Highlights‑Pakete für den ganzen Tag – plus Einzelattraktionen für spontane Stopps.');
        }
        activateCity(activeSlug);

        result.innerHTML = (notice ? '<div class="empty-state"><strong>Keine passende Stadt gefunden.</strong><p>Wähle eine der kuratierten Städte unten oder suche nach einer anderen Schreibweise.</p></div>' : '') +
            '<div class="home-results">' +
                '<section class="home-results-section is-packages" aria-label="Highlights-Pakete">' +
                    '<header class="home-results-section-header">' +
                        '<div class="home-results-section-title">' +
                            '<h3>Highlights‑Paket</h3>' +
                            '<p>Ein kompletter Tagesplan als Paket – ideal als Basis für deinen Trip.</p>' +
                        '</div>' +
                        '<a class="home-results-link" href="' + packagesHref + '">Alle Pakete</a>' +
                    '</header>' +
                    '<div class="home-package-grid">' + packageCards.join('') + '</div>' +
                '</section>' +
                '<section class="home-results-section is-spots" aria-label="Einzelattraktionen">' +
                    '<header class="home-results-section-header">' +
                        '<div class="home-results-section-title">' +
                            '<h3>Einzelattraktionen</h3>' +
                            '<p>Einzelne Orte, die du spontan hinzufügen kannst – kurz, klar, direkt umsetzbar.</p>' +
                        '</div>' +
                        '<a class="home-results-link" href="' + spotsHref + '">Alle Spots</a>' +
                    '</header>' +
                    '<div class="home-spots-grid">' + spotCards.join('') + '</div>' +
                '</section>' +
            '</div>';

        bindDestinationCards();
    }

    function renderDestinationCard(city, index, activeSlug) {
        var position = getSpritePosition(city, index);
        var isActive = city.slug === activeSlug;
        var href = city.slug === 'duisburg' ? '/cool-places?city=duisburg' : '?city=' + encodeURIComponent(city.slug);
        var meta = getExperienceMeta(city, index);

        return '<a class="destination-card' + (isActive ? ' is-active' : '') + '" href="' + href + '" data-destination-card data-city-slug="' + escapeHtml(city.slug) + '" aria-label="City Guide ' + escapeHtml(city.displayName) + ' ansehen" style="--sprite-x: ' + position.x + '%; --sprite-y: ' + position.y + '%; --city-accent: ' + escapeHtml(city.accent || '#ff7a1a') + ';">' +
            '<span class="destination-media">' +
                '<span class="destination-photo" aria-hidden="true"></span>' +
                '<span class="destination-shade" aria-hidden="true"></span>' +
                '<span class="destination-top">' +
                    '<span class="experience-badge">' + escapeHtml(meta.badge) + '</span>' +
                '</span>' +
            '</span>' +
            '<span class="destination-content">' +
                '<span class="destination-copy">' +
                    '<span class="destination-category">' + escapeHtml(meta.category) + '</span>' +
                    '<strong>' + escapeHtml(meta.title) + '</strong>' +
                    '<span class="destination-description">' + escapeHtml(meta.description) + '</span>' +
                    '<span class="destination-perks">' + meta.perks.map(function (perk) {
                        return '<span>' + escapeHtml(perk) + '</span>';
                    }).join('') + '</span>' +
                    '<span class="destination-rating"><strong>' + escapeHtml(meta.rating) + '</strong><span>(' + escapeHtml(meta.reviews) + ')</span></span>' +
                    '<span class="destination-price"><small>ab:</small><strong>' + escapeHtml(meta.price) + '</strong></span>' +
                '</span>' +
            '</span>' +
        '</a>';
    }

    function bindDestinationCards() {
        var destinationCards = result.querySelectorAll('[data-destination-card]');

        destinationCards.forEach(function (card) {
            card.addEventListener('click', function (event) {
                var city = findCity(card.getAttribute('data-city-slug'));

                if (!city) {
                    return;
                }

                if (city.slug === 'duisburg') {
                    return;
                }

                event.preventDefault();
                setSearchCity(city);
                renderCity(city);
            });
        });
    }

    function renderDestinationGrid(activeCity, notice) {
        if (document.querySelector('.home-page')) {
            renderHomeMixedGrid(activeCity, notice);
            return;
        }

        if (document.querySelector('.highlights-page')) {
            renderHighlightPackageGrid(activeCity, notice);
            return;
        }

        var activeSlug = activeCity ? activeCity.slug : '';
        var destinationCards = cities.slice(0, 20).map(function (city, index) {
            return renderDestinationCard(city, index, activeSlug);
        }).join('');

        if (heading) {
            heading.textContent = notice ? 'Stadt nicht gefunden' : 'Jetzt angesagt';
        }
        if (copy) {
            copy.textContent = notice || 'Beliebte City-Erlebnisse mit klaren Routen, starken Motiven und genug Inspiration für dein nächstes Wochenende.';
        }
        activateCity(activeSlug);
        result.innerHTML = (notice ? '<div class="empty-state"><strong>Keine passende Stadt gefunden.</strong><p>Wähle eine der kuratierten Städte unten oder suche nach einer anderen Schreibweise.</p></div>' : '') +
            '<div class="destinations-grid">' + destinationCards + '</div>';
        bindDestinationCards();
    }

    function renderCity(city) {
        renderDestinationGrid(city || null, '');

        if (city) {
            window.history.replaceState({}, '', '?city=' + encodeURIComponent(city.slug));
            return;
        }

        window.history.replaceState({}, '', window.location.pathname);
    }

    function searchFrom(value, scroll) {
        var normalized = normalize(value);
        var city = findCity(value);

        if (!normalized) {
            setSearchCity(null);
            renderCity(null);
        } else if (city) {
            setSearchCity(city);
            renderCity(city);
        } else {
            input.value = value;
            if (miniInput) {
                miniInput.value = value;
            }
            renderDestinationGrid(null, 'Versuche Berlin, Hamburg, München, Köln, Frankfurt, Dresden, Leipzig, Stuttgart, Nürnberg, Heidelberg, Bremen, Düsseldorf, Duisburg, Bonn, Münster, Rostock, Aachen, Trier, Lübeck oder Freiburg.');
        }

        if (scroll) {
            var targetCard = city ? result.querySelector('[data-destination-card][data-city-slug="' + city.slug + '"]') : null;
            if (targetCard) {
                targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                document.getElementById('places').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }

    function handleSearch(event, sourceInput) {
        event.preventDefault();
        closeOptions();

        if (activeSearchMode === 'spot') {
            window.location.href = getAttractionSearchHref(sourceInput.value, null);
            return;
        }

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
                renderCity(findCity(input.value));
            });
        });
    }

    function initialize() {
        if (!cities.length || !form || !input || !cityOptions) {
            return;
        }

        var params = new URLSearchParams(window.location.search);
        var initialCity = findCity(params.get('city'));

        setSearchMode(initialCity ? 'city' : activeSearchMode, true);
        renderOptions('');
        renderCityStrip();
        bindCategoryFilters();

        if (initialCity) {
            setSearchCity(initialCity);
        } else {
            setSearchCity(null);
        }

        renderCity(initialCity);

        searchTabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                setSearchMode(tab.getAttribute('data-search-tab'), false);
                input.focus();
            });
        });

        cityOptions.addEventListener('click', function (event) {
            var button = event.target.closest('[data-search-option]');

            if (!button) {
                return;
            }

            selectOption(visibleOptions[parseInt(button.getAttribute('data-option-index') || '0', 10)]);
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

(function () {
    var data = window.COOL_PLACES_DATA || null;

    if (!data || !data.cities || !data.cities.length) {
        return;
    }

    var cities = data.cities || [];
    var placesByCity = data.places || {};
    var cityRail = document.querySelector('[data-cool-city-rail]');
    var showcase = document.querySelector('[data-cool-showcase]');
    var feedback = document.querySelector('[data-cool-feedback]');
    var activeSlug = data.featuredSlug || cities[0].slug;

    if (!cityRail || !showcase) {
        return;
    }

    function escapeHtml(value) {
        var node = document.createElement('span');
        node.textContent = value;
        return node.innerHTML;
    }

    function getSpritePosition(city, fallbackIndex) {
        var imageIndex = typeof city.imageIndex === 'number' ? city.imageIndex : fallbackIndex;
        var column = imageIndex % 4;
        var row = Math.floor(imageIndex / 4);

        return {
            x: column === 3 ? 100 : column * 33.3333,
            y: row === 3 ? 100 : row * 33.3333
        };
    }

    function visualClass(value) {
        return 'cool-photo-' + String(value || 'city').toLowerCase().replace(/[^a-z0-9-]+/g, '-');
    }

    function findCity(slug) {
        for (var i = 0; i < cities.length; i += 1) {
            if (cities[i].slug === slug) {
                return cities[i];
            }
        }

        return cities[0];
    }

    function renderMoods(detail) {
        return (detail.moods || []).map(function (mood) {
            return '<span>' + escapeHtml(mood) + '</span>';
        }).join('');
    }

    function renderStats(detail) {
        return (detail.stats || []).map(function (stat) {
            return '<span><strong>' + escapeHtml(stat.value) + '</strong><small>' + escapeHtml(stat.label) + '</small></span>';
        }).join('');
    }

    function renderCityRail() {
        cityRail.innerHTML = cities.map(function (city, index) {
            var position = getSpritePosition(city, index);
            var isActive = city.slug === activeSlug;
            var isAvailable = !!city.available;

            return '<button class="cool-city-button' + (isActive ? ' is-active' : '') + (isAvailable ? '' : ' is-unavailable') + '" type="button" data-cool-city="' + escapeHtml(city.slug) + '" style="--sprite-x: ' + position.x + '%; --sprite-y: ' + position.y + '%; --city-accent: ' + escapeHtml(city.accent || '#ff7a1a') + ';">' +
                '<span class="cool-city-thumb" aria-hidden="true"></span>' +
                '<span class="cool-city-button-copy">' +
                    '<strong>' + escapeHtml(city.displayName) + '</strong>' +
                    '<small>' + escapeHtml(city.region) + '</small>' +
                '</span>' +
                '<span class="cool-city-status">' + (isAvailable ? 'Live' : 'Bald') + '</span>' +
            '</button>';
        }).join('');

        cityRail.querySelectorAll('[data-cool-city]').forEach(function (button) {
            button.addEventListener('click', function () {
                activeSlug = button.getAttribute('data-cool-city');
                render();
            });
        });
    }

    function renderPhotoStrip(detail) {
        return '<div class="cool-photo-strip" aria-label="Duisburg Motive">' +
            (detail.places || []).slice(1, 6).map(function (place) {
                return '<div class="cool-photo-tile ' + visualClass(place.visual) + '">' +
                    '<span>' + escapeHtml(place.type) + '</span>' +
                    '<strong>' + escapeHtml(place.name) + '</strong>' +
                '</div>';
            }).join('') +
        '</div>';
    }

    function renderPlaceCard(place, index) {
        var order = index + 1;
        var orderLabel = order < 10 ? '0' + order : String(order);

        return '<article class="cool-place-card">' +
            '<div class="cool-place-photo ' + visualClass(place.visual) + '">' +
                '<span>' + orderLabel + '</span>' +
            '</div>' +
            '<div class="cool-place-card-body">' +
                '<div class="cool-place-meta">' +
                    '<span>' + escapeHtml(place.type) + '</span>' +
                    '<span>' + escapeHtml(place.time) + '</span>' +
                '</div>' +
                '<h3>' + escapeHtml(place.name) + '</h3>' +
                '<p>' + escapeHtml(place.note) + '</p>' +
                '<div class="cool-place-foot">' +
                    '<span>' + escapeHtml(place.area) + '</span>' +
                    '<small>' + escapeHtml(place.tip) + '</small>' +
                '</div>' +
            '</div>' +
        '</article>';
    }

    function renderAvailableCity(city, detail) {
        var featuredPlace = (detail.places || [])[0] || {};
        var places = detail.places || [];

        showcase.innerHTML =
            '<div class="cool-city-feature" style="--city-accent: ' + escapeHtml(city.accent || detail.accent || '#ff7a1a') + ';">' +
                '<div class="cool-city-feature-copy">' +
                    '<span class="section-kicker">' + escapeHtml(detail.kicker || city.displayName) + '</span>' +
                    '<h2>' + escapeHtml(detail.headline) + '</h2>' +
                    '<p>' + escapeHtml(detail.summary) + '</p>' +
                    '<div class="cool-mood-row">' + renderMoods(detail) + '</div>' +
                    '<div class="cool-stat-row">' + renderStats(detail) + '</div>' +
                '</div>' +
                '<div class="cool-feature-photo ' + visualClass(featuredPlace.visual) + '">' +
                    '<span>' + escapeHtml(featuredPlace.type || 'Highlight') + '</span>' +
                    '<strong>' + escapeHtml(featuredPlace.name || city.displayName) + '</strong>' +
                    '<small>' + escapeHtml(featuredPlace.area || city.region) + '</small>' +
                '</div>' +
            '</div>' +
            renderPhotoStrip(detail) +
            '<div class="cool-place-grid">' + places.map(renderPlaceCard).join('') + '</div>';
    }

    function renderComingSoon(city) {
        var placeholders = [1, 2, 3, 4].map(function (item) {
            return '<article class="cool-place-card is-placeholder">' +
                '<div class="cool-place-photo"></div>' +
                '<div class="cool-place-card-body">' +
                    '<div class="cool-place-meta"><span>Coming soon</span><span>City trip</span></div>' +
                    '<h3>' + escapeHtml(city.displayName) + ' Spot ' + item + '</h3>' +
                    '<p>Die Orte für diese Stadt folgen bald.</p>' +
                '</div>' +
            '</article>';
        }).join('');

        showcase.innerHTML =
            '<div class="cool-city-feature is-coming-soon" style="--city-accent: ' + escapeHtml(city.accent || '#ff7a1a') + ';">' +
                '<div class="cool-city-feature-copy">' +
                    '<span class="section-kicker">' + escapeHtml(city.region) + '</span>' +
                    '<h2>' + escapeHtml(city.displayName) + ' ist vorbereitet.</h2>' +
                    '<p>Die Orte für diese Stadt folgen bald. Duisburg ist schon als erster City-Guide befüllt.</p>' +
                    '<div class="cool-mood-row"><span>Orte folgen</span><span>Guide folgt</span><span>Feedbacks folgen</span></div>' +
                '</div>' +
                '<div class="cool-feature-photo">' +
                    '<span>Bald</span>' +
                    '<strong>' + escapeHtml(city.displayName) + '</strong>' +
                    '<small>' + escapeHtml(city.region) + '</small>' +
                '</div>' +
            '</div>' +
            '<div class="cool-place-grid">' + placeholders + '</div>';
    }

    function renderFeedback(city, detail) {
        if (!feedback) {
            return;
        }

        if (!detail || !detail.feedbacks || !detail.feedbacks.length) {
            feedback.innerHTML = '<div class="feedback-empty">Feedbacks für ' + escapeHtml(city.displayName) + ' folgen bald.</div>';
            return;
        }

        feedback.innerHTML = '<div class="feedback-grid">' +
            detail.feedbacks.map(function (item) {
                return '<article class="feedback-card">' +
                    '<p>' + escapeHtml(item.quote) + '</p>' +
                    '<div>' +
                        '<strong>' + escapeHtml(item.name) + '</strong>' +
                        '<span>' + escapeHtml(item.trip) + '</span>' +
                    '</div>' +
                '</article>';
            }).join('') +
        '</div>';
    }

    function render() {
        var city = findCity(activeSlug);
        var detail = placesByCity[city.slug] || null;

        activeSlug = city.slug;
        renderCityRail();

        if (detail) {
            renderAvailableCity(city, detail);
        } else {
            renderComingSoon(city);
        }

        renderFeedback(city, detail);

        if (window.history && window.history.replaceState) {
            window.history.replaceState({}, '', window.location.pathname + '?city=' + encodeURIComponent(city.slug));
        }
    }

    var params = new URLSearchParams(window.location.search);
    var cityFromUrl = params.get('city');

    if (cityFromUrl && findCity(cityFromUrl)) {
        activeSlug = cityFromUrl;
    }

    render();
}());

(function () {
    var data = window.COOL_PLACES_DATA || null;

    if (!data || !data.city || !data.places || !data.places.length) {
        return;
    }

    var places = data.places || [];
    var placeCards = document.querySelector('[data-place-cards]');
    var carousel = document.querySelector('[data-place-carousel]');
    var info = document.querySelector('[data-place-info]');
    var feedback = document.querySelector('[data-place-feedback]');
    var activePlaceSlug = places[0].slug;
    var activePhotoIndex = 0;

    if (!placeCards || !carousel || !info || !feedback) {
        return;
    }

    function escapeHtml(value) {
        var node = document.createElement('span');
        node.textContent = value || '';
        return node.innerHTML;
    }

    function visualClass(value) {
        return 'cool-photo-' + String(value || 'city').toLowerCase().replace(/[^a-z0-9-]+/g, '-');
    }

    function findPlace(slug) {
        for (var i = 0; i < places.length; i += 1) {
            if (places[i].slug === slug) {
                return places[i];
            }
        }

        return places[0];
    }

    function storageKey(place) {
        return 'tripora-feedback-duisburg-' + place.slug;
    }

    function readStoredFeedbacks(place) {
        try {
            return JSON.parse(window.localStorage.getItem(storageKey(place)) || '[]');
        } catch (error) {
            return place.userFeedbacks || [];
        }
    }

    function writeStoredFeedbacks(place, items) {
        place.userFeedbacks = items;

        try {
            window.localStorage.setItem(storageKey(place), JSON.stringify(items));
        } catch (error) {
            return;
        }
    }

    function getPlaceFeedbacks(place) {
        return (place.feedbacks || []).concat(readStoredFeedbacks(place));
    }

    function renderPlaceCards(place) {
        placeCards.innerHTML = places.map(function (item, index) {
            var photo = (item.photos || [])[0] || {};
            var isActive = item.slug === place.slug;
            var order = index + 1;
            var orderLabel = order < 10 ? '0' + order : String(order);

            return '<button class="duisburg-place-card' + (isActive ? ' is-active' : '') + '" type="button" data-place-card="' + escapeHtml(item.slug) + '">' +
                '<span class="duisburg-place-thumb ' + visualClass(photo.visual) + '" aria-hidden="true"></span>' +
                '<span class="duisburg-place-copy">' +
                    '<small>' + orderLabel + ' / ' + escapeHtml(item.type) + '</small>' +
                    '<strong>' + escapeHtml(item.name) + '</strong>' +
                    '<em>' + escapeHtml(item.area) + '</em>' +
                '</span>' +
            '</button>';
        }).join('');

        placeCards.querySelectorAll('[data-place-card]').forEach(function (button) {
            button.addEventListener('click', function () {
                activePlaceSlug = button.getAttribute('data-place-card');
                activePhotoIndex = 0;
                render();
                
                var mainContent = document.querySelector('.place-main-content');
                if (mainContent) {
                    mainContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    function renderCarousel(place) {
        var photos = place.photos || [];
        var photo = photos[activePhotoIndex] || photos[0] || {};

        carousel.innerHTML =
            '<section class="place-carousel" aria-label="Fotos von ' + escapeHtml(place.name) + '">' +
                '<div class="place-carousel-main ' + visualClass(photo.visual) + '">' +
                    '<button class="carousel-arrow is-prev" type="button" data-carousel-prev aria-label="Vorheriges Foto">‹</button>' +
                    '<div class="place-carousel-caption">' +
                        '<span>' + escapeHtml(photo.label || place.type) + '</span>' +
                        '<strong>' + escapeHtml(place.name) + '</strong>' +
                        '<p>' + escapeHtml(photo.caption || place.intro) + '</p>' +
                    '</div>' +
                    '<button class="carousel-arrow is-next" type="button" data-carousel-next aria-label="Nächstes Foto">›</button>' +
                '</div>' +
                '<div class="place-carousel-thumbs" aria-label="Fotoauswahl">' +
                    photos.map(function (item, index) {
                        return '<button class="place-carousel-thumb ' + visualClass(item.visual) + (index === activePhotoIndex ? ' is-active' : '') + '" type="button" data-photo-index="' + index + '">' +
                            '<span>' + escapeHtml(item.label) + '</span>' +
                        '</button>';
                    }).join('') +
                '</div>' +
            '</section>';

        var previous = carousel.querySelector('[data-carousel-prev]');
        var next = carousel.querySelector('[data-carousel-next]');

        previous.addEventListener('click', function () {
            activePhotoIndex = (activePhotoIndex - 1 + photos.length) % photos.length;
            renderCarousel(place);
        });

        next.addEventListener('click', function () {
            activePhotoIndex = (activePhotoIndex + 1) % photos.length;
            renderCarousel(place);
        });

        carousel.querySelectorAll('[data-photo-index]').forEach(function (button) {
            button.addEventListener('click', function () {
                activePhotoIndex = Number(button.getAttribute('data-photo-index')) || 0;
                renderCarousel(place);
            });
        });
    }

    function renderInfo(place) {
        info.innerHTML =
            '<section class="place-info-panel" aria-labelledby="place-info-title">' +
                '<div class="place-info-copy">' +
                    '<span class="section-kicker">' + escapeHtml(place.type) + '</span>' +
                    '<h2 id="place-info-title">' + escapeHtml(place.name) + '</h2>' +
                    '<p>' + escapeHtml(place.intro) + '</p>' +
                    (place.why ? '<p>' + escapeHtml(place.why) + '</p>' : '') +
                    (place.tip ? '<div class="place-tip"><strong>Tipp</strong><span>' + escapeHtml(place.tip) + '</span></div>' : '') +
                '</div>' +
                '<dl class="place-facts">' +
                    (place.facts || []).map(function (fact) {
                        return '<div><dt>' + escapeHtml(fact.label) + '</dt><dd>' + escapeHtml(fact.value) + '</dd></div>';
                    }).join('') +
                    (place.area ? '<div><dt>Stadtteil</dt><dd>' + escapeHtml(place.area) + '</dd></div>' : '') +
                    '<div><dt>Adresse</dt><dd>' + escapeHtml(place.address) + '</dd></div>' +
                    '<div><dt>Beste Zeit</dt><dd>' + escapeHtml(place.bestTime) + '</dd></div>' +
                '</dl>' +
            '</section>';
    }

    function renderFeedback(place) {
        var items = getPlaceFeedbacks(place);

        feedback.innerHTML =
            '<section class="place-feedback-panel" aria-labelledby="place-feedback-title">' +
                '<div class="place-feedback-copy">' +
                    '<span class="section-kicker">Feedbacks</span>' +
                    '<h2 id="place-feedback-title">Feedback zu ' + escapeHtml(place.name) + '</h2>' +
                    '<div class="place-feedback-list">' +
                        items.map(function (item) {
                            return '<article class="place-feedback-card">' +
                                '<div class="feedback-quote-icon">“</div>' +
                                '<p>' + escapeHtml(item.quote) + '</p>' +
                                '<div class="feedback-meta">' +
                                    '<strong>' + escapeHtml(item.name) + '</strong>' +
                                    '<span class="feedback-date">Entdecker</span>' +
                                '</div>' +
                            '</article>';
                        }).join('') +
                    '</div>' +
                '</div>' +
                '<form class="place-feedback-form" data-place-feedback-form autocomplete="off">' +
                    '<label><span>Dein Kommentar</span><textarea name="quote" rows="5" placeholder="Was denkst du über diesen Ort?" maxlength="280" required></textarea></label>' +
                    '<button type="submit">Feedback senden</button>' +
                '</form>' +
            '</section>';

        feedback.querySelector('[data-place-feedback-form]').addEventListener('submit', function (event) {
            var form = event.currentTarget;
            var quote = form.elements.quote.value.trim();
            var stored = readStoredFeedbacks(place);

            event.preventDefault();

            if (!quote) {
                return;
            }

            stored.unshift({ name: 'Entdecker', quote: quote });
            writeStoredFeedbacks(place, stored.slice(0, 8));
            form.reset();
            renderFeedback(place);
        });
    }

    function render() {
        var place = findPlace(activePlaceSlug);

        activePlaceSlug = place.slug;
        activePhotoIndex = Math.min(activePhotoIndex, (place.photos || []).length - 1);

        renderPlaceCards(place);
        renderCarousel(place);
        renderInfo(place);
        renderFeedback(place);

        if (window.history && window.history.replaceState) {
            window.history.replaceState({}, '', window.location.pathname + '?place=' + encodeURIComponent(place.slug));
        }
    }

    var params = new URLSearchParams(window.location.search);
    var initialPlace = params.get('place');

    if (initialPlace && findPlace(initialPlace)) {
        activePlaceSlug = initialPlace;
    }

    render();
}());
