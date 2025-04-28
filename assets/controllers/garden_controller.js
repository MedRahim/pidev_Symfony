// assets/controllers/garden_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['plant'];

  connect() {
    this.animateGarden();
  }

  animateGarden() {
    this.plantTargets.forEach((el, index) => {
      setTimeout(() => {
        el.classList.add('plant-bloom');
      }, index * 300);
    });
  }
}
