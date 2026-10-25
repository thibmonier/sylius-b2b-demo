import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
// US-200: the "−" and "+" buttons of a line of the cart change the quantity field by one and tell the live cart form, which
// recalculates the prices and the totals on the server like for a typed quantity. Without JavaScript the field stays editable.
export default class extends Controller {
    static targets = ['input'];

    decrease() {
        this.change(-1);
    }

    increase() {
        this.change(1);
    }

    change(step) {
        const minimum = parseInt(this.inputTarget.min, 10) || 1;
        const current = parseInt(this.inputTarget.value, 10) || minimum;
        const next = Math.max(minimum, current + step);
        if (next === current) {
            return;
        }
        this.inputTarget.value = String(next);
        this.inputTarget.dispatchEvent(new Event('input', { bubbles: true }));
        this.inputTarget.dispatchEvent(new Event('change', { bubbles: true }));
    }
}
