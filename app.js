/**
 * Mazvikadei Resort - Enhanced JavaScript Functionality
 * Modern, responsive resort management system
 */

// Global variables
let selectedItems = [];
let currentUser = null;
let bookingData = null;

// Initialize application
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
    setupEventListeners();
    loadUserPreferences();
    setupNotifications();
});

/**
 * Initialize the application
 */
function initializeApp() {
    // Set current year
    const yearElement = document.getElementById('year');
    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }
    
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize lazy loading
    initializeLazyLoading();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Load pending booking data
    loadPendingBooking();
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Navigation
    setupNavigation();
    
    // Forms
    setupFormHandlers();
    
    // Booking system
    setupBookingSystem();
    
    // Search functionality
    setupSearchFunctionality();
    
    // Modal handlers
    setupModalHandlers();
    
    // Image galleries
    setupImageGalleries();
}

/**
 * Navigation functionality
 */
function setupNavigation() {
    const navLinks = document.querySelectorAll('.nav a');
    const currentPage = window.location.pathname.split('/').pop();
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && href.includes(currentPage)) {
            link.classList.add('active');
        }
        
        // Smooth scrolling for anchor links
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });
    
    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            this.classList.toggle('active');
        });
    }
}

/**
 * Form handling and validation
 */
function setupFormHandlers() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                return false;
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
    });
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    // Add validation rules to forms
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateEmail(this);
        });
    });
    
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('blur', function() {
            validatePhone(this);
        });
    });
    
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            validateDate(this);
        });
    });
}

/**
 * Validate form
 */
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Validate individual field
 */
function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute('required');
    
    clearFieldError(field);
    
    if (required && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    if (value) {
        switch (type) {
            case 'email':
                return validateEmail(field);
            case 'tel':
                return validatePhone(field);
            case 'date':
                return validateDate(field);
            case 'number':
                return validateNumber(field);
        }
    }
    
    return true;
}

/**
 * Validate email
 */
function validateEmail(field) {
    const email = field.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email && !emailRegex.test(email)) {
        showFieldError(field, 'Please enter a valid email address');
        return false;
    }
    
    return true;
}

/**
 * Validate phone number
 */
function validatePhone(field) {
    const phone = field.value.trim();
    const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
    
    if (phone && !phoneRegex.test(phone)) {
        showFieldError(field, 'Please enter a valid phone number');
        return false;
    }
    
    return true;
}

/**
 * Validate date
 */
function validateDate(field) {
    const date = new Date(field.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (field.value && date < today) {
        showFieldError(field, 'Date cannot be in the past');
        return false;
    }
    
    return true;
}

/**
 * Validate number
 */
function validateNumber(field) {
    const value = parseFloat(field.value);
    const min = field.getAttribute('min');
    const max = field.getAttribute('max');
    
    if (isNaN(value)) {
        showFieldError(field, 'Please enter a valid number');
        return false;
    }
    
    if (min && value < parseFloat(min)) {
        showFieldError(field, `Value must be at least ${min}`);
        return false;
    }
    
    if (max && value > parseFloat(max)) {
        showFieldError(field, `Value must be no more than ${max}`);
        return false;
    }
    
    return true;
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    clearFieldError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.color = 'var(--danger)';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    
    field.parentNode.appendChild(errorDiv);
    field.style.borderColor = 'var(--danger)';
}

/**
 * Clear field error
 */
function clearFieldError(field) {
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
    field.style.borderColor = '';
}

/**
 * Booking system functionality
 */
function setupBookingSystem() {
    // Load pending booking data
    loadPendingBooking();
    
    // Setup item selection
    setupItemSelection();
    
    // Setup quantity controls
    setupQuantityControls();
    
    // Setup payment methods
    setupPaymentMethods();
}

/**
 * Load pending booking data
 */
function loadPendingBooking() {
    const pendingBooking = localStorage.getItem('pendingBooking');
    if (pendingBooking) {
        try {
            bookingData = JSON.parse(pendingBooking);
            selectedItems = bookingData.items || [];
            displaySelectedItems();
            calculateTotal();
        } catch (e) {
            console.error('Error parsing booking data:', e);
            localStorage.removeItem('pendingBooking');
        }
    }
}

/**
 * Setup item selection
 */
function setupItemSelection() {
    const itemCards = document.querySelectorAll('.item-card, .room-card, .activity-card, .event-card');
    
    itemCards.forEach(card => {
        card.addEventListener('click', function() {
            if (this.classList.contains('selectable')) {
                this.classList.toggle('selected');
                updateSelectedItems();
            }
        });
    });
}

/**
 * Setup quantity controls
 */
function setupQuantityControls() {
    const quantityButtons = document.querySelectorAll('.quantity-btn');
    
    quantityButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.quantity-input');
            const change = this.textContent === '+' ? 1 : -1;
            const newValue = parseInt(input.value) + change;
            
            if (newValue >= 1 && newValue <= 10) {
                input.value = newValue;
                updateSelectedItems();
            }
        });
    });
}

/**
 * Update selected items
 */
function updateSelectedItems() {
    selectedItems = [];
    const selectedCards = document.querySelectorAll('.item-card.selected, .room-card.selected, .activity-card.selected, .event-card.selected');
    
    selectedCards.forEach(card => {
        const quantity = parseInt(card.querySelector('.quantity-input')?.value || 1);
        if (quantity > 0) {
            selectedItems.push({
                id: card.dataset.id,
                type: card.dataset.type,
                title: card.dataset.title,
                price: parseFloat(card.dataset.price),
                quantity: quantity
            });
        }
    });
    
    displaySelectedItems();
    calculateTotal();
}

/**
 * Display selected items
 */
function displaySelectedItems() {
    const container = document.getElementById('selectedItems');
    const list = document.getElementById('selectedItemsList');
    
    if (!container) return;
    
    if (selectedItems.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'block';
    
    if (list) {
        const itemsHTML = selectedItems.map((item, index) => `
            <div class="selected-item">
                <div>
                    <div style="font-weight: 600;">${item.title}</div>
                    <div style="font-size: 0.875rem; color: #6b7280;">Qty: ${item.quantity}</div>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span style="font-weight: 600; color: #059669;">$${(item.price * item.quantity).toFixed(2)}</span>
                    <button class="remove-btn" onclick="removeSelectedItem(${index})">Remove</button>
                </div>
            </div>
        `).join('');
        
        list.innerHTML = itemsHTML;
    }
}

/**
 * Remove selected item
 */
function removeSelectedItem(index) {
    selectedItems.splice(index, 1);
    displaySelectedItems();
    calculateTotal();
    
    // Update card selection
    document.querySelectorAll('.item-card, .room-card, .activity-card, .event-card').forEach(card => {
        const cardId = card.dataset.id;
        const cardType = card.dataset.type;
        const isSelected = selectedItems.some(item => item.id == cardId && item.type == cardType);
        card.classList.toggle('selected', isSelected);
    });
}

/**
 * Calculate total
 */
function calculateTotal() {
    const subtotal = selectedItems.reduce((sum, item) => {
        return sum + (item.price * item.quantity);
    }, 0);
    
    const deposit = subtotal * 0.2;
    const total = subtotal;
    
    const subtotalEl = document.getElementById('subtotal');
    const depositEl = document.getElementById('deposit');
    const totalEl = document.getElementById('total');
    
    if (subtotalEl) subtotalEl.textContent = `$${subtotal.toFixed(2)}`;
    if (depositEl) depositEl.textContent = `$${deposit.toFixed(2)}`;
    if (totalEl) totalEl.textContent = `$${total.toFixed(2)}`;
}

/**
 * Setup payment methods
 */
function setupPaymentMethods() {
    const paymentMethods = document.querySelectorAll('.payment-method');
    
    paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
            paymentMethods.forEach(m => m.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
}

/**
 * Submit booking
 */
async function submitBooking() {
    const form = document.getElementById('customerForm');
    if (!form || !validateForm(form)) {
        return;
    }
    
    if (selectedItems.length === 0) {
        showNotification('Please select at least one item to book.', 'error');
        return;
    }
    
    const selectedPaymentMethod = document.querySelector('.payment-method.selected');
    if (!selectedPaymentMethod) {
        showNotification('Please select a payment method.', 'error');
        return;
    }
    
    const formData = new FormData(form);
    
    // Get user information from session
    const customer = {
        fullname: formData.get('fullname') || '',
        email: formData.get('email') || '',
        phone: formData.get('phone') || ''
    };
    
    const bookingData = {
        type: selectedItems[0].type,
        items: selectedItems,
        customer: customer,
        special_requests: formData.get('special_requests'),
        payment_method: selectedPaymentMethod.dataset.method,
        check_in_date: formData.get('check_in_date'),
        check_out_date: formData.get('check_out_date')
    };
    
    showNotification('Processing your booking...', 'info');
    
    try {
        const response = await fetch('php/enhanced_book.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(bookingData)
        });
        
        const result = await response.json();
        
        if (result.ok) {
            showNotification(`Booking successful! Your booking reference is: ${result.booking_reference}`, 'success');
            
            // Clear localStorage
            localStorage.removeItem('pendingBooking');
            selectedItems = [];
            
            // Redirect to confirmation page
            setTimeout(() => {
                window.location.href = 'booking_confirmation.php?ref=' + result.booking_reference;
            }, 3000);
        } else {
            showNotification(`Booking failed: ${result.message || 'Unknown error'}`, 'error');
        }
    } catch (error) {
        console.error('Booking error:', error);
        showNotification('Booking failed. Please try again.', 'error');
    }
}

/**
 * Book room function
 */
function bookRoom(roomId, title, price) {
    // Check if user is logged in
    fetch('php/api/check_auth.php')
        .then(response => response.json())
        .then(data => {
            if (!data.logged_in) {
                showNotification('Please login to make a booking.', 'warning');
                setTimeout(() => {
                    window.location.href = 'auth/login.php?redirect=' + encodeURIComponent(window.location.href);
                }, 2000);
                return;
            }
            
            const checkIn = document.getElementById('check_in')?.value;
            const checkOut = document.getElementById('check_out')?.value;
            const guests = document.getElementById('guests')?.value || 2;
            
            if (!checkIn || !checkOut) {
                showNotification('Please select check-in and check-out dates.', 'warning');
                return;
            }
            
            const bookingData = {
                type: 'room',
                items: [{
                    id: roomId,
                    title: title,
                    price: price,
                    quantity: 1,
                    room_id: roomId
                }],
                check_in_date: checkIn,
                check_out_date: checkOut,
                guests: guests
            };
            
            localStorage.setItem('pendingBooking', JSON.stringify(bookingData));
            window.location.href = 'bookings.php';
        })
        .catch(error => {
            console.error('Auth check failed:', error);
            showNotification('Please login to make a booking.', 'warning');
            setTimeout(() => {
                window.location.href = 'auth/login.php?redirect=' + encodeURIComponent(window.location.href);
            }, 2000);
        });
}

/**
 * Book activity function
 */
function bookActivity(activityId, title, price) {
    // Check if user is logged in
    fetch('php/api/check_auth.php')
        .then(response => response.json())
        .then(data => {
            if (!data.logged_in) {
                showNotification('Please login to make a booking.', 'warning');
                setTimeout(() => {
                    window.location.href = 'auth/login.php?redirect=' + encodeURIComponent(window.location.href);
                }, 2000);
                return;
            }
            
            const bookingData = {
                type: 'activity',
                items: [{
                    id: activityId,
                    title: title,
                    price: price,
                    quantity: 1,
                    activity_id: activityId
                }]
            };
            
            localStorage.setItem('pendingBooking', JSON.stringify(bookingData));
            window.location.href = 'bookings.php';
        })
        .catch(error => {
            console.error('Auth check failed:', error);
            showNotification('Please login to make a booking.', 'warning');
            setTimeout(() => {
                window.location.href = 'auth/login.php?redirect=' + encodeURIComponent(window.location.href);
            }, 2000);
        });
}

/**
 * Book event function
 */
function bookEvent(eventId, title, price) {
    // Check if user is logged in
    fetch('php/api/check_auth.php')
        .then(response => response.json())
        .then(data => {
            if (!data.logged_in) {
                showNotification('Please login to make a booking.', 'warning');
                setTimeout(() => {
                    window.location.href = 'auth/login.php?redirect=' + encodeURIComponent(window.location.href);
                }, 2000);
                return;
            }
            
            const bookingData = {
                type: 'event',
                items: [{
                    id: eventId,
                    title: title,
                    price: price,
                    quantity: 1,
                    event_id: eventId
                }]
            };
            
            localStorage.setItem('pendingBooking', JSON.stringify(bookingData));
            window.location.href = 'bookings.php';
        })
        .catch(error => {
            console.error('Auth check failed:', error);
            showNotification('Please login to make a booking.', 'warning');
            setTimeout(() => {
                window.location.href = 'auth/login.php?redirect=' + encodeURIComponent(window.location.href);
            }, 2000);
        });
}

/**
 * Search functionality
 */
function setupSearchFunctionality() {
    const searchInputs = document.querySelectorAll('input[type="search"], input[placeholder*="search" i]');
    
    searchInputs.forEach(input => {
        let searchTimeout;
        
        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(this.value);
            }, 300);
        });
    });
}

/**
 * Perform search
 */
function performSearch(query) {
    if (query.length < 2) return;
    
    // Implement search functionality
    console.log('Searching for:', query);
}

/**
 * Modal handlers
 */
function setupModalHandlers() {
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        const closeBtn = modal.querySelector('.close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => closeModal(modal));
        }
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal(modal);
            }
        });
    });
}

/**
 * Open modal
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Close modal
 */
function closeModal(modal) {
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

/**
 * Image galleries
 */
function setupImageGalleries() {
    const galleries = document.querySelectorAll('.image-gallery');
    
    galleries.forEach(gallery => {
        const images = gallery.querySelectorAll('img');
        images.forEach((img, index) => {
            img.addEventListener('click', () => openImageModal(img.src, index));
        });
    });
}

/**
 * Open image modal
 */
function openImageModal(src, index) {
    // Implement image modal
    console.log('Opening image:', src, index);
}

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

/**
 * Show tooltip
 */
function showTooltip(e) {
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = e.target.dataset.tooltip;
    tooltip.style.position = 'absolute';
    tooltip.style.background = 'var(--text)';
    tooltip.style.color = 'white';
    tooltip.style.padding = '0.5rem';
    tooltip.style.borderRadius = '4px';
    tooltip.style.fontSize = '0.875rem';
    tooltip.style.zIndex = '1000';
    
    document.body.appendChild(tooltip);
    
    const rect = e.target.getBoundingClientRect();
    tooltip.style.left = rect.left + 'px';
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
    
    e.target._tooltip = tooltip;
}

/**
 * Hide tooltip
 */
function hideTooltip(e) {
    if (e.target._tooltip) {
        e.target._tooltip.remove();
        delete e.target._tooltip;
    }
}

/**
 * Initialize lazy loading
 */
function initializeLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

/**
 * Setup notifications
 */
function setupNotifications() {
    // Create notification container
    const container = document.createElement('div');
    container.id = 'notification-container';
    container.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        display: flex;
        flex-direction: column;
        gap: 10px;
    `;
    document.body.appendChild(container);
}

/**
 * Show notification
 */
function showNotification(message, type = 'info', duration = 5000) {
    const container = document.getElementById('notification-container');
    if (!container) return;
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    const styles = {
        background: type === 'success' ? 'var(--success)' : 
                   type === 'error' ? 'var(--danger)' : 
                   type === 'warning' ? 'var(--warning)' : 'var(--info)',
        color: 'white',
        padding: '1rem 1.5rem',
        borderRadius: '8px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        transform: 'translateX(100%)',
        transition: 'transform 0.3s ease',
        maxWidth: '400px',
        wordWrap: 'break-word'
    };
    
    Object.assign(notification.style, styles);
    
    container.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto remove
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, duration);
}

/**
 * Load user preferences
 */
function loadUserPreferences() {
    const preferences = localStorage.getItem('userPreferences');
    if (preferences) {
        try {
            const prefs = JSON.parse(preferences);
            applyUserPreferences(prefs);
        } catch (e) {
            console.error('Error loading user preferences:', e);
        }
    }
}

/**
 * Apply user preferences
 */
function applyUserPreferences(prefs) {
    if (prefs.theme === 'dark') {
        document.body.classList.add('dark-theme');
    }
    
    if (prefs.fontSize) {
        document.body.style.fontSize = prefs.fontSize;
    }
}

/**
 * Save user preferences
 */
function saveUserPreferences(prefs) {
    localStorage.setItem('userPreferences', JSON.stringify(prefs));
}

/**
 * Utility functions
 */
const utils = {
    formatCurrency: (amount) => `$${parseFloat(amount).toFixed(2)}`,
    formatDate: (date) => new Date(date).toLocaleDateString(),
    formatDateTime: (date) => new Date(date).toLocaleString(),
    debounce: (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    throttle: (func, limit) => {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
};

// Export functions for global use
window.bookRoom = bookRoom;
window.bookActivity = bookActivity;
window.bookEvent = bookEvent;
window.submitBooking = submitBooking;
window.openModal = openModal;
window.closeModal = closeModal;
window.showNotification = showNotification;
window.utils = utils;
window.utils = utils;