// JavaScript for Library Management System

document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let valid = true;
            const inputs = this.querySelectorAll('input[required], select[required], textarea[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    valid = false;
                    highlightError(input);
                } else {
                    removeHighlight(input);
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
    
    function highlightError(element) {
        element.style.borderColor = '#e74c3c';
    }
    
    function removeHighlight(element) {
        element.style.borderColor = '';
    }
    
    // Search functionality enhancement
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const bookCards = document.querySelectorAll('.book-card');
            
            bookCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const author = card.querySelector('.author').textContent.toLowerCase();
                const isbn = card.querySelector('.isbn').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || author.includes(searchTerm) || isbn.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    
    // Auto-logout after 30 minutes of inactivity
    let inactivityTime = function() {
        let time;
        
        window.onload = resetTimer;
        document.onmousemove = resetTimer;
        document.onkeypress = resetTimer;
        
        function logout() {
            alert('You have been logged out due to inactivity.');
            window.location.href = 'logout.php';
        }
        
        function resetTimer() {
            clearTimeout(time);
            time = setTimeout(logout, 1800000); // 30 minutes
        }
    };
    
    // Only enable auto-logout if user is logged in
    if (document.body.classList.contains('logged-in')) {
        inactivityTime();
    }
    
    // Responsive navigation menu
    const navToggle = document.createElement('button');
    navToggle.innerHTML = '☰';
    navToggle.classList.add('nav-toggle');
    
    const nav = document.querySelector('nav');
    if (nav && window.innerWidth <= 768) {
        const logo = document.querySelector('.logo');
        logo.parentNode.insertBefore(navToggle, logo.nextSibling);
        
        const navLinks = document.querySelector('.nav-links');
        navToggle.addEventListener('click', function() {
            navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
        });
    }
});
