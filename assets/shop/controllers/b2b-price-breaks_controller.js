import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
// US-090: the quantity typed in the add to cart form picks the price break (row in evidence), the total of the line
// and the "add N more" hint, without reloading the page. The prices come from the server (plugin calculator): this
// only reads the rows it rendered.
export default class extends Controller {
    static targets = ['row', 'total', 'nudge', 'announcement'];

    static values = {
        rows: Array,
        locale: String,
        currency: String,
        tax: String,
        totalTemplate: String,
        nudgeTemplate: String,
    };

    connect() {
        this.input = this.element.closest('form')?.querySelector('input[name$="[quantity]"]') ?? null;
        if (!this.input) {
            return;
        }
        // The lines change at every keystroke; the screen reader hears them once the typing pauses or the field is left.
        this.listener = () => { this.refresh(); this.announceLater(); };
        this.changeListener = () => { this.refresh(); this.announce(); };
        this.input.addEventListener('input', this.listener);
        this.input.addEventListener('change', this.changeListener);
        this.refresh();
    }

    disconnect() {
        this.input?.removeEventListener('input', this.listener);
        this.input?.removeEventListener('change', this.changeListener);
        clearTimeout(this.timer);
    }

    announceLater() {
        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.announce(), 500);
    }

    announce() {
        clearTimeout(this.timer);
        this.announcementTarget.textContent = [this.totalTarget.textContent, this.nudgeTarget.hidden ? '' : this.nudgeTarget.textContent].filter(Boolean).join(' ');
    }

    refresh() {
        const quantity = Math.max(1, parseInt(this.input.value, 10) || 1);
        const rows = this.rowsValue;
        const current = rows.filter((row) => row.min <= quantity).pop() ?? rows[0];

        this.rowTargets.forEach((element) => {
            const active = Number(element.dataset.min) === current.min;
            element.classList.toggle('b2b-price-breaks__row--current', active);
            element.querySelector('.b2b-price-breaks__current-label').hidden = !active;
            active ? element.setAttribute('aria-current', 'true') : element.removeAttribute('aria-current');
        });

        this.totalTarget.textContent = this.fill(this.totalTemplateValue, {
            quantity,
            unit: this.money(current.price),
            total: this.money(current.price * quantity),
            tax: this.taxValue,
        });
        this.totalTarget.hidden = false;

        const next = rows.find((row) => row.min > quantity);
        this.nudgeTarget.hidden = !next;
        if (next) {
            this.nudgeTarget.textContent = this.fill(this.nudgeTemplateValue, { quantity: next.min - quantity, price: this.money(next.price), tax: this.taxValue });
        }
    }

    money(cents) {
        return new Intl.NumberFormat(this.localeValue, { style: 'currency', currency: this.currencyValue }).format(cents / 100);
    }

    fill(template, values) {
        return template.replace(/\{(\w+)\}/g, (match, key) => (key in values ? values[key] : match));
    }
}
