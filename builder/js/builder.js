/**
 * SpaSpb Page Builder - JavaScript
 * Gestisce drag & drop, editing blocchi, salvataggio
 */

// Stato globale
let blocks = [];
let selectedBlockId = null;
let draggedType = null;

// Inizializzazione
document.addEventListener('DOMContentLoaded', function() {
    initDragAndDrop();
    initButtons();
    loadPage();
});

/**
 * Inizializza drag & drop
 */
function initDragAndDrop() {
    const blockItems = document.querySelectorAll('.block-item');
    const canvas = document.getElementById('canvas');

    // Drag start dai blocchi della palette
    blockItems.forEach(item => {
        item.addEventListener('dragstart', function(e) {
            draggedType = this.dataset.type;
            this.classList.add('dragging');
        });

        item.addEventListener('dragend', function(e) {
            this.classList.remove('dragging');
        });
    });

    // Drop sul canvas
    canvas.addEventListener('dragover', function(e) {
        e.preventDefault();
        canvas.classList.add('drag-over');
    });

    canvas.addEventListener('dragleave', function(e) {
        canvas.classList.remove('drag-over');
    });

    canvas.addEventListener('drop', function(e) {
        e.preventDefault();
        canvas.classList.remove('drag-over');

        if (draggedType) {
            addBlock(draggedType);
            draggedType = null;
        }
    });

    // Sortable per riordinare blocchi
    initSortable();
}

/**
 * Inizializza Sortable per riordinare blocchi nel canvas
 */
function initSortable() {
    const canvas = document.getElementById('canvas');

    canvas.addEventListener('dragstart', function(e) {
        if (e.target.classList.contains('canvas-block')) {
            e.target.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        }
    });

    canvas.addEventListener('dragend', function(e) {
        if (e.target.classList.contains('canvas-block')) {
            e.target.classList.remove('dragging');
        }
    });

    canvas.addEventListener('dragover', function(e) {
        e.preventDefault();
        const draggingElement = document.querySelector('.canvas-block.dragging');
        if (!draggingElement) return;

        const afterElement = getDragAfterElement(canvas, e.clientY);
        if (afterElement == null) {
            canvas.appendChild(draggingElement);
        } else {
            canvas.insertBefore(draggingElement, afterElement);
        }
    });
}

/**
 * Trova l'elemento dopo cui inserire il blocco draggato
 */
function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.canvas-block:not(.dragging)')];

    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;

        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

/**
 * Inizializza bottoni toolbar
 */
function initButtons() {
    document.getElementById('btn-save').addEventListener('click', savePage);
    document.getElementById('btn-preview').addEventListener('click', previewPage);
    document.getElementById('close-properties').addEventListener('click', closeProperties);

    document.getElementById('page-title').addEventListener('change', function() {
        autoSave();
    });

    document.getElementById('ui-library').addEventListener('change', function() {
        autoSave();
    });
}

/**
 * Aggiunge un blocco al canvas
 */
function addBlock(type) {
    const block = {
        id: generateId(),
        type: type,
        content: getDefaultContent(type),
        settings: getDefaultSettings(type)
    };

    blocks.push(block);
    renderCanvas();
    selectBlock(block.id);
}

/**
 * Genera ID univoco
 */
function generateId() {
    return 'block_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
}

/**
 * Contenuto di default per tipo blocco
 */
function getDefaultContent(type) {
    const defaults = {
        hero: '<h1>Benvenuto</h1><p>Sottotitolo della hero section</p>',
        text: '<p>Scrivi qui il tuo testo...</p>',
        image: '',
        gallery: '',
        cta: '<h2>Call to Action</h2><p>Descrizione della CTA</p>',
        features: '<h2>Le nostre Features</h2>',
        contact: '<h2>Contattaci</h2><p>Invia un messaggio</p>'
    };

    return defaults[type] || '';
}

/**
 * Impostazioni di default per tipo blocco
 */
function getDefaultSettings(type) {
    const defaults = {
        hero: {
            backgroundImage: '',
            backgroundColor: '#f3f4f6',
            textColor: '#1f2937',
            align: 'center',
            height: '500px'
        },
        text: {
            backgroundColor: '#ffffff',
            textColor: '#1f2937',
            padding: '40px',
            align: 'left'
        },
        image: {
            image: '',
            alt: 'Immagine',
            width: '100%',
            align: 'center'
        },
        gallery: {
            images: [],
            columns: 3,
            gap: '20px'
        },
        cta: {
            backgroundColor: '#3b82f6',
            textColor: '#ffffff',
            buttonText: 'Inizia ora',
            buttonLink: '#'
        },
        features: {
            features: [
                { icon: '⚡', title: 'Veloce', description: 'Prestazioni eccellenti' },
                { icon: '🎨', title: 'Bello', description: 'Design moderno' },
                { icon: '🔒', title: 'Sicuro', description: 'Protetto e affidabile' }
            ]
        },
        contact: {
            email: 'info@example.com',
            phone: '+39 123 456 789',
            address: 'Via Roma 1, Milano'
        }
    };

    return defaults[type] || {};
}

/**
 * Renderizza il canvas con i blocchi
 */
function renderCanvas() {
    const canvas = document.getElementById('canvas');
    const placeholder = canvas.querySelector('.canvas-placeholder');

    if (blocks.length === 0) {
        if (!placeholder) {
            canvas.innerHTML = '<div class="canvas-placeholder">Trascina i blocchi qui per iniziare a costruire la tua pagina</div>';
        }
        return;
    }

    if (placeholder) {
        placeholder.remove();
    }

    canvas.innerHTML = blocks.map(block => `
        <div class="canvas-block" draggable="true" data-id="${block.id}" onclick="selectBlock('${block.id}')">
            <div class="block-header">
                <span class="block-type">${getBlockIcon(block.type)} ${getBlockLabel(block.type)}</span>
                <div class="block-actions">
                    <button onclick="event.stopPropagation(); moveBlockUp('${block.id}')" class="btn-icon" title="Sposta su">↑</button>
                    <button onclick="event.stopPropagation(); moveBlockDown('${block.id}')" class="btn-icon" title="Sposta giù">↓</button>
                    <button onclick="event.stopPropagation(); duplicateBlock('${block.id}')" class="btn-icon" title="Duplica">📋</button>
                    <button onclick="event.stopPropagation(); deleteBlock('${block.id}')" class="btn-icon btn-delete" title="Elimina">🗑️</button>
                </div>
            </div>
            <div class="block-preview">
                ${renderBlockPreview(block)}
            </div>
        </div>
    `).join('');

    // Re-bind eventi drag
    const canvasBlocks = canvas.querySelectorAll('.canvas-block');
    canvasBlocks.forEach(el => {
        el.addEventListener('click', function(e) {
            if (!e.target.classList.contains('btn-icon')) {
                selectBlock(this.dataset.id);
            }
        });
    });
}

/**
 * Icona per tipo blocco
 */
function getBlockIcon(type) {
    const icons = {
        hero: '🎯',
        text: '📝',
        image: '🖼️',
        gallery: '🎨',
        cta: '🚀',
        features: '⭐',
        contact: '📧'
    };
    return icons[type] || '📦';
}

/**
 * Label per tipo blocco
 */
function getBlockLabel(type) {
    const labels = {
        hero: 'Hero Section',
        text: 'Testo',
        image: 'Immagine',
        gallery: 'Galleria',
        cta: 'Call to Action',
        features: 'Features',
        contact: 'Contatti'
    };
    return labels[type] || type;
}

/**
 * Renderizza preview del blocco
 */
function renderBlockPreview(block) {
    const content = block.content || '';
    const settings = block.settings || {};

    let preview = '<div class="block-content">';

    switch (block.type) {
        case 'hero':
            preview += `<div style="background: ${settings.backgroundColor}; color: ${settings.textColor}; text-align: ${settings.align}; padding: 20px;">${content}</div>`;
            break;
        case 'text':
            preview += `<div style="text-align: ${settings.align};">${content}</div>`;
            break;
        case 'image':
            if (settings.image) {
                preview += `<img src="${settings.image}" alt="${settings.alt}" style="max-width: 200px;">`;
            } else {
                preview += '<div class="empty-image">Nessuna immagine</div>';
            }
            break;
        case 'gallery':
            if (settings.images && settings.images.length > 0) {
                preview += `<div style="display: grid; grid-template-columns: repeat(${settings.columns}, 1fr); gap: 10px;">`;
                settings.images.forEach(img => {
                    preview += `<img src="${img}" style="width: 100%; height: 100px; object-fit: cover;">`;
                });
                preview += '</div>';
            } else {
                preview += '<div class="empty-gallery">Nessuna immagine in galleria</div>';
            }
            break;
        case 'cta':
            preview += `<div style="background: ${settings.backgroundColor}; color: ${settings.textColor}; text-align: center; padding: 20px;">${content}<br><button>${settings.buttonText}</button></div>`;
            break;
        case 'features':
            preview += content;
            if (settings.features && settings.features.length > 0) {
                preview += '<div class="features-preview">';
                settings.features.forEach(f => {
                    preview += `<div class="feature-item"><span>${f.icon}</span> <strong>${f.title}</strong></div>`;
                });
                preview += '</div>';
            }
            break;
        case 'contact':
            preview += content;
            break;
        default:
            preview += content;
    }

    preview += '</div>';
    return preview;
}

/**
 * Seleziona un blocco
 */
function selectBlock(id) {
    selectedBlockId = id;
    const block = blocks.find(b => b.id === id);

    if (!block) return;

    // Highlight nel canvas
    document.querySelectorAll('.canvas-block').forEach(el => {
        el.classList.remove('selected');
    });
    document.querySelector(`.canvas-block[data-id="${id}"]`)?.classList.add('selected');

    // Mostra pannello proprietà
    showProperties(block);
}

/**
 * Mostra pannello proprietà
 */
function showProperties(block) {
    const panel = document.getElementById('properties-panel');
    const content = document.getElementById('properties-content');

    panel.classList.add('active');

    let html = `<h4>${getBlockLabel(block.type)}</h4>`;

    // Editor contenuto
    html += `
        <div class="property-group">
            <label>Contenuto HTML</label>
            <textarea id="block-content" rows="6" class="form-control">${escapeHtml(block.content)}</textarea>
        </div>
    `;

    // Impostazioni specifiche per tipo
    html += renderBlockSettings(block);

    // Bottoni azione
    html += `
        <div class="property-actions">
            <button onclick="saveBlockProperties()" class="btn btn-primary btn-block">Applica Modifiche</button>
        </div>
    `;

    content.innerHTML = html;
}

/**
 * Renderizza impostazioni specifiche per tipo blocco
 */
function renderBlockSettings(block) {
    let html = '<div class="property-group"><label>Impostazioni</label></div>';

    switch (block.type) {
        case 'hero':
            html += `
                <div class="property-group">
                    <label>Colore Sfondo</label>
                    <input type="color" id="setting-backgroundColor" value="${block.settings.backgroundColor}" class="form-control">
                </div>
                <div class="property-group">
                    <label>Colore Testo</label>
                    <input type="color" id="setting-textColor" value="${block.settings.textColor}" class="form-control">
                </div>
                <div class="property-group">
                    <label>Allineamento</label>
                    <select id="setting-align" class="form-control">
                        <option value="left" ${block.settings.align === 'left' ? 'selected' : ''}>Sinistra</option>
                        <option value="center" ${block.settings.align === 'center' ? 'selected' : ''}>Centro</option>
                        <option value="right" ${block.settings.align === 'right' ? 'selected' : ''}>Destra</option>
                    </select>
                </div>
                <div class="property-group">
                    <label>Altezza</label>
                    <input type="text" id="setting-height" value="${block.settings.height}" class="form-control" placeholder="500px">
                </div>
            `;
            break;

        case 'text':
            html += `
                <div class="property-group">
                    <label>Allineamento</label>
                    <select id="setting-align" class="form-control">
                        <option value="left" ${block.settings.align === 'left' ? 'selected' : ''}>Sinistra</option>
                        <option value="center" ${block.settings.align === 'center' ? 'selected' : ''}>Centro</option>
                        <option value="right" ${block.settings.align === 'right' ? 'selected' : ''}>Destra</option>
                    </select>
                </div>
                <div class="property-group">
                    <label>Padding</label>
                    <input type="text" id="setting-padding" value="${block.settings.padding}" class="form-control" placeholder="40px">
                </div>
            `;
            break;

        case 'image':
            html += `
                <div class="property-group">
                    <label>Immagine</label>
                    <input type="file" id="image-upload" class="form-control" accept="image/*" onchange="uploadImage(this)">
                    ${block.settings.image ? `<img src="${block.settings.image}" style="max-width: 100%; margin-top: 10px;">` : ''}
                    <input type="hidden" id="setting-image" value="${block.settings.image}">
                </div>
                <div class="property-group">
                    <label>Testo Alt</label>
                    <input type="text" id="setting-alt" value="${block.settings.alt}" class="form-control">
                </div>
            `;
            break;

        case 'cta':
            html += `
                <div class="property-group">
                    <label>Colore Sfondo</label>
                    <input type="color" id="setting-backgroundColor" value="${block.settings.backgroundColor}" class="form-control">
                </div>
                <div class="property-group">
                    <label>Testo Bottone</label>
                    <input type="text" id="setting-buttonText" value="${block.settings.buttonText}" class="form-control">
                </div>
                <div class="property-group">
                    <label>Link Bottone</label>
                    <input type="text" id="setting-buttonLink" value="${block.settings.buttonLink}" class="form-control">
                </div>
            `;
            break;
    }

    return html;
}

/**
 * Salva proprietà del blocco corrente
 */
function saveBlockProperties() {
    if (!selectedBlockId) return;

    const block = blocks.find(b => b.id === selectedBlockId);
    if (!block) return;

    // Aggiorna contenuto
    const contentEl = document.getElementById('block-content');
    if (contentEl) {
        block.content = contentEl.value;
    }

    // Aggiorna settings
    const settingInputs = document.querySelectorAll('[id^="setting-"]');
    settingInputs.forEach(input => {
        const key = input.id.replace('setting-', '');
        block.settings[key] = input.value;
    });

    renderCanvas();
    showProperties(block);
    autoSave();
}

/**
 * Chiude pannello proprietà
 */
function closeProperties() {
    document.getElementById('properties-panel').classList.remove('active');
    selectedBlockId = null;
    document.querySelectorAll('.canvas-block').forEach(el => {
        el.classList.remove('selected');
    });
}

/**
 * Sposta blocco su
 */
function moveBlockUp(id) {
    const index = blocks.findIndex(b => b.id === id);
    if (index > 0) {
        [blocks[index - 1], blocks[index]] = [blocks[index], blocks[index - 1]];
        renderCanvas();
        autoSave();
    }
}

/**
 * Sposta blocco giù
 */
function moveBlockDown(id) {
    const index = blocks.findIndex(b => b.id === id);
    if (index < blocks.length - 1) {
        [blocks[index], blocks[index + 1]] = [blocks[index + 1], blocks[index]];
        renderCanvas();
        autoSave();
    }
}

/**
 * Duplica blocco
 */
function duplicateBlock(id) {
    const block = blocks.find(b => b.id === id);
    if (block) {
        const newBlock = {
            ...JSON.parse(JSON.stringify(block)),
            id: generateId()
        };
        blocks.push(newBlock);
        renderCanvas();
        autoSave();
    }
}

/**
 * Elimina blocco
 */
function deleteBlock(id) {
    if (confirm('Eliminare questo blocco?')) {
        blocks = blocks.filter(b => b.id !== id);
        renderCanvas();
        closeProperties();
        autoSave();
    }
}

/**
 * Upload immagine
 */
function uploadImage(input) {
    if (!input.files || !input.files[0]) return;

    const formData = new FormData();
    formData.append('image', input.files[0]);

    fetch('?action=api_upload', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('setting-image').value = data.url;
            alert('Immagine caricata con successo!');
        } else {
            alert('Errore: ' + data.error);
        }
    })
    .catch(err => {
        alert('Errore nel caricamento');
    });
}

/**
 * Carica pagina dal database
 */
function loadPage() {
    fetch(`?action=api_load&id=${PAGE_ID}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                blocks = JSON.parse(data.page.blocks);
                renderCanvas();
            }
        })
        .catch(err => {
            console.error('Errore caricamento:', err);
        });
}

/**
 * Salva pagina
 */
function savePage() {
    const title = document.getElementById('page-title').value;
    const uiLibrary = document.getElementById('ui-library').value;

    const formData = new FormData();
    formData.append('id', PAGE_ID);
    formData.append('title', title);
    formData.append('blocks', JSON.stringify(blocks));
    formData.append('ui_library', uiLibrary);

    fetch('?action=api_save', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showNotification('Salvato con successo!');
        } else {
            alert('Errore: ' + data.error);
        }
    })
    .catch(err => {
        alert('Errore nel salvataggio');
    });
}

/**
 * Auto-salva (debounced)
 */
let autoSaveTimeout;
function autoSave() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(() => {
        savePage();
    }, 2000);
}

/**
 * Anteprima pagina
 */
function previewPage() {
    // Salva prima
    savePage();

    // Apri in nuova finestra
    setTimeout(() => {
        window.open(`?action=export&id=${PAGE_ID}&preview=1`, '_blank');
    }, 500);
}

/**
 * Mostra notifica
 */
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.add('show');
    }, 10);

    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 2000);
}

/**
 * Escape HTML
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
