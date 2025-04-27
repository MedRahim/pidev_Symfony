// assets/controllers/seatmap_controller.js
import { Controller } from '@hotwired/stimulus';
import SeatChart from 'seatchart';
import 'seatchart/dist/seatchart.css';

export default class extends Controller {
  static values = {
    tripId: Number,
    apiUrl: String,
    fallbackSeats: { type: Number, default: 49 },
    fallbackPrice: { type: Number, default: 6.5 }
  };

  async connect() {
    let config;
    try {
      config = await this.fetchConfig();
      config = this.normalizeConfig(config);
    } catch (e) {
      console.error('Erreur de récupération, fallback :', e);
      config = this.getFallbackConfig();
    }
    this.initSeatMap(config);
  }

  async fetchConfig() {
    const res = await fetch(this.apiUrlValue);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  }

  normalizeConfig(cfg = {}) {
    return {
      totalSeats: Math.abs(Number(cfg.totalSeats)) || this.fallbackSeatsValue,
      reservedSeats: Array.isArray(cfg.reservedSeats)
        ? cfg.reservedSeats.map(Number)
        : [],
      seatPrice: parseFloat(cfg.seatPrice) || this.fallbackPriceValue
    };
  }

  getFallbackConfig() {
    return {
      totalSeats: this.fallbackSeatsValue,
      reservedSeats: [],
      seatPrice: this.fallbackPriceValue
    };
  }

  initSeatMap({ totalSeats, reservedSeats, seatPrice }) {
    // Supprime le loader
    this.element.querySelector('.loading-overlay')?.remove();

    if (isNaN(totalSeats) || totalSeats <= 0) {
      return this.showError('Configuration des sièges invalide.');
    }

    const cols = 7;
    const rows = Math.ceil(totalSeats / cols);
    // transforme numéros en coordonnées
    const reservedCoords = reservedSeats.map(n => ({
      row: Math.floor((n - 1) / cols),
      col: (n - 1) % cols
    }));

    const options = {
      map: {
        rows:    Math.ceil(totalSeats / cols),
        columns: cols,
        reservedSeats: reservedCoords,       // [{row, col}, …]
        seatTypes: {                         // **seatTypes.default** est obligatoire
          default: {
            label: 'Standard',
            cssClass: 'seat-standard',
            price: seatPrice
          },
          premium: {
            label: 'Premium',
            cssClass: 'seat-premium',
            price: seatPrice * 1.5
          }
        }
      },
      legendVisible: true,
      cart: {
        visible: true,
        currency: 'TND',
        submitLabel: 'Réserver'
      }
    };
    
    console.log('Options finales Seatchart :', options);

    try {
      this.seatChart = new SeatChart(this.element, options);
    } catch (e) {
      console.error('[Seatmap] Initialization error:', e);
      this.showError('Impossible d’afficher le plan des sièges.');
    }
  }

  showError(msg) {
    this.element.querySelector('.loading-overlay')?.remove();
    let div = this.element.querySelector('.seatmap-error');
    if (!div) {
      div = document.createElement('div');
      div.classList.add('seatmap-error');
      this.element.appendChild(div);
    }
    div.textContent = msg;
    div.style.display = 'block';
  }
}
