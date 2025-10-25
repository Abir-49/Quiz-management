// Quiz Management System - JavaScript Utilities

// Confirmation dialogs for critical actions
document.addEventListener('DOMContentLoaded', function() {
    
    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('.btn-danger[onclick*="delete"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this? This action cannot be undone.')) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'red';
                } else {
                    field.style.borderColor = '#ddd';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return false;
            }
        });
    });
    
    // Search functionality with debounce
    const searchInputs = document.querySelectorAll('input[name="search"]');
    searchInputs.forEach(input => {
        let timeout;
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                if (this.value.length >= 3 || this.value.length === 0) {
                    // Auto-submit search form
                    this.form.submit();
                }
            }, 500);
        });
    });
    
    // Table row highlighting
    const tableRows = document.querySelectorAll('.data-table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('click', function() {
            // Remove highlight from all rows
            tableRows.forEach(r => r.classList.remove('selected-row'));
            // Add highlight to clicked row
            this.classList.add('selected-row');
        });
    });
    
    // Password strength indicator
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        const strengthIndicator = document.createElement('div');
        strengthIndicator.className = 'password-strength';
        input.parentNode.appendChild(strengthIndicator);
        
        input.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthIndicator.innerHTML = '';
            if (password.length > 0) {
                const strengthText = ['Weak', 'Fair', 'Good', 'Strong'];
                const strengthColors = ['#f56565', '#ed8936', '#48bb78', '#38a169'];
                strengthIndicator.textContent = 'Password Strength: ' + strengthText[strength - 1] || 'Very Weak';
                strengthIndicator.style.color = strengthColors[strength - 1] || '#e53e3e';
                strengthIndicator.style.marginTop = '5px';
                strengthIndicator.style.fontSize = '12px';
                strengthIndicator.style.fontWeight = '500';
            }
        });
    });
    
    // Quiz timer warning
    const timerElement = document.getElementById('timer');
    if (timerElement) {
        // Check if time is running low (handled in PHP, but adding visual effect)
        setInterval(() => {
            const timerText = timerElement.textContent;
            const timeMatch = timerText.match(/(\d+):(\d+)/);
            if (timeMatch) {
                const minutes = parseInt(timeMatch[1]);
                const seconds = parseInt(timeMatch[2]);
                const totalSeconds = minutes * 60 + seconds;
                
                // Visual warning at 1 minute
                if (totalSeconds <= 60 && totalSeconds > 0) {
                    timerElement.style.animation = 'pulse 1s infinite';
                }
            }
        }, 1000);
    }
    
    // Prevent multiple form submissions
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitButtons = form.querySelectorAll('button[type="submit"]');
            submitButtons.forEach(button => {
                button.disabled = true;
                button.textContent = 'Processing...';
            });
        });
    });
    
    // Smooth scroll to top button
    const scrollTopBtn = document.createElement('button');
    scrollTopBtn.innerHTML = '↑';
    scrollTopBtn.className = 'scroll-top-btn';
    scrollTopBtn.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        background: #667eea;
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-size: 24px;
        display: none;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s;
    `;
    document.body.appendChild(scrollTopBtn);
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollTopBtn.style.display = 'block';
        } else {
            scrollTopBtn.style.display = 'none';
        }
    });
    
    scrollTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    scrollTopBtn.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.1)';
    });
    
    scrollTopBtn.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });
    
    // Print answer script functionality
    const printButtons = document.querySelectorAll('.print-btn');
    printButtons.forEach(button => {
        button.addEventListener('click', function() {
            window.print();
        });
    });
    
    // Copy to clipboard functionality for sharing quiz links
    const copyButtons = document.querySelectorAll('.copy-link-btn');
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const link = this.dataset.link;
            navigator.clipboard.writeText(link).then(() => {
                const originalText = this.textContent;
                this.textContent = 'Copied!';
                setTimeout(() => {
                    this.textContent = originalText;
                }, 2000);
            });
        });
    });
    
    // Question counter for quiz creation
    const addQuestionBtn = document.querySelector('.add-question-btn');
    if (addQuestionBtn) {
        updateQuestionCount();
    }
    
    function updateQuestionCount() {
        const questionCards = document.querySelectorAll('.question-card');
        const counter = document.getElementById('question-counter');
        if (counter) {
            counter.textContent = `Total Questions: ${questionCards.length}`;
        }
    }
    
    // Auto-save draft functionality (could be enhanced with localStorage)
    let autoSaveTimeout;
    const formInputs = document.querySelectorAll('input, textarea, select');
    formInputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(() => {
                // Could save draft here
                console.log('Auto-saving draft...');
            }, 3000);
        });
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl + S to submit form (prevent default save)
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            const form = document.querySelector('form');
            if (form && confirm('Submit form?')) {
                form.submit();
            }
        }
        
        // Escape to go back
        if (e.key === 'Escape') {
            const backBtn = document.querySelector('.btn-secondary[href]');
            if (backBtn && confirm('Go back?')) {
                window.location.href = backBtn.href;
            }
        }
    });
    
    // Tooltip functionality
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = element.dataset.tooltip;
        tooltip.style.cssText = `
            position: absolute;
            background: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            display: none;
            z-index: 1000;
            white-space: nowrap;
        `;
        document.body.appendChild(tooltip);
        
        element.addEventListener('mouseenter', function(e) {
            tooltip.style.display = 'block';
            tooltip.style.left = e.pageX + 10 + 'px';
            tooltip.style.top = e.pageY + 10 + 'px';
        });
        
        element.addEventListener('mouseleave', function() {
            tooltip.style.display = 'none';
        });
        
        element.addEventListener('mousemove', function(e) {
            tooltip.style.left = e.pageX + 10 + 'px';
            tooltip.style.top = e.pageY + 10 + 'px';
        });
    });
    
    // Statistics animation on load
    const statCards = document.querySelectorAll('.stat-card h3');
    statCards.forEach(card => {
        const targetValue = parseInt(card.textContent);
        let currentValue = 0;
        const increment = targetValue / 50;
        
        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= targetValue) {
                currentValue = targetValue;
                clearInterval(timer);
            }
            card.textContent = Math.floor(currentValue);
        }, 20);
    });
});

// Add CSS animation for pulse effect
const style = document.createElement('style');
style.textContent = `
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    .selected-row {
        background: #ebf4ff !important;
        box-shadow: 0 0 0 2px #667eea inset;
    }
`;
document.head.appendChild(style);

// Export functions for use in other scripts
window.quizUtils = {
    validateEmail: function(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },
    
    formatTime: function(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    },
    
    calculatePercentage: function(obtained, total) {
        if (total === 0) return 0;
        return ((obtained / total) * 100).toFixed(2);
    }
};

console.log('Quiz Management System - JavaScript loaded successfully!');