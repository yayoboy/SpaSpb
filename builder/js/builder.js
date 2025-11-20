/**
 * SpaSpb Page Builder - JavaScript
 * Gestisce drag & drop, editing blocchi, salvataggio
 */

// Stato globale
let blocks = [];
let selectedBlockId = null;
let draggedType = null;
let currentTheme = 'light';
let isResizing = false;
let resizeBlockId = null;
let animationObserver = null;

// Undo/Redo
let history = [];
let historyIndex = -1;
const MAX_HISTORY = 50;

// Temi disponibili
const themes = {
    light: {
        name: 'Light',
        primary: '#3b82f6',
        secondary: '#6b7280',
        background: '#ffffff',
        surface: '#f9fafb',
        text: '#1f2937',
        textMuted: '#6b7280',
        border: '#e5e7eb',
        accent: '#10b981'
    },
    dark: {
        name: 'Dark',
        primary: '#60a5fa',
        secondary: '#9ca3af',
        background: '#111827',
        surface: '#1f2937',
        text: '#f9fafb',
        textMuted: '#9ca3af',
        border: '#374151',
        accent: '#34d399'
    },
    professional: {
        name: 'Professional',
        primary: '#1e40af',
        secondary: '#475569',
        background: '#f8fafc',
        surface: '#ffffff',
        text: '#0f172a',
        textMuted: '#64748b',
        border: '#e2e8f0',
        accent: '#0d9488'
    },
    creative: {
        name: 'Creative',
        primary: '#7c3aed',
        secondary: '#a78bfa',
        background: '#faf5ff',
        surface: '#ffffff',
        text: '#581c87',
        textMuted: '#7e22ce',
        border: '#e9d5ff',
        accent: '#f472b6'
    },
    minimal: {
        name: 'Minimal',
        primary: '#18181b',
        secondary: '#71717a',
        background: '#fafafa',
        surface: '#ffffff',
        text: '#18181b',
        textMuted: '#71717a',
        border: '#e4e4e7',
        accent: '#18181b'
    }
};

// Inizializzazione
document.addEventListener('DOMContentLoaded', function() {
    initDragAndDrop();
    initButtons();
    initThemeSelector();
    initResizeHandlers();
    initKeyboardShortcuts();
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
    saveToHistory();
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
        contact: '<h2>Contattaci</h2><p>Invia un messaggio</p>',
        video: '',
        tabs: '',
        countdown: '<h2>Prossimo Evento</h2><p>Non perdere questa occasione!</p>',
        header: '',
        footer: '',
        faq: '',
        testimonials: '',
        pricing: ''
    };

    return defaults[type] || '';
}

/**
 * Impostazioni di default per tipo blocco
 */
function getDefaultSettings(type) {
    // Grid settings comuni a tutti i blocchi
    const gridDefaults = {
        gridColumns: 12,      // Larghezza in colonne (1-12)
        gridOffset: 0,        // Offset in colonne (0-11)
        minHeight: 'auto',    // Altezza minima
        // Animazioni
        animation: 'none',           // Tipo animazione
        animationDuration: '0.6',    // Durata in secondi
        animationDelay: '0'          // Ritardo in secondi
    };

    const defaults = {
        hero: {
            ...gridDefaults,
            backgroundImage: '',
            backgroundColor: '#f3f4f6',
            textColor: '#1f2937',
            align: 'center',
            height: '500px'
        },
        text: {
            ...gridDefaults,
            backgroundColor: '#ffffff',
            textColor: '#1f2937',
            padding: '40px',
            align: 'left'
        },
        image: {
            ...gridDefaults,
            gridColumns: 6,
            image: '',
            alt: 'Immagine',
            width: '100%',
            align: 'center'
        },
        gallery: {
            ...gridDefaults,
            images: [],
            columns: 3,
            gap: 'medium'
        },
        cta: {
            ...gridDefaults,
            backgroundColor: '#3b82f6',
            textColor: '#ffffff',
            buttonText: 'Inizia ora',
            buttonLink: '#'
        },
        features: {
            ...gridDefaults,
            features: [
                { icon: '⚡', title: 'Veloce', description: 'Prestazioni eccellenti' },
                { icon: '🎨', title: 'Bello', description: 'Design moderno' },
                { icon: '🔒', title: 'Sicuro', description: 'Protetto e affidabile' }
            ]
        },
        contact: {
            ...gridDefaults,
            gridColumns: 8,
            gridOffset: 2,
            email: 'info@example.com',
            phone: '+39 123 456 789',
            address: 'Via Roma 1, Milano'
        },
        video: {
            ...gridDefaults,
            videoUrl: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            aspectRatio: '16:9',
            autoplay: false
        },
        tabs: {
            ...gridDefaults,
            tabs: [
                { title: 'Tab 1', content: 'Contenuto del primo tab' },
                { title: 'Tab 2', content: 'Contenuto del secondo tab' },
                { title: 'Tab 3', content: 'Contenuto del terzo tab' }
            ]
        },
        countdown: {
            ...gridDefaults,
            targetDate: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            backgroundColor: '#1f2937',
            textColor: '#ffffff'
        },
        header: {
            ...gridDefaults,
            backgroundColor: '#ffffff',
            textColor: '#1f2937',
            logoText: 'Brand',
            logoImage: '',
            sticky: false,
            transparent: false,
            menuItems: [
                { label: 'Home', url: '#' },
                { label: 'Features', url: '#features' },
                { label: 'Pricing', url: '#pricing' },
                { label: 'Contact', url: '#contact' }
            ],
            ctaText: 'Get Started',
            ctaUrl: '#'
        },
        footer: {
            ...gridDefaults,
            backgroundColor: '#1f2937',
            textColor: '#ffffff',
            companyName: 'Company Name',
            companyDescription: 'Building amazing products since 2020.',
            copyrightText: '© ' + new Date().getFullYear() + ' All rights reserved.',
            columns: [
                {
                    title: 'Product',
                    links: [
                        { label: 'Features', url: '#features' },
                        { label: 'Pricing', url: '#pricing' }
                    ]
                },
                {
                    title: 'Company',
                    links: [
                        { label: 'About', url: '#about' },
                        { label: 'Contact', url: '#contact' }
                    ]
                }
            ],
            socialLinks: [
                { platform: 'twitter', url: '#' },
                { platform: 'facebook', url: '#' },
                { platform: 'linkedin', url: '#' }
            ]
        },
        faq: {
            ...gridDefaults,
            backgroundColor: '#ffffff',
            textColor: '#1f2937',
            title: 'Domande Frequenti',
            subtitle: '',
            items: [
                { question: 'Come posso iniziare?', answer: 'Registrati gratuitamente e inizia subito.' },
                { question: 'Quali metodi di pagamento accettate?', answer: 'Carte di credito, PayPal e bonifico.' },
                { question: 'Posso annullare in qualsiasi momento?', answer: 'Sì, senza penali.' }
            ]
        },
        testimonials: {
            ...gridDefaults,
            backgroundColor: '#f9fafb',
            textColor: '#1f2937',
            title: 'Cosa dicono i clienti',
            subtitle: '',
            columns: 3,
            testimonials: [
                { name: 'Maria Rossi', role: 'CEO', text: 'Prodotto eccezionale!', rating: 5, image: '' },
                { name: 'Giuseppe Verdi', role: 'Manager', text: 'Servizio impeccabile.', rating: 5, image: '' },
                { name: 'Anna Bianchi', role: 'Designer', text: 'Facile e potente.', rating: 4, image: '' }
            ]
        },
        pricing: {
            ...gridDefaults,
            backgroundColor: '#ffffff',
            textColor: '#1f2937',
            title: 'Scegli il tuo piano',
            subtitle: 'Prezzi semplici e trasparenti',
            plans: [
                {
                    name: 'Starter',
                    price: '9',
                    period: '/mese',
                    description: 'Perfetto per iniziare',
                    features: ['5 progetti', '10GB storage', 'Supporto email'],
                    cta: 'Inizia gratis',
                    ctaUrl: '#',
                    highlighted: false
                },
                {
                    name: 'Professional',
                    price: '29',
                    period: '/mese',
                    description: 'Per professionisti',
                    features: ['Progetti illimitati', '100GB storage', 'Supporto prioritario', 'Analytics'],
                    cta: 'Prova gratuita',
                    ctaUrl: '#',
                    highlighted: true
                },
                {
                    name: 'Enterprise',
                    price: '99',
                    period: '/mese',
                    description: 'Per grandi team',
                    features: ['Tutto in Pro', 'Storage illimitato', 'Account manager', 'SLA'],
                    cta: 'Contattaci',
                    ctaUrl: '#',
                    highlighted: false
                }
            ]
        }
    };

    return defaults[type] || gridDefaults;
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

    // Griglia contenitore
    canvas.innerHTML = '<div class="grid-container">' + blocks.map(block => {
        const gridCols = block.settings.gridColumns || 12;
        const gridOffset = block.settings.gridOffset || 0;
        const minHeight = block.settings.minHeight || 'auto';
        const animation = block.settings.animation || 'none';
        const animDuration = block.settings.animationDuration || '0.6';
        const animDelay = block.settings.animationDelay || '0';

        return `
        <div class="canvas-block ${animation !== 'none' ? 'has-animation' : ''}"
             draggable="true"
             data-id="${block.id}"
             data-animation="${animation}"
             data-animation-duration="${animDuration}"
             data-animation-delay="${animDelay}"
             onclick="selectBlock('${block.id}')"
             style="grid-column: span ${gridCols}; ${gridOffset > 0 ? `margin-left: calc(${gridOffset} * 100% / 12);` : ''} min-height: ${minHeight};">
            <div class="block-header">
                <span class="block-type">${getBlockIcon(block.type)} ${getBlockLabel(block.type)}</span>
                ${animation !== 'none' ? `<span class="block-anim-info">✨ ${animation}</span>` : ''}
                <span class="block-grid-info">${gridCols}/12 col</span>
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
            <div class="resize-handle resize-handle-right" data-block-id="${block.id}" data-direction="right"></div>
            <div class="resize-handle resize-handle-bottom" data-block-id="${block.id}" data-direction="bottom"></div>
            <div class="resize-handle resize-handle-corner" data-block-id="${block.id}" data-direction="corner"></div>
        </div>
    `}).join('') + '</div>';

    // Inizializza osservatore animazioni
    initAnimationObserver();

    // Re-bind eventi drag
    const canvasBlocks = canvas.querySelectorAll('.canvas-block');
    canvasBlocks.forEach(el => {
        el.addEventListener('click', function(e) {
            if (!e.target.classList.contains('btn-icon') && !e.target.classList.contains('resize-handle')) {
                selectBlock(this.dataset.id);
            }
        });
    });

    // Re-bind resize handles
    initResizeHandlers();
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
        contact: '📧',
        video: '🎬',
        tabs: '📑',
        countdown: '⏱️',
        header: '🔝',
        footer: '🔻',
        faq: '❓',
        testimonials: '💬',
        pricing: '💰'
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
        contact: 'Contatti',
        video: 'Video',
        tabs: 'Tabs',
        countdown: 'Countdown',
        header: 'Header/Navbar',
        footer: 'Footer',
        faq: 'FAQ',
        testimonials: 'Testimonials',
        pricing: 'Pricing'
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
                const gapPx = settings.gap === 'small' ? '5px' : settings.gap === 'large' ? '20px' : '10px';
                preview += `<div style="display: grid; grid-template-columns: repeat(${Math.min(settings.columns, 4)}, 1fr); gap: ${gapPx};">`;
                settings.images.forEach(img => {
                    preview += `<img src="${img}" style="width: 100%; height: 100px; object-fit: cover; border-radius: 4px;">`;
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
        case 'video':
            preview += content;
            if (settings.videoUrl) {
                preview += `<div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px; margin-top: 10px;">
                    <iframe src="${settings.videoUrl}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;" allowfullscreen></iframe>
                </div>`;
            }
            break;
        case 'tabs':
            if (settings.tabs && settings.tabs.length > 0) {
                preview += '<div class="tabs-preview" style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">';
                preview += '<div style="display: flex; background: #f3f4f6; border-bottom: 1px solid #e5e7eb;">';
                settings.tabs.forEach((tab, i) => {
                    preview += `<div style="padding: 8px 16px; ${i === 0 ? 'background: white; border-bottom: 2px solid #3b82f6;' : ''}">${tab.title}</div>`;
                });
                preview += '</div>';
                preview += `<div style="padding: 16px;">${settings.tabs[0].content}</div>`;
                preview += '</div>';
            }
            break;
        case 'countdown':
            preview += content;
            preview += `<div style="display: flex; gap: 16px; justify-content: center; margin-top: 16px; background: ${settings.backgroundColor}; color: ${settings.textColor}; padding: 20px; border-radius: 8px;">
                <div style="text-align: center;"><span style="font-size: 2rem; font-weight: bold;">00</span><br><small>Giorni</small></div>
                <div style="text-align: center;"><span style="font-size: 2rem; font-weight: bold;">00</span><br><small>Ore</small></div>
                <div style="text-align: center;"><span style="font-size: 2rem; font-weight: bold;">00</span><br><small>Min</small></div>
                <div style="text-align: center;"><span style="font-size: 2rem; font-weight: bold;">00</span><br><small>Sec</small></div>
            </div>`;
            break;
        case 'header':
            preview += `<div style="background: ${settings.backgroundColor}; color: ${settings.textColor}; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; border-radius: 8px;">
                <strong>${settings.logoText}</strong>
                <div style="display: flex; gap: 16px; align-items: center;">
                    <span style="opacity: 0.7;">Home</span>
                    <span style="opacity: 0.7;">Features</span>
                    <span style="opacity: 0.7;">Pricing</span>
                    ${settings.ctaText ? `<button style="background: #3b82f6; color: white; border: none; padding: 6px 12px; border-radius: 4px;">${settings.ctaText}</button>` : ''}
                </div>
            </div>`;
            break;
        case 'footer':
            preview += `<div style="background: ${settings.backgroundColor}; color: ${settings.textColor}; padding: 20px; border-radius: 8px;">
                <div style="font-weight: bold; margin-bottom: 8px;">${settings.companyName}</div>
                <div style="font-size: 0.75rem; opacity: 0.7;">${settings.copyrightText}</div>
            </div>`;
            break;
        case 'faq':
            preview += `<div style="background: ${settings.backgroundColor}; color: ${settings.textColor}; padding: 20px; border-radius: 8px;">
                <div style="text-align: center; margin-bottom: 16px;"><strong>${settings.title}</strong></div>
                <div style="border: 1px solid #e5e7eb; border-radius: 4px;">
                    <div style="padding: 10px; border-bottom: 1px solid #e5e7eb; cursor: pointer;">Come posso iniziare? <span style="float: right;">+</span></div>
                    <div style="padding: 10px; border-bottom: 1px solid #e5e7eb; cursor: pointer;">Quali metodi di pagamento? <span style="float: right;">+</span></div>
                    <div style="padding: 10px; cursor: pointer;">Posso annullare? <span style="float: right;">+</span></div>
                </div>
            </div>`;
            break;
        case 'testimonials':
            preview += `<div style="background: ${settings.backgroundColor}; color: ${settings.textColor}; padding: 20px; border-radius: 8px;">
                <div style="text-align: center; margin-bottom: 16px;"><strong>${settings.title}</strong></div>
                <div style="display: grid; grid-template-columns: repeat(${Math.min(settings.columns || 3, 3)}, 1fr); gap: 10px;">
                    ${(settings.testimonials || []).slice(0, 3).map(t => `
                        <div style="background: white; padding: 10px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <div style="color: #fbbf24; margin-bottom: 4px;">★★★★★</div>
                            <div style="font-size: 0.75rem; margin-bottom: 8px;">"${t.text.substring(0, 50)}..."</div>
                            <div style="font-size: 0.7rem; font-weight: bold;">${t.name}</div>
                        </div>
                    `).join('')}
                </div>
            </div>`;
            break;
        case 'pricing':
            preview += `<div style="background: ${settings.backgroundColor}; color: ${settings.textColor}; padding: 20px; border-radius: 8px;">
                <div style="text-align: center; margin-bottom: 16px;"><strong>${settings.title}</strong></div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                    ${(settings.plans || []).slice(0, 3).map(p => `
                        <div style="background: white; padding: 10px; border-radius: 4px; text-align: center; ${p.highlighted ? 'border: 2px solid #3b82f6;' : 'border: 1px solid #e5e7eb;'}">
                            <div style="font-weight: bold; font-size: 0.8rem;">${p.name}</div>
                            <div style="font-size: 1.2rem; font-weight: bold; margin: 8px 0;">€${p.price}</div>
                            <div style="font-size: 0.6rem; opacity: 0.7;">${p.period}</div>
                        </div>
                    `).join('')}
                </div>
            </div>`;
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
    // Controlli griglia comuni a tutti i blocchi
    let html = `
        <div class="property-group">
            <label>Layout Griglia</label>
            <div class="grid-controls">
                <div class="grid-control-item">
                    <label>Larghezza (colonne)</label>
                    <input type="range" id="setting-gridColumns" min="1" max="12" value="${block.settings.gridColumns || 12}"
                           class="form-control range-slider" oninput="updateGridPreview(this)">
                    <span class="range-value">${block.settings.gridColumns || 12}/12</span>
                </div>
                <div class="grid-control-item">
                    <label>Offset (colonne)</label>
                    <input type="range" id="setting-gridOffset" min="0" max="11" value="${block.settings.gridOffset || 0}"
                           class="form-control range-slider" oninput="updateGridPreview(this)">
                    <span class="range-value">${block.settings.gridOffset || 0}</span>
                </div>
                <div class="grid-control-item">
                    <label>Altezza minima</label>
                    <input type="text" id="setting-minHeight" value="${block.settings.minHeight || 'auto'}"
                           class="form-control" placeholder="auto, 200px, 50vh">
                </div>
            </div>
        </div>
        <div class="property-group">
            <label>Animazione on Scroll</label>
            <div class="animation-controls">
                <div class="grid-control-item">
                    <label>Tipo Animazione</label>
                    <select id="setting-animation" class="form-control">
                        <option value="none" ${(block.settings.animation || 'none') === 'none' ? 'selected' : ''}>Nessuna</option>
                        <option value="fade-in" ${block.settings.animation === 'fade-in' ? 'selected' : ''}>Fade In</option>
                        <option value="slide-up" ${block.settings.animation === 'slide-up' ? 'selected' : ''}>Slide Up</option>
                        <option value="slide-down" ${block.settings.animation === 'slide-down' ? 'selected' : ''}>Slide Down</option>
                        <option value="slide-left" ${block.settings.animation === 'slide-left' ? 'selected' : ''}>Slide Left</option>
                        <option value="slide-right" ${block.settings.animation === 'slide-right' ? 'selected' : ''}>Slide Right</option>
                        <option value="zoom-in" ${block.settings.animation === 'zoom-in' ? 'selected' : ''}>Zoom In</option>
                        <option value="zoom-out" ${block.settings.animation === 'zoom-out' ? 'selected' : ''}>Zoom Out</option>
                        <option value="flip" ${block.settings.animation === 'flip' ? 'selected' : ''}>Flip</option>
                        <option value="bounce" ${block.settings.animation === 'bounce' ? 'selected' : ''}>Bounce</option>
                    </select>
                </div>
                <div class="grid-control-item">
                    <label>Durata (secondi)</label>
                    <select id="setting-animationDuration" class="form-control">
                        <option value="0.3" ${block.settings.animationDuration === '0.3' ? 'selected' : ''}>0.3s (Veloce)</option>
                        <option value="0.6" ${(block.settings.animationDuration || '0.6') === '0.6' ? 'selected' : ''}>0.6s (Normale)</option>
                        <option value="1" ${block.settings.animationDuration === '1' ? 'selected' : ''}>1s (Lento)</option>
                        <option value="1.5" ${block.settings.animationDuration === '1.5' ? 'selected' : ''}>1.5s (Molto lento)</option>
                    </select>
                </div>
                <div class="grid-control-item">
                    <label>Ritardo (secondi)</label>
                    <select id="setting-animationDelay" class="form-control">
                        <option value="0" ${(block.settings.animationDelay || '0') === '0' ? 'selected' : ''}>0s</option>
                        <option value="0.1" ${block.settings.animationDelay === '0.1' ? 'selected' : ''}>0.1s</option>
                        <option value="0.2" ${block.settings.animationDelay === '0.2' ? 'selected' : ''}>0.2s</option>
                        <option value="0.3" ${block.settings.animationDelay === '0.3' ? 'selected' : ''}>0.3s</option>
                        <option value="0.5" ${block.settings.animationDelay === '0.5' ? 'selected' : ''}>0.5s</option>
                        <option value="1" ${block.settings.animationDelay === '1' ? 'selected' : ''}>1s</option>
                    </select>
                </div>
                <button type="button" onclick="previewAnimation('${block.id}')" class="btn btn-sm btn-secondary" style="width: 100%; margin-top: 8px;">
                    ▶ Anteprima Animazione
                </button>
            </div>
        </div>
        <div class="property-group"><label>Impostazioni Blocco</label></div>
    `;

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

        case 'gallery':
            html += `
                <div class="property-group">
                    <label>Colonne</label>
                    <select id="setting-columns" class="form-control">
                        <option value="1" ${block.settings.columns == 1 ? 'selected' : ''}>1 colonna</option>
                        <option value="2" ${block.settings.columns == 2 ? 'selected' : ''}>2 colonne</option>
                        <option value="3" ${block.settings.columns == 3 ? 'selected' : ''}>3 colonne</option>
                        <option value="4" ${block.settings.columns == 4 ? 'selected' : ''}>4 colonne</option>
                        <option value="6" ${block.settings.columns == 6 ? 'selected' : ''}>6 colonne</option>
                    </select>
                </div>
                <div class="property-group">
                    <label>Spaziatura</label>
                    <select id="setting-gap" class="form-control">
                        <option value="small" ${block.settings.gap === 'small' ? 'selected' : ''}>Piccola</option>
                        <option value="medium" ${block.settings.gap === 'medium' ? 'selected' : ''}>Media</option>
                        <option value="large" ${block.settings.gap === 'large' ? 'selected' : ''}>Grande</option>
                    </select>
                </div>
                <div class="property-group">
                    <label>Aggiungi Immagini</label>
                    <input type="file" id="gallery-upload" class="form-control" accept="image/*" multiple onchange="uploadGalleryImages(this)">
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

        case 'video':
            html += `
                <div class="property-group">
                    <label>URL Video (YouTube/Vimeo embed)</label>
                    <input type="text" id="setting-videoUrl" value="${block.settings.videoUrl}" class="form-control" placeholder="https://www.youtube.com/embed/...">
                </div>
                <div class="property-group">
                    <label>Aspect Ratio</label>
                    <select id="setting-aspectRatio" class="form-control">
                        <option value="16:9" ${block.settings.aspectRatio === '16:9' ? 'selected' : ''}>16:9</option>
                        <option value="4:3" ${block.settings.aspectRatio === '4:3' ? 'selected' : ''}>4:3</option>
                        <option value="1:1" ${block.settings.aspectRatio === '1:1' ? 'selected' : ''}>1:1</option>
                    </select>
                </div>
            `;
            break;

        case 'countdown':
            html += `
                <div class="property-group">
                    <label>Data Target</label>
                    <input type="date" id="setting-targetDate" value="${block.settings.targetDate}" class="form-control">
                </div>
                <div class="property-group">
                    <label>Colore Sfondo</label>
                    <input type="color" id="setting-backgroundColor" value="${block.settings.backgroundColor}" class="form-control">
                </div>
                <div class="property-group">
                    <label>Colore Testo</label>
                    <input type="color" id="setting-textColor" value="${block.settings.textColor}" class="form-control">
                </div>
            `;
            break;

        case 'header':
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
                    <label>Testo Logo</label>
                    <input type="text" id="setting-logoText" value="${block.settings.logoText}" class="form-control">
                </div>
                <div class="property-group">
                    <label>Testo CTA</label>
                    <input type="text" id="setting-ctaText" value="${block.settings.ctaText}" class="form-control">
                </div>
                <div class="property-group">
                    <label>URL CTA</label>
                    <input type="text" id="setting-ctaUrl" value="${block.settings.ctaUrl}" class="form-control">
                </div>
                <div class="property-group">
                    <label>
                        <input type="checkbox" id="setting-sticky" ${block.settings.sticky ? 'checked' : ''}>
                        Sticky Header
                    </label>
                </div>
            `;
            break;

        case 'footer':
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
                    <label>Nome Azienda</label>
                    <input type="text" id="setting-companyName" value="${block.settings.companyName}" class="form-control">
                </div>
                <div class="property-group">
                    <label>Descrizione</label>
                    <textarea id="setting-companyDescription" class="form-control" rows="2">${block.settings.companyDescription}</textarea>
                </div>
                <div class="property-group">
                    <label>Testo Copyright</label>
                    <input type="text" id="setting-copyrightText" value="${block.settings.copyrightText}" class="form-control">
                </div>
            `;
            break;

        case 'faq':
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
                    <label>Titolo</label>
                    <input type="text" id="setting-title" value="${block.settings.title}" class="form-control">
                </div>
                <div class="property-group">
                    <label>Sottotitolo</label>
                    <input type="text" id="setting-subtitle" value="${block.settings.subtitle || ''}" class="form-control">
                </div>
            `;
            break;

        case 'testimonials':
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
                    <label>Titolo</label>
                    <input type="text" id="setting-title" value="${block.settings.title}" class="form-control">
                </div>
                <div class="property-group">
                    <label>Colonne</label>
                    <select id="setting-columns" class="form-control">
                        <option value="1" ${block.settings.columns == 1 ? 'selected' : ''}>1 colonna</option>
                        <option value="2" ${block.settings.columns == 2 ? 'selected' : ''}>2 colonne</option>
                        <option value="3" ${block.settings.columns == 3 ? 'selected' : ''}>3 colonne</option>
                    </select>
                </div>
            `;
            break;

        case 'pricing':
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
                    <label>Titolo</label>
                    <input type="text" id="setting-title" value="${block.settings.title}" class="form-control">
                </div>
                <div class="property-group">
                    <label>Sottotitolo</label>
                    <input type="text" id="setting-subtitle" value="${block.settings.subtitle || ''}" class="form-control">
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

    saveToHistory();
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
        saveToHistory();
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
        saveToHistory();
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
        saveToHistory();
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
        saveToHistory();
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
    formData.append('csrf_token', CSRF_TOKEN);

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
 * Upload multiple immagini per galleria
 */
function uploadGalleryImages(input) {
    if (!input.files || input.files.length === 0) return;
    if (!selectedBlockId) return;

    const block = blocks.find(b => b.id === selectedBlockId);
    if (!block || block.type !== 'gallery') return;

    const uploads = [];
    const totalFiles = input.files.length;
    let completedUploads = 0;

    for (let i = 0; i < input.files.length; i++) {
        const formData = new FormData();
        formData.append('image', input.files[i]);

        uploads.push(
            fetch('?action=api_upload', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (!block.settings.images) {
                        block.settings.images = [];
                    }
                    block.settings.images.push(data.url);
                    completedUploads++;
                }
                return data;
            })
        );
    }

    Promise.all(uploads).then(() => {
        renderCanvas();
        showProperties(block);
        autoSave();
        showNotification(`${completedUploads} immagini caricate!`);
    }).catch(err => {
        alert('Errore nel caricamento delle immagini');
    });

    // Reset input
    input.value = '';
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

                // Carica tema se salvato nei blocchi
                if (data.page.theme) {
                    currentTheme = data.page.theme;
                    const themeSelector = document.getElementById('theme-selector');
                    if (themeSelector) {
                        themeSelector.value = currentTheme;
                    }
                    applyTheme(currentTheme);
                }

                // Salva stato iniziale per undo
                saveToHistory();
                renderCanvas();
                updateUndoRedoButtons();
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
    const themeSelector = document.getElementById('theme-selector');
    const theme = themeSelector ? themeSelector.value : 'light';

    const formData = new FormData();
    formData.append('id', PAGE_ID);
    formData.append('title', title);
    formData.append('blocks', JSON.stringify(blocks));
    formData.append('ui_library', uiLibrary);
    formData.append('theme', theme);
    formData.append('csrf_token', CSRF_TOKEN);

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
    const title = document.getElementById('page-title').value;
    const uiLibrary = document.getElementById('ui-library').value;

    // Crea form per POST
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `?action=api_preview&id=${PAGE_ID}`;
    form.target = '_blank';

    // Aggiungi campi nascosti
    const fields = {
        id: PAGE_ID,
        title: title,
        blocks: JSON.stringify(blocks),
        ui_library: uiLibrary
    };

    for (const [key, value] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }

    // Invia form
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

/**
 * Mostra notifica toast migliorata
 */
function showNotification(message, type = 'info') {
    // Crea container se non esiste
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    // Icone per tipo
    const icons = {
        success: '<svg viewBox="0 0 24 24" width="20" height="20"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" fill="none"/></svg>',
        error: '<svg viewBox="0 0 24 24" width="20" height="20"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" fill="none"/></svg>',
        warning: '<svg viewBox="0 0 24 24" width="20" height="20"><path d="M12 9v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" stroke="currentColor" stroke-width="2" fill="none"/></svg>',
        info: '<svg viewBox="0 0 24 24" width="20" height="20"><path d="M12 16v-4m0-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" stroke="currentColor" stroke-width="2" fill="none"/></svg>'
    };

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <span class="toast-icon">${icons[type] || icons.info}</span>
        <span class="toast-message">${message}</span>
        <div class="toast-progress"></div>
    `;

    container.appendChild(toast);

    // Auto remove after 3s
    setTimeout(() => {
        toast.style.animation = 'slideIn 0.3s ease reverse';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Shorthand per toast success
 */
function showSuccess(message) {
    showNotification(message, 'success');
}

/**
 * Shorthand per toast error
 */
function showError(message) {
    showNotification(message, 'error');
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

/**
 * Inizializza selettore temi
 */
function initThemeSelector() {
    const themeSelector = document.getElementById('theme-selector');
    if (!themeSelector) return;

    themeSelector.addEventListener('change', function() {
        applyTheme(this.value);
        autoSave();
    });
}

/**
 * Applica tema alla pagina
 */
function applyTheme(themeName) {
    currentTheme = themeName;
    const theme = themes[themeName];
    if (!theme) return;

    const root = document.documentElement;
    root.style.setProperty('--theme-primary', theme.primary);
    root.style.setProperty('--theme-secondary', theme.secondary);
    root.style.setProperty('--theme-background', theme.background);
    root.style.setProperty('--theme-surface', theme.surface);
    root.style.setProperty('--theme-text', theme.text);
    root.style.setProperty('--theme-text-muted', theme.textMuted);
    root.style.setProperty('--theme-border', theme.border);
    root.style.setProperty('--theme-accent', theme.accent);

    // Applica tema al canvas
    const canvas = document.getElementById('canvas');
    if (canvas) {
        canvas.style.backgroundColor = theme.surface;
        canvas.setAttribute('data-theme', themeName);
    }

    // Mostra notifica
    showNotification(`Tema: ${theme.name}`);
}

/**
 * Inizializza handlers per ridimensionamento
 */
let resizeInitialized = false;
function initResizeHandlers() {
    // Aggiungi listener globali solo una volta
    if (!resizeInitialized) {
        document.addEventListener('mousemove', doResize);
        document.addEventListener('mouseup', stopResize);
        resizeInitialized = true;
    }

    // Rimuovi vecchi listener e aggiungi nuovi per gli handle
    const handles = document.querySelectorAll('.resize-handle');
    handles.forEach(handle => {
        // Clona per rimuovere vecchi listener
        const newHandle = handle.cloneNode(true);
        handle.parentNode.replaceChild(newHandle, handle);
        newHandle.addEventListener('mousedown', startResize);
    });
}

/**
 * Avvia ridimensionamento
 */
function startResize(e) {
    e.preventDefault();
    e.stopPropagation();

    isResizing = true;
    resizeBlockId = e.target.dataset.blockId;
    resizeDirection = e.target.dataset.direction;

    const blockEl = document.querySelector(`.canvas-block[data-id="${resizeBlockId}"]`);
    if (blockEl) {
        blockEl.classList.add('resizing');
        resizeStartX = e.clientX;
        resizeStartY = e.clientY;
        resizeStartWidth = blockEl.offsetWidth;
        resizeStartHeight = blockEl.offsetHeight;
    }
}

let resizeDirection = null;
let resizeStartX = 0;
let resizeStartY = 0;
let resizeStartWidth = 0;
let resizeStartHeight = 0;

/**
 * Esegue ridimensionamento
 */
function doResize(e) {
    if (!isResizing || !resizeBlockId) return;

    const block = blocks.find(b => b.id === resizeBlockId);
    if (!block) return;

    const canvas = document.getElementById('canvas');
    const gridContainer = canvas.querySelector('.grid-container');
    if (!gridContainer) return;

    const containerWidth = gridContainer.offsetWidth;
    const colWidth = containerWidth / 12;

    if (resizeDirection === 'right' || resizeDirection === 'corner') {
        const deltaX = e.clientX - resizeStartX;
        const newWidth = resizeStartWidth + deltaX;
        const newCols = Math.round(newWidth / colWidth);
        const clampedCols = Math.max(1, Math.min(12, newCols));

        if (clampedCols !== block.settings.gridColumns) {
            block.settings.gridColumns = clampedCols;
            renderCanvas();
        }
    }

    if (resizeDirection === 'bottom' || resizeDirection === 'corner') {
        const deltaY = e.clientY - resizeStartY;
        const newHeight = Math.max(100, resizeStartHeight + deltaY);
        block.settings.minHeight = newHeight + 'px';

        const blockEl = document.querySelector(`.canvas-block[data-id="${resizeBlockId}"]`);
        if (blockEl) {
            blockEl.style.minHeight = newHeight + 'px';
        }
    }
}

/**
 * Ferma ridimensionamento
 */
function stopResize(e) {
    if (!isResizing) return;

    const blockEl = document.querySelector(`.canvas-block[data-id="${resizeBlockId}"]`);
    if (blockEl) {
        blockEl.classList.remove('resizing');
    }

    isResizing = false;
    resizeBlockId = null;
    resizeDirection = null;

    renderCanvas();
    autoSave();
}

/**
 * Aggiorna preview griglia in tempo reale
 */
function updateGridPreview(input) {
    const valueSpan = input.nextElementSibling;
    if (input.id === 'setting-gridColumns') {
        valueSpan.textContent = input.value + '/12';
    } else {
        valueSpan.textContent = input.value;
    }
}

/**
 * Ottiene i dati del tema corrente per il salvataggio
 */
function getThemeData() {
    return {
        theme: currentTheme,
        colors: themes[currentTheme]
    };
}

/**
 * Salva stato nella cronologia per Undo/Redo
 */
function saveToHistory() {
    // Rimuovi stati futuri se siamo nel mezzo della cronologia
    if (historyIndex < history.length - 1) {
        history = history.slice(0, historyIndex + 1);
    }

    // Aggiungi stato corrente
    history.push(JSON.stringify(blocks));

    // Limita dimensione cronologia
    if (history.length > MAX_HISTORY) {
        history.shift();
    } else {
        historyIndex++;
    }

    updateUndoRedoButtons();
}

/**
 * Undo - Annulla ultima modifica
 */
function undo() {
    if (historyIndex > 0) {
        historyIndex--;
        blocks = JSON.parse(history[historyIndex]);
        renderCanvas();
        updateUndoRedoButtons();
        showNotification('Azione annullata');
    }
}

/**
 * Redo - Ripristina modifica annullata
 */
function redo() {
    if (historyIndex < history.length - 1) {
        historyIndex++;
        blocks = JSON.parse(history[historyIndex]);
        renderCanvas();
        updateUndoRedoButtons();
        showNotification('Azione ripristinata');
    }
}

/**
 * Aggiorna stato bottoni Undo/Redo
 */
function updateUndoRedoButtons() {
    const undoBtn = document.getElementById('btn-undo');
    const redoBtn = document.getElementById('btn-redo');

    if (undoBtn) {
        undoBtn.disabled = historyIndex <= 0;
        undoBtn.style.opacity = historyIndex <= 0 ? '0.5' : '1';
    }
    if (redoBtn) {
        redoBtn.disabled = historyIndex >= history.length - 1;
        redoBtn.style.opacity = historyIndex >= history.length - 1 ? '0.5' : '1';
    }
}

/**
 * Imposta viewport per preview responsive
 */
function setViewport(viewport) {
    const canvas = document.getElementById('canvas');
    const gridContainer = canvas.querySelector('.grid-container');

    // Rimuovi classi viewport precedenti
    canvas.classList.remove('viewport-desktop', 'viewport-tablet', 'viewport-mobile');

    // Aggiungi nuova classe viewport
    canvas.classList.add(`viewport-${viewport}`);

    // Aggiorna bottoni attivi
    document.querySelectorAll('.responsive-buttons button').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById(`btn-${viewport}`).classList.add('active');

    // Mostra notifica
    const labels = {
        desktop: 'Desktop (100%)',
        tablet: 'Tablet (768px)',
        mobile: 'Mobile (375px)'
    };
    showNotification(`Preview: ${labels[viewport]}`);
}

/**
 * Inizializza keyboard shortcuts
 */
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Z = Undo
        if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) {
            e.preventDefault();
            undo();
        }
        // Ctrl/Cmd + Shift + Z = Redo
        if ((e.ctrlKey || e.metaKey) && e.key === 'z' && e.shiftKey) {
            e.preventDefault();
            redo();
        }
        // Ctrl/Cmd + Y = Redo (alternativo)
        if ((e.ctrlKey || e.metaKey) && e.key === 'y') {
            e.preventDefault();
            redo();
        }
        // Ctrl/Cmd + S = Salva
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            savePage();
        }
        // Delete/Backspace = Elimina blocco selezionato
        if ((e.key === 'Delete' || e.key === 'Backspace') && selectedBlockId) {
            // Solo se non siamo in un input/textarea
            if (!['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
                e.preventDefault();
                deleteBlock(selectedBlockId);
            }
        }
        // Escape = Deseleziona
        if (e.key === 'Escape') {
            closeProperties();
        }
    });
}

/**
 * Inizializza l'observer per le animazioni on scroll
 */
function initAnimationObserver() {
    // Disconnetti observer precedente se esiste
    if (animationObserver) {
        animationObserver.disconnect();
    }

    // Crea nuovo observer
    animationObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                const animation = element.dataset.animation;

                if (animation && animation !== 'none') {
                    const duration = element.dataset.animationDuration || '0.6';
                    const delay = element.dataset.animationDelay || '0';

                    element.style.animationDuration = duration + 's';
                    element.style.animationDelay = delay + 's';
                    element.classList.add('animate-' + animation);
                    element.classList.add('animated');
                }
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    // Osserva tutti i blocchi con animazione
    const animatedBlocks = document.querySelectorAll('.canvas-block.has-animation');
    animatedBlocks.forEach(block => {
        // Reset animazione per permettere replay
        block.classList.remove('animated');
        block.classList.remove(
            'animate-fade-in', 'animate-slide-up', 'animate-slide-down',
            'animate-slide-left', 'animate-slide-right', 'animate-zoom-in',
            'animate-zoom-out', 'animate-flip', 'animate-bounce'
        );
        animationObserver.observe(block);
    });
}

/**
 * Preview animazione di un blocco
 */
function previewAnimation(blockId) {
    const blockEl = document.querySelector(`.canvas-block[data-id="${blockId}"]`);
    if (!blockEl) return;

    const block = blocks.find(b => b.id === blockId);
    if (!block) return;

    // Ottieni valori correnti dai controlli
    const animSelect = document.getElementById('setting-animation');
    const durationSelect = document.getElementById('setting-animationDuration');
    const delaySelect = document.getElementById('setting-animationDelay');

    const animation = animSelect ? animSelect.value : block.settings.animation;
    const duration = durationSelect ? durationSelect.value : block.settings.animationDuration;
    const delay = delaySelect ? delaySelect.value : block.settings.animationDelay;

    if (!animation || animation === 'none') {
        showNotification('Seleziona un\'animazione');
        return;
    }

    // Rimuovi tutte le classi di animazione
    blockEl.classList.remove('animated');
    blockEl.classList.remove(
        'animate-fade-in', 'animate-slide-up', 'animate-slide-down',
        'animate-slide-left', 'animate-slide-right', 'animate-zoom-in',
        'animate-zoom-out', 'animate-flip', 'animate-bounce'
    );

    // Forza reflow per reset animazione
    void blockEl.offsetWidth;

    // Applica animazione
    blockEl.style.animationDuration = duration + 's';
    blockEl.style.animationDelay = delay + 's';
    blockEl.classList.add('animate-' + animation);
    blockEl.classList.add('animated');

    showNotification(`Preview: ${animation}`);
}

/**
 * Resetta animazioni per tutti i blocchi (utile per replay)
 */
function resetAnimations() {
    const animatedBlocks = document.querySelectorAll('.canvas-block.has-animation');
    animatedBlocks.forEach(block => {
        block.classList.remove('animated');
        block.classList.remove(
            'animate-fade-in', 'animate-slide-up', 'animate-slide-down',
            'animate-slide-left', 'animate-slide-right', 'animate-zoom-in',
            'animate-zoom-out', 'animate-flip', 'animate-bounce'
        );
    });

    // Re-inizializza observer
    initAnimationObserver();
    showNotification('Animazioni resettate');
}

/**
 * Mostra modal di export
 */
function showExportModal(id) {
    // Crea modal se non esiste
    let modal = document.getElementById('export-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'export-modal';
        modal.className = 'modal';
        modal.style.display = 'none';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Esporta Pagina</h3>
                    <button onclick="closeExportModal()" class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Scegli il formato di esportazione:</p>
                    <div class="export-options">
                        <button onclick="doExport('html')" class="export-option">
                            <span class="export-icon">🌐</span>
                            <span class="export-title">HTML Standard</span>
                            <span class="export-desc">Esporta nella cartella root</span>
                        </button>
                        <button onclick="doExport('minified')" class="export-option">
                            <span class="export-icon">⚡</span>
                            <span class="export-title">HTML Minificato</span>
                            <span class="export-desc">HTML ottimizzato e compresso</span>
                        </button>
                        <button onclick="doExport('zip')" class="export-option">
                            <span class="export-icon">📦</span>
                            <span class="export-title">Download ZIP</span>
                            <span class="export-desc">Pacchetto completo con assets</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        // Chiudi su click esterno
        modal.addEventListener('click', function(e) {
            if (e.target === this) closeExportModal();
        });
    }

    window.currentExportId = id || PAGE_ID;
    modal.style.display = 'flex';
}

/**
 * Chiudi modal export
 */
function closeExportModal() {
    const modal = document.getElementById('export-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Esegui export
 */
function doExport(format) {
    const id = window.currentExportId || PAGE_ID;
    window.location.href = `?action=export&id=${id}&format=${format}`;
    closeExportModal();
}

/**
 * Toggle Dark Mode
 */
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    
    // Salva preferenza in localStorage
    const isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('spaspb-dark-mode', isDark ? 'true' : 'false');
    
    // Aggiorna icona del bottone
    const btn = document.getElementById('btn-dark-mode');
    if (btn) {
        if (isDark) {
            btn.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="5"/>
                <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
            </svg>`;
        } else {
            btn.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>`;
        }
    }
    
    showNotification(isDark ? 'Dark mode attivato' : 'Light mode attivato', 'info');
}

/**
 * Carica preferenza dark mode da localStorage
 */
function loadDarkModePreference() {
    const isDark = localStorage.getItem('spaspb-dark-mode') === 'true';
    if (isDark) {
        document.body.classList.add('dark-mode');
        const btn = document.getElementById('btn-dark-mode');
        if (btn) {
            btn.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="5"/>
                <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
            </svg>`;
        }
    }
}

// Carica preferenza dark mode all'avvio
document.addEventListener('DOMContentLoaded', loadDarkModePreference);

/**
 * Abilita inline editing per un blocco
 */
function enableInlineEdit(blockId) {
    const blockEl = document.querySelector(`.canvas-block[data-id="${blockId}"]`);
    if (!blockEl) return;

    const contentEl = blockEl.querySelector('.block-content');
    if (!contentEl) return;

    // Trova il blocco nei dati
    const block = blocks.find(b => b.id === blockId);
    if (!block) return;

    // Non permettere inline edit per alcuni tipi
    const noInlineEdit = ['image', 'gallery', 'video', 'countdown'];
    if (noInlineEdit.includes(block.type)) {
        showNotification('Usa il pannello proprietà per questo blocco', 'info');
        return;
    }

    // Abilita editing
    contentEl.setAttribute('contenteditable', 'true');
    contentEl.classList.add('block-content-editable');
    blockEl.classList.add('editing');
    contentEl.focus();

    // Seleziona tutto il contenuto
    const range = document.createRange();
    range.selectNodeContents(contentEl);
    const sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(range);

    // Crea toolbar inline
    createInlineToolbar(blockEl);

    // Event listeners
    contentEl.addEventListener('blur', function onBlur() {
        saveInlineEdit(blockId, contentEl.innerHTML);
        disableInlineEdit(blockEl, contentEl);
        contentEl.removeEventListener('blur', onBlur);
    });

    contentEl.addEventListener('keydown', function onKeydown(e) {
        if (e.key === 'Escape') {
            // Annulla modifiche
            contentEl.innerHTML = block.content;
            disableInlineEdit(blockEl, contentEl);
            contentEl.removeEventListener('keydown', onKeydown);
            e.preventDefault();
        }
        if (e.key === 'Enter' && !e.shiftKey) {
            // Salva con Enter (Shift+Enter per nuovo paragrafo)
            contentEl.blur();
            e.preventDefault();
        }
    });
}

/**
 * Disabilita inline editing
 */
function disableInlineEdit(blockEl, contentEl) {
    contentEl.removeAttribute('contenteditable');
    contentEl.classList.remove('block-content-editable');
    blockEl.classList.remove('editing');

    // Rimuovi toolbar
    const toolbar = blockEl.querySelector('.inline-toolbar');
    if (toolbar) toolbar.remove();
}

/**
 * Salva contenuto inline
 */
function saveInlineEdit(blockId, newContent) {
    const block = blocks.find(b => b.id === blockId);
    if (!block) return;

    if (block.content !== newContent) {
        block.content = newContent;
        saveToHistory();
        autoSave();
        showNotification('Contenuto salvato', 'success');
    }
}

/**
 * Crea toolbar inline per formattazione
 */
function createInlineToolbar(blockEl) {
    // Rimuovi toolbar esistente
    const existing = blockEl.querySelector('.inline-toolbar');
    if (existing) existing.remove();

    const toolbar = document.createElement('div');
    toolbar.className = 'inline-toolbar';
    toolbar.innerHTML = `
        <button onclick="formatText('bold')" title="Grassetto (Ctrl+B)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/>
                <path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/>
            </svg>
        </button>
        <button onclick="formatText('italic')" title="Corsivo (Ctrl+I)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="4" x2="10" y2="4"/>
                <line x1="14" y1="20" x2="5" y2="20"/>
                <line x1="15" y1="4" x2="9" y2="20"/>
            </svg>
        </button>
        <button onclick="formatText('underline')" title="Sottolineato (Ctrl+U)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 3v7a6 6 0 0 0 6 6 6 6 0 0 0 6-6V3"/>
                <line x1="4" y1="21" x2="20" y2="21"/>
            </svg>
        </button>
        <button onclick="formatText('insertUnorderedList')" title="Lista puntata">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="8" y1="6" x2="21" y2="6"/>
                <line x1="8" y1="12" x2="21" y2="12"/>
                <line x1="8" y1="18" x2="21" y2="18"/>
                <line x1="3" y1="6" x2="3.01" y2="6"/>
                <line x1="3" y1="12" x2="3.01" y2="12"/>
                <line x1="3" y1="18" x2="3.01" y2="18"/>
            </svg>
        </button>
    `;

    blockEl.appendChild(toolbar);
}

/**
 * Formatta testo selezionato
 */
function formatText(command) {
    document.execCommand(command, false, null);
}

// Aggiungi double-click per inline editing
document.addEventListener('dblclick', function(e) {
    const blockEl = e.target.closest('.canvas-block');
    if (blockEl) {
        const blockId = blockEl.dataset.id;
        enableInlineEdit(blockId);
    }
});
