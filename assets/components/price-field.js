export class PriceField extends HTMLElement {
    connectedCallback() {
        const input = this.querySelector('input');
        input.addEventListener('input', (e) => {
            let value = e.target.value;
            value = value.replace(',', '.').replace(/[^0-9.]/g, '');
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            e.target.value = value;
        });
    }
}
customElements.define('price-field', PriceField);