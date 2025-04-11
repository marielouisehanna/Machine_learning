// Journal page writing prompts
const writingPrompts = [
    "Express your thoughts and feelings. What's on your mind today?",
    "What are three things that made you smile today?",
    "Describe a challenge you faced today and how you handled it.",
    "What are you grateful for in this moment?",
    "What would you like to accomplish tomorrow?",
    "Reflect on something you learned today about yourself.",
    "What's one thing you're proud of from today?",
    "How did you take care of yourself today?",
    "Write about something that's been on your mind lately.",
    "What's something you're looking forward to?",
    "Describe your current mood using colors, textures, or weather.",
    "If today were a chapter in your life story, what would you title it?"
];

// Function to change the writing prompt
function changePrompt() {
    const promptElement = document.getElementById('writing-prompt');
    if (promptElement) {
        const currentPrompt = promptElement.textContent;
        let newPrompt = currentPrompt;
        
        // Make sure we get a different prompt
        while (newPrompt === currentPrompt) {
            newPrompt = writingPrompts[Math.floor(Math.random() * writingPrompts.length)];
        }
        
        // Animate the change
        promptElement.style.opacity = '0';
        setTimeout(() => {
            promptElement.textContent = newPrompt;
            promptElement.style.opacity = '1';
        }, 300);
    }
}document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('show');
            this.classList.toggle('active');
        });
    }
    
    // Add active class to current page in navigation
    const currentLocation = location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        if(link.getAttribute('href') === currentLocation.substring(currentLocation.lastIndexOf('/') + 1)) {
            link.classList.add('active');
        }
    });
    
    // Removed the fade-in animations that were causing content to disappear
    
    // Auto-resize textarea in journal form
    const journalTextarea = document.getElementById('entry-content');
    
    if (journalTextarea) {
        journalTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Trigger the resize on page load
        window.addEventListener('load', function() {
            if (journalTextarea.value.trim() !== '') {
                journalTextarea.style.height = 'auto';
                journalTextarea.style.height = (journalTextarea.scrollHeight) + 'px';
            }
        });
    }
    
    // Journal entry form validation
    const journalForm = document.getElementById('journal-form');
    
    if (journalForm) {
        journalForm.addEventListener('submit', function(e) {
            const entryContent = document.getElementById('entry-content');
            
            if (entryContent.value.trim() === '') {
                e.preventDefault();
                showAlert('Please write something in your journal entry', 'danger');
            }
        });
    }
    
    // Registration form validation
    const registerForm = document.getElementById('register-form');
    
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username');
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm-password');
            
            if (username.value.trim() === '') {
                e.preventDefault();
                showAlert('Username is required', 'danger');
                return;
            }
            
            if (email.value.trim() === '') {
                e.preventDefault();
                showAlert('Email is required', 'danger');
                return;
            }
            
            if (!isValidEmail(email.value)) {
                e.preventDefault();
                showAlert('Please enter a valid email', 'danger');
                return;
            }
            
            if (password.value === '') {
                e.preventDefault();
                showAlert('Password is required', 'danger');
                return;
            }
            
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                showAlert('Passwords do not match', 'danger');
                return;
            }
        });
    }
    
    // Login form validation
    const loginForm = document.getElementById('login-form');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            
            if (email.value.trim() === '') {
                e.preventDefault();
                showAlert('Email is required', 'danger');
                return;
            }
            
            if (password.value === '') {
                e.preventDefault();
                showAlert('Password is required', 'danger');
                return;
            }
        });
    }
    
    // Handle like/dislike for recommendations
    const likeButtons = document.querySelectorAll('.like-btn');
    const dislikeButtons = document.querySelectorAll('.dislike-btn');
    
    likeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const recommendationId = this.getAttribute('data-id');
            updateRecommendationFeedback(recommendationId, true);
        });
    });
    
    dislikeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const recommendationId = this.getAttribute('data-id');
            updateRecommendationFeedback(recommendationId, false);
        });
    });
    
    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const target = document.querySelector(this.getAttribute('href'));
            
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Helper Functions
    function showAlert(message, type) {
        // Remove any existing alerts
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => {
            alert.remove();
        });
        
        // Create the alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        
        // Add icon based on alert type
        if (type === 'success') {
            alertDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
        } else if (type === 'danger') {
            alertDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        } else {
            alertDiv.innerHTML = message;
        }
        
        // Find the location to insert the alert
        const container = document.querySelector('.form-container');
        const formTitle = container.querySelector('.form-title');
        const form = container.querySelector('form');
        
        // Insert before the form or after the title
        if (form) {
            container.insertBefore(alertDiv, form);
        } else if (formTitle) {
            formTitle.parentNode.insertBefore(alertDiv, formTitle.nextSibling);
        } else {
            container.prepend(alertDiv);
        }
        
        // Add animation class
        setTimeout(() => {
            alertDiv.classList.add('show');
        }, 10);
        
        // Significantly increased the alert display time to prevent quick disappearance
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => {
                alertDiv.remove();
            }, 500);
        }, 30000);
    }
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function updateRecommendationFeedback(id, liked) {
        // Find the recommendation item
        const recommendationItem = document.querySelector(`.recommendation-item[data-id="${id}"]`);
        
        if (!recommendationItem) {
            return;
        }
        
        // Find the buttons
        const likeBtn = recommendationItem.querySelector('.like-btn');
        const dislikeBtn = recommendationItem.querySelector('.dislike-btn');
        
        // Prevent double-clicking if request is in progress
        if (recommendationItem.classList.contains('loading')) {
            return;
        }
        
        // Add loading state
        recommendationItem.classList.add('loading');
        
        // Pre-update the UI for immediate feedback (optimistic update)
        if (liked) {
            likeBtn.classList.add('active');
            dislikeBtn.classList.remove('active');
        } else {
            dislikeBtn.classList.add('active');
            likeBtn.classList.remove('active');
        }
        
        // Send AJAX request to update user feedback
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_recommendation.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            // Remove loading state
            recommendationItem.classList.remove('loading');
            
            if (this.status === 200) {
                try {
                    const response = JSON.parse(this.responseText);
                    
                    if (response.success) {
                        // Show success message
                        const feedbackType = liked ? 'liked' : 'disliked';
                        showAlert(`You ${feedbackType} this recommendation!`, 'success');
                        
                        // Add animation effect
                        recommendationItem.classList.add('feedback-recorded');
                        setTimeout(() => {
                            recommendationItem.classList.remove('feedback-recorded');
                        }, 700);
                    } else {
                        // Revert UI changes if request failed
                        showAlert(response.message || 'Something went wrong', 'danger');
                    }
                } catch (e) {
                    console.error("Error parsing JSON:", e);
                    showAlert('Error processing response', 'danger');
                }
            } else {
                showAlert('Server error. Please try again later.', 'danger');
            }
        };
        
        xhr.onerror = function() {
            // Remove loading state
            recommendationItem.classList.remove('loading');
            showAlert('Connection error. Please try again later.', 'danger');
        };
        
        xhr.send(`id=${id}&liked=${liked ? 1 : 0}`);
    }
});

// Add CSS class when the page is fully loaded
window.addEventListener('load', function() {
    document.body.classList.add('page-loaded');
});