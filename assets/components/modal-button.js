export class ModalButton extends HTMLElement {
    connectedCallback() {
        this.uid = `modal-${Math.random().toString(36).substring(2, 9)}`;
        
        const message = this.getAttribute('message') || 'Deseja confirmar esta ação?';
        const title = this.getAttribute('title') || 'Confirmação';
        const cssClass = this.getAttribute('class') || 'btn btn-danger';
        const label = this.innerHTML.trim() || 'Confirmar';

        this.innerHTML = `
            <button type="button" class="${cssClass}" data-bs-toggle="modal" data-bs-target="#${this.uid}">
                ${label}
            </button>

            <div class="modal fade" id="${this.uid}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-start text-dark fw-normal">
                            ${message}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link text-secondary decoration-none" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-danger confirm-action-btn">Confirmar Exclusão</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        this.querySelector('.confirm-action-btn').addEventListener('click', () => {
            const parentForm = this.closest('form');
            if (parentForm) {
                parentForm.submit();
            }
        });
    }
}

if (!customElements.get('modal-button')) {
    customElements.define('modal-button', ModalButton);
}