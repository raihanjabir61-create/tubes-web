// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. NAVBAR SCROLL EFFECT
    const navbar = document.querySelector('header.navbar-container');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 20) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // 2. MOBILE NAVIGATION TOGGLE
    const navToggle = document.querySelector('.mobile-nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            const icon = navToggle.querySelector('i');
            if (icon) {
                if (navMenu.classList.contains('active')) {
                    icon.className = 'fas fa-times';
                } else {
                    icon.className = 'fas fa-bars';
                }
            }
        });
    }

    // 3. BOOK COVER LIGHTBOX MODAL SETUP
    // Create and append modal markup dynamically to body if not already present
    let modalOverlay = document.getElementById('lightbox-modal');
    if (!modalOverlay) {
        modalOverlay = document.createElement('div');
        modalOverlay.id = 'lightbox-modal';
        modalOverlay.className = 'modal-overlay';
        modalOverlay.innerHTML = `
            <div class="modal-content">
                <button type="button" class="modal-close" id="lightbox-close">&times;</button>
                <div class="modal-body">
                    <img id="lightbox-img" src="" alt="Book Cover Large">
                    <h3 id="lightbox-title" style="margin-top: 1rem; text-align: center;"></h3>
                    <p id="lightbox-meta" style="color: var(--text-muted); font-size: 0.9rem; text-align: center;"></p>
                </div>
            </div>
        `;
        document.body.appendChild(modalOverlay);
    }

    const lightboxClose = document.getElementById('lightbox-close');
    const lightboxImg = document.getElementById('lightbox-img');
    const lightboxTitle = document.getElementById('lightbox-title');
    const lightboxMeta = document.getElementById('lightbox-meta');

    // Attach click listener to close modal
    if (lightboxClose && modalOverlay) {
        const closeModal = function() {
            modalOverlay.classList.remove('active');
        };
        lightboxClose.addEventListener('click', closeModal);
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === modalOverlay) {
                closeModal();
            }
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modalOverlay.classList.contains('active')) {
                closeModal();
            }
        });
    }

    // Export showLightbox global function
    window.showLightbox = function(imgSrc, titleText = '', metaText = '') {
        if (modalOverlay && lightboxImg) {
            lightboxImg.src = imgSrc;
            if (lightboxTitle) lightboxTitle.textContent = titleText;
            if (lightboxMeta) lightboxMeta.textContent = metaText;
            modalOverlay.classList.add('active');
        }
    };

    // Automatically bind to elements with 'data-lightbox' attribute
    document.body.addEventListener('click', function(e) {
        const target = e.target.closest('[data-lightbox]');
        if (target) {
            e.preventDefault();
            const imgSrc = target.getAttribute('href') || target.getAttribute('src') || target.getAttribute('data-lightbox-src');
            const title = target.getAttribute('data-title') || '';
            const meta = target.getAttribute('data-meta') || '';
            if (imgSrc) {
                window.showLightbox(imgSrc, title, meta);
            }
        }
    });

    // 4. DYNAMIC DRAG AND DROP FILE PREVIEW
    const fileWrapper = document.querySelector('.file-upload-wrapper');
    const fileInput = document.querySelector('.file-upload-input');
    const filePreview = document.querySelector('.file-upload-preview');
    const uploadContent = document.querySelector('.file-upload-content');
    
    if (fileInput && fileWrapper) {
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileWrapper.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Highlight drop zone
        ['dragenter', 'dragover'].forEach(eventName => {
            fileWrapper.addEventListener(eventName, () => fileWrapper.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileWrapper.addEventListener(eventName, () => fileWrapper.classList.remove('dragover'), false);
        });

        // Handle dropped files
        fileWrapper.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length) {
                fileInput.files = files;
                handleFileSelect(fileInput);
            }
        });

        // Handle file selection via browse
        fileInput.addEventListener('change', function() {
            handleFileSelect(fileInput);
        });

        function handleFileSelect(input) {
            const file = input.files[0];
            if (file) {
                // Check if it's an image
                if (!file.type.match('image.*')) {
                    window.showToast('File harus berupa gambar (JPG, PNG, WebP)!', 'error');
                    input.value = '';
                    return;
                }
                // Size validation: max 2MB
                if (file.size > 2 * 1024 * 1024) {
                    window.showToast('Ukuran file maksimal 2MB!', 'error');
                    input.value = '';
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (filePreview) {
                        filePreview.src = e.target.result;
                        filePreview.style.display = 'block';
                    }
                    if (uploadContent) {
                        const fileHint = uploadContent.querySelector('.file-upload-hint');
                        const fileText = uploadContent.querySelector('.file-upload-text');
                        if (fileText) fileText.textContent = file.name;
                        if (fileHint) fileHint.textContent = `(${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                    }
                };
                reader.readAsDataURL(file);
            }
        }
    }
});

// 5. TOAST NOTIFICATION UTILITY
window.showToast = function(message, type = 'success') {
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    // Get appropriate icon
    let iconClass = 'fas fa-check-circle';
    if (type === 'error') iconClass = 'fas fa-times-circle';
    if (type === 'warning') iconClass = 'fas fa-exclamation-circle';
    if (type === 'info') iconClass = 'fas fa-info-circle';
    
    toast.innerHTML = `
        <i class="${iconClass}" style="font-size: 1.25rem;"></i>
        <div class="toast-message">${message}</div>
    `;
    
    toastContainer.appendChild(toast);
    
    // Remove after 4 seconds
    setTimeout(() => {
        toast.classList.add('fade-out');
        toast.addEventListener('animationend', () => {
            toast.remove();
        });
    }, 4000);
};
