import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static values = {
    apiUrl: String,
    fallbackSeats: Number,
    fallbackPrice: Number,
    premiumSeatsCount: Number,
    premiumMultiplier: Number
  };

  static targets = [
    'map',
    'priceDisplay',
    'seatNumberInput',
    'seatTypesInput'
  ];

  connect() {
    this.selected = new Set();
    this.submitBtn = this.element.querySelector('button[type="submit"]');
    this.baseBtnText = this.submitBtn.textContent.trim();
    this.submitBtn.disabled = true;

    this.element.addEventListener('submit', this.validateSelection.bind(this));
    this.fetchConfig();
  }

  validateSelection(event) {
    if (this.selected.size === 0) {
      event.preventDefault();
      alert('⚠️ Veuillez sélectionner au moins un siège avant de confirmer la réservation.');
    } else {
      this.updateDisplay(); // Force la mise à jour avant soumission
    }
  }

  async fetchConfig() {
    let config;
    try {
      const res = await fetch(this.apiUrlValue);
      if (!res.ok) throw new Error(res.status);
      config = await res.json();
    } catch {
      config = {
        totalSeats: this.fallbackSeatsValue,
        reservedSeats: []
      };
    }
    this.init2DMap(config);
  }

  init2DMap({ totalSeats, reservedSeats }) {
    this.element.querySelector('.loading-overlay').style.display = 'none';
    this.element.querySelector('.seatmap-error').style.display = 'none';

    this.mapTarget.innerHTML = '';
    this.mapTarget.classList.add('seatmap-2d');

    const reservedSet = new Set(reservedSeats.map(Number));
    for (let n = 1; n <= totalSeats; n++) {
      const cell = this.createSeatElement(n, reservedSet);
      this.mapTarget.appendChild(cell);
    }
    this.updateDisplay();
  }

  createSeatElement(n, reservedSet) {
    const cell = document.createElement('div');
    const row = Math.floor((n - 1) / 7);
    const col = (n - 1) % 7;
    cell.textContent = `${String.fromCharCode(65 + row)}${col + 1}`;
    cell.classList.add('seat');
    cell.dataset.seatNumber = n;
    cell.style.position = 'relative';

    const isPremium = n <= this.premiumSeatsCountValue;
    const price = this.fallbackPriceValue * (isPremium ? this.premiumMultiplierValue : 1);
    cell.dataset.price = price.toFixed(2);

    if (isPremium) {
      cell.classList.add('premium');
      const badge = document.createElement('span');
      badge.classList.add('premium-badge');
      badge.textContent = '★';
      cell.appendChild(badge);
    }

    if (reservedSet.has(n)) {
      cell.classList.add('reserved');
    } else {
      cell.classList.add('available');
      cell.dataset.action = [
        'click->seatmap#toggleSeat',
        'mouseenter->seatmap#addHoverEffect',
        'mouseleave->seatmap#removeHoverEffect'
      ].join(' ');
    }
    return cell;
  }

  addHoverEffect(e) {
    const cell = e.currentTarget;
    if (!cell.classList.contains('selected')) {
      cell.style.transform = 'translateY(-3px)';
      cell.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.05)';
    }
  }

  removeHoverEffect(e) {
    const cell = e.currentTarget;
    if (!cell.classList.contains('selected')) {
      cell.style.transform = 'none';
      cell.style.boxShadow = 'none';
    }
  }

  toggleSeat(e) {
    const cell = e.currentTarget;
    const n = Number(cell.dataset.seatNumber);
    if (this.selected.has(n)) {
      this.selected.delete(n);
      cell.classList.replace('selected', 'available');
      cell.style.transform = 'none';
    } else {
      this.selected.add(n);
      cell.classList.replace('available', 'selected');
      cell.style.transform = 'scale(1.05)';
    }
    this.updateDisplay();
  }

  updateDisplay() {
    let total = 0;
    const seatTypes = [];
    
    this.selected.forEach(n => {
      const cell = this.mapTarget.querySelector(`[data-seat-number="${n}"]`);
      total += parseFloat(cell.dataset.price);
      seatTypes.push(cell.classList.contains('premium') ? 'Premium' : 'Standard');
    });

    this.priceDisplayTarget.textContent = `${total.toFixed(2)} TND`;
    this.seatNumberInputTarget.value = Array.from(this.selected).join(',');
    this.seatTypesInputTarget.value = seatTypes.join(',');

    this.submitBtn.disabled = this.selected.size === 0;
    this.submitBtn.textContent = this.selected.size > 0 
      ? `${this.baseBtnText} (${total.toFixed(2)} TND)`
      : this.baseBtnText;
  }
}