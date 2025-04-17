document.addEventListener('DOMContentLoaded', function () {
    // Autocomplétion avec jQuery UI
    $("#searchInput").autocomplete({
        source: $("#searchInput").data("autocomplete-url"), // définir data-autocomplete-url dans le template de base
        minLength: 2
    });

    // Mise à jour dynamique du label du slider de prix
    const priceRange = document.getElementById('priceRange');
    const priceLabel = document.getElementById('priceLabel');
    if (priceRange && priceLabel) {
        priceLabel.textContent = priceRange.value;
        priceRange.addEventListener('input', function () {
            priceLabel.textContent = this.value;
        });
    }

    // Fonction AJAX pour charger les trajets en fonction des filtres
    function loadTrips(replaceContent = false, page = 1) {
        const filterForm = document.getElementById('filterForm');
        const listingContainer = document.getElementById('tripsContainer');
        const spinner = document.getElementById('spinner');

        spinner.style.display = 'flex';

        const formData = new FormData(filterForm);
        formData.append('page', page);
        const queryString = new URLSearchParams(formData).toString();

        $.ajax({
            url: filterForm.action,
            method: 'GET',
            data: queryString,
            dataType: 'html',
            success: function(response) {
                spinner.style.display = 'none';
                if (replaceContent) {
                    listingContainer.innerHTML = response;
                } else {
                    listingContainer.insertAdjacentHTML('beforeend', response);
                }
            },
            error: function() {
                spinner.style.display = 'none';
                alert('Une erreur est survenue lors du chargement des trajets. Veuillez réessayer.');
            }
        });
    }

    // Déclenchement du filtrage à chaque changement dans le formulaire
    const filterForm = document.getElementById('filterForm');
    filterForm.addEventListener('change', function(e) {
        e.preventDefault();
        loadTrips(true, 1);
    });

    // Réinitialisation des filtres
    const resetBtn = document.getElementById('resetFilters');
    resetBtn.addEventListener('click', function() {
        filterForm.reset();
        loadTrips(true, 1);
    });

    // Infinite scrolling avec IntersectionObserver
    const infiniteTrigger = document.getElementById('infiniteScrollTrigger');
    if (infiniteTrigger) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    let nextPage = parseInt(infiniteTrigger.getAttribute('data-next-page')) || 2;
                    loadTrips(false, nextPage);
                    infiniteTrigger.setAttribute('data-next-page', nextPage + 1);
                }
            });
        });
        observer.observe(infiniteTrigger);
    }

    // Animation fade-in pour les nouvelles cartes
    const tripItems = document.querySelectorAll('.trip-item');
    tripItems.forEach(item => {
        item.style.opacity = 0;
        setTimeout(() => {
            item.style.transition = 'opacity 0.8s ease-in-out';
            item.style.opacity = 1;
        }, 100);
    });
});
