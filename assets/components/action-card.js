export class ActionCard extends HTMLElement {
    connectedCallback() {
        const title = this.getAttribute('title') || 'Listagem';
        const variant = this.getAttribute('variant') || 'dark';
        
        // Preserve the original HTML content to move it inside the card-body
        const originalContent = this.innerHTML;

        this.innerHTML = `
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-${variant} text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold">${title}</h5>
                </div>
                <div class="card-body p-0"> 
                    <div class="p-3">
                        ${originalContent}
                    </div>
                </div>
            </div>
        `;
    }
}

if (!customElements.get('action-card')) {
    customElements.define('action-card', ActionCard);
}