import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
// US-076: the demo bar's two <details> menus are absolutely positioned panels: opening one closes the other, Escape
// closes them (focus goes back to the summary) and so does a click outside.
export default class extends Controller {
    static targets = ['menu'];

    toggled(event) {
        if (!event.target.open) {
            return;
        }
        this.menuTargets.filter((menu) => menu !== event.target).forEach((menu) => { menu.open = false; });
    }

    close(event) {
        const open = this.menuTargets.find((menu) => menu.open);
        if (!open) {
            return;
        }
        open.open = false;
        open.querySelector('summary')?.focus();
        event.preventDefault();
    }

    outside(event) {
        this.menuTargets.filter((menu) => menu.open && !menu.contains(event.target)).forEach((menu) => { menu.open = false; });
    }
}
