/**
 * ResearchFlow - Main JavaScript
 */

// Modal System
const Modal = {
    show: function(title, message, buttons = [{text: 'OK', className: 'btn-primary'}]) {
        // Create modal HTML
        const modalHTML = `
            <div class="modal-overlay active" id="customModal">
                <div class="modal">
                    <div class="modal-header">
                        <h3>${title}</h3>
                        <button class="modal-close" onclick="Modal.hide()">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        ${buttons.map(btn => `
                            <button class="btn ${btn.className || 'btn-primary'}"
                                    onclick="${btn.onClick || 'Modal.hide()'}">
                                ${btn.text}
                            </button>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal
        const existingModal = document.getElementById('customModal');
        if (existingModal) existingModal.remove();

        // Add new modal
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Close on overlay click
        const overlay = document.getElementById('customModal');
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                Modal.hide();
            }
        });
    },

    hide: function() {
        const modal = document.getElementById('customModal');
        if (modal) {
            modal.classList.remove('active');
            setTimeout(() => modal.remove(), 300);
        }
    },

    alert: function(message, title = 'Notice') {
        this.show(title, message);
    },

    confirm: function(message, onConfirm, title = 'Confirm') {
        this.show(title, message, [
            {text: 'Cancel', className: 'btn-secondary', onClick: 'Modal.hide()'},
            {text: 'Confirm', className: 'btn-primary', onClick: `${onConfirm}; Modal.hide()`}
        ]);
    }
};

// Make Modal globally available
window.Modal = Modal;

// Navbar scroll effect
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
});

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navMenu = document.querySelector('.nav-menu');

    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            this.classList.toggle('active');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!mobileMenuToggle.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
                mobileMenuToggle.classList.remove('active');
            }
        });
    }

    // Auto-hide flash messages after 5 seconds
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(function(message) {
        setTimeout(function() {
            message.style.transition = 'opacity 0.3s';
            message.style.opacity = '0';
            setTimeout(function() {
                message.remove();
            }, 300);
        }, 5000);
    });
});
