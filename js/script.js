// =============================================
// Online Library Management System - Custom JS
// =============================================

// Scroll to top button
const scrollTopBtn = document.createElement('button');
scrollTopBtn.className = 'btn-scroll-top';
scrollTopBtn.innerHTML = '<i class="fas fa-chevron-up"></i>';
document.body.appendChild(scrollTopBtn);

window.addEventListener('scroll', function() {
    if (window.scrollY > 300) {
        scrollTopBtn.classList.add('visible');
    } else {
        scrollTopBtn.classList.remove('visible');
    }
});

scrollTopBtn.addEventListener('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// Form validation - Confirm delete
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this item? This action cannot be undone.');
}

// Search form auto-submit on enter
const searchInputs = document.querySelectorAll('.search-input');
searchInputs.forEach(function(input) {
    input.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            this.closest('form').submit();
        }
    });
});

// Book request confirmation
function confirmIssue(bookTitle) {
    return confirm('Do you want to request/issue the book: "' + bookTitle + '"?');
}

// Return book confirmation
function confirmReturn(bookTitle) {
    return confirm('Do you want to return the book: "' + bookTitle + '"?');
}

// Auto-focus first input on page load
document.addEventListener('DOMContentLoaded', function() {
    var firstInput = document.querySelector('form input[type="text"], form input[type="email"], form input[type="password"]');
    if (firstInput) {
        firstInput.focus();
    }
});

// Live search filtering (for book lists)
function filterBooks(query) {
    var cards = document.querySelectorAll('.book-card-wrapper');
    query = query.toLowerCase();
    
    cards.forEach(function(card) {
        var title = card.getAttribute('data-title') || '';
        var author = card.getAttribute('data-author') || '';
        var category = card.getAttribute('data-category') || '';
        
        if (title.toLowerCase().includes(query) || 
            author.toLowerCase().includes(query) || 
            category.toLowerCase().includes(query)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
    anchor.addEventListener('click', function(e) {
        var targetId = this.getAttribute('href');
        if (targetId !== '#') {
            e.preventDefault();
            var target = document.querySelector(targetId);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        }
    });
});