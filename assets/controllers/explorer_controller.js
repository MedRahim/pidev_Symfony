// assets/controllers/explorer_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['badge'];

  connect() {
    this.animateBadge();
  }

  animateBadge() {
    this.badgeTargets.forEach((el, index) => {
      setTimeout(() => {
        el.classList.add('badge-grow');
      }, index * 200);
    });
  }
}