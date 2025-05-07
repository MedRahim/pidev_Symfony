// assets/controllers/countup_controller.js
import { Controller } from '@hotwired/stimulus';
import { CountUp } from 'countup.js';

export default class extends Controller {
  connect() {
    const kmEl = this.element.querySelector('#km-value');
    const co2El = this.element.querySelector('#co2-value');
    const tripsEl = this.element.querySelector('#trips-value');

    const km = new CountUp(kmEl, parseFloat(kmEl.textContent), { decimalPlaces: 1 });
    const co2 = new CountUp(co2El, parseFloat(co2El.textContent), { decimalPlaces: 1 });
    const trips = new CountUp(tripsEl, parseInt(tripsEl.textContent));

    if (!km.error) km.start();
    if (!co2.error) co2.start();
    if (!trips.error) trips.start();
  }
}