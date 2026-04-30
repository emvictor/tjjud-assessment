export class ActionButton extends HTMLElement {
    connectedCallback() {
        const action = this.getAttribute('action');
        const itemId = this.getAttribute('id');
        const label = this.getAttribute('label') || 'Visualizar';
        const a = document.createElement('a');
        a.textContent = label;
        a.href = action;
        this.appendChild(a);
    }
}