import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  connect() {
    setTimeout(() => this.element.classList.add('fade-out'), 4000);
  }
}
