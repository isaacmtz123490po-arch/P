// ===== PAGE MANAGEMENT =====
function showPage(pageId) {
    console.log("Showing page:", pageId);

    // Hide all pages
    document.querySelectorAll('.page').forEach(page => {
        page.classList.remove('active');
    });

    // Show selected page
    const targetPage = document.getElementById(pageId + 'Page');
    if (targetPage) {
        targetPage.classList.add('active');
    }

    // Update nav links
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.classList.remove('active');
    });

    const activeLink = document.getElementById(pageId + 'Link');
    if (activeLink) {
        activeLink.classList.add('active');
    }

    // Scroll to top
    window.scrollTo(0, 0);
}

// ===== THEME MANAGEMENT =====
document.getElementById('themeToggle').addEventListener('click', function () {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);

    // Update icon
    const icon = this.querySelector('i');
    if (newTheme === 'dark') {
        icon.className = 'fas fa-sun';
        showToast('Dark mode enabled', 'info');
    } else {
        icon.className = 'fas fa-moon';
        showToast('Light mode enabled', 'info');
    }
});

// Load saved theme
const savedTheme = localStorage.getItem('theme') || 'light';
document.documentElement.setAttribute('data-theme', savedTheme);
const themeIcon = document.querySelector('#themeToggle i');
themeIcon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';

// ===== EMAIL GENERATOR =====
let variations = [];

function generateVariations() {
    const input = document.getElementById('gmailInput').value.trim();

    if (!input) {
        showToast('Please enter a Gmail address', 'error');
        return;
    }

    // Validate Gmail
    if (!input.toLowerCase().endsWith('@gmail.com')) {
        showToast('Please enter a valid Gmail address (example@gmail.com)', 'error');
        return;
    }

    const [username, domain] = input.split('@');
    const includeDot = document.getElementById('includeDot').checked;
    const includePlus = document.getElementById('includePlus').checked;
    const removeDuplicates = document.getElementById('removeDuplicates').checked;

    // Show loading
    showLoading(true, 'Generating variations...');

    // Process in background to prevent UI freeze
    setTimeout(() => {
        variations = [];

        // Add original email
        variations.push(`${username}@${domain}`);

        // Generate dot variations
        if (includeDot) {
            const length = username.length;
            // Generate all possible dot placements
            const totalCombinations = Math.pow(2, length - 1);

            for (let i = 1; i < totalCombinations; i++) {
                let variation = '';
                for (let j = 0; j < length; j++) {
                    variation += username[j];
                    if (j < length - 1 && (i >> j) & 1) {
                        variation += '.';
                    }
                }
                variations.push(`${variation}@${domain}`);

                // Limit for performance (max 1000 dot variations)
                if (variations.length >= 1000) break;
            }
        }

        // Generate plus variations
        if (includePlus) {
            const tags = [
                'shop', 'work', 'news', 'social', 'test', 'temp', 'demo',
                'web', 'app', 'service', 'personal', 'business', 'official',
                'mail', 'contact', 'support', 'sales', 'info', 'newsletter',
                'subscription', 'notification', 'update', 'alert', 'reminder',
                'spam', 'filter', 'track', 'verify', 'confirm', 'security'
            ];

            // Add tags
            tags.forEach(tag => {
                variations.push(`${username}+${tag}@${domain}`);
            });

            // Add numbered tags (1-10)
            for (let i = 1; i <= 10; i++) {
                variations.push(`${username}+${i}@${domain}`);
            }
        }

        // Remove duplicates if enabled
        if (removeDuplicates) {
            variations = [...new Set(variations)];
        }

        // Save last input
        localStorage.setItem('lastGmailInput', input);

        // Hide loading and show results
        showLoading(false);
        displayResults();

        // Show success message
        const count = variations.length.toLocaleString();
        showToast(`Generated ${count} variations successfully!`, 'success');

    }, 100); // Small delay for better UX
}

function displayResults() {
    const list = document.getElementById('resultsList');
    const empty = document.getElementById('emptyState');
    const total = document.getElementById('totalCount');
    const copyAllBtn = document.getElementById('copyAllBtn');

    total.textContent = variations.length.toLocaleString();
    copyAllBtn.disabled = variations.length === 0;

    if (variations.length === 0) {
        empty.style.display = 'block';
        list.innerHTML = '';
        return;
    }

    empty.style.display = 'none';
    list.innerHTML = '';

    // Show only first 200 variations for performance
    const displayCount = Math.min(variations.length, 200);
    const displayVariations = variations.slice(0, displayCount);

    displayVariations.forEach((email, index) => {
        const div = document.createElement('div');
        div.className = 'email-item';
        div.innerHTML = `
            <span class="email-text">${email}</span>
            <button class="btn-secondary" onclick="copySingle(${index}, this)" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">
                <i class="fas fa-copy"></i> Copy
            </button>
        `;
        list.appendChild(div);
    });

    // Show "more" message if there are more variations
    if (variations.length > 200) {
        const moreDiv = document.createElement('div');
        moreDiv.className = 'email-item';
        moreDiv.style.background = 'linear-gradient(45deg, var(--primary), var(--secondary))';
        moreDiv.style.color = 'white';
        moreDiv.innerHTML = `
            <span>... and ${(variations.length - 200).toLocaleString()} more variations</span>
            <button class="btn-secondary" onclick="showAllVariations()" style="background: white; color: var(--primary);">
                Show All
            </button>
        `;
        list.appendChild(moreDiv);
    }
}

function showAllVariations() {
    const list = document.getElementById('resultsList');
    list.innerHTML = '';

    variations.forEach((email, index) => {
        const div = document.createElement('div');
        div.className = 'email-item';
        div.innerHTML = `
            <span class="email-text">${email}</span>
            <button class="btn-secondary" onclick="copySingle(${index}, this)" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">
                <i class="fas fa-copy"></i> Copy
            </button>
        `;
        list.appendChild(div);
    });

    showToast(`Showing all ${variations.length.toLocaleString()} variations`, 'info');
}

function copySingle(index, button) {
    const email = variations[index];

    navigator.clipboard.writeText(email).then(() => {
        // Visual feedback on button
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        button.style.background = 'var(--secondary)';

        // Show toast
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true,
        });

        Toast.fire({
            icon: 'success',
            title: `Copied: ${email.substring(0, 30)}...`
        });

        // Revert button after 2 seconds
        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.style.background = '';
        }, 2000);

    }).catch(err => {
        console.error('Copy failed:', err);
        showToast('Failed to copy to clipboard', 'error');
    });
}

function copyAll() {
    if (variations.length === 0) {
        showToast('No variations to copy', 'warning');
        return;
    }

    Swal.fire({
        title: 'Copy All Variations',
        html: `Copy <b>${variations.length.toLocaleString()}</b> variations to clipboard?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Copy All',
        cancelButtonText: 'Cancel',
        confirmButtonColor: 'var(--primary)'
    }).then((result) => {
        if (result.isConfirmed) {
            const text = variations.join('\n');

            navigator.clipboard.writeText(text).then(() => {
                // Success
                const btn = document.getElementById('copyAllBtn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> All Copied!';
                btn.style.background = 'var(--secondary)';

                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    html: `<b>${variations.length.toLocaleString()}</b> variations copied to clipboard`,
                    timer: 2000,
                    showConfirmButton: false
                });

                // Revert button
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = '';
                }, 3000);

            }).catch(err => {
                console.error('Copy all failed:', err);
                showToast('Failed to copy variations', 'error');
            });
        }
    });
}

function clearResults() {
    if (variations.length === 0) {
        showToast('No results to clear', 'info');
        return;
    }

    Swal.fire({
        title: 'Clear Results',
        html: `Clear <b>${variations.length.toLocaleString()}</b> variations?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'var(--accent)',
        cancelButtonColor: 'var(--primary)',
        confirmButtonText: 'Yes, clear all!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            variations = [];
            displayResults();
            showToast('All results cleared', 'info');
        }
    });
}

function saveToLocal() {
    if (variations.length === 0) {
        showToast('No variations to save', 'warning');
        return;
    }

    const data = {
        input: document.getElementById('gmailInput').value,
        variations: variations,
        timestamp: new Date().toISOString(),
        count: variations.length
    };

    localStorage.setItem('savedVariations', JSON.stringify(data));

    showToast(`Saved ${variations.length} variations locally`, 'success');
}

function loadLast() {
    const lastInput = localStorage.getItem('lastGmailInput');
    if (lastInput) {
        document.getElementById('gmailInput').value = lastInput;
        showToast('Last input loaded', 'info');
    } else {
        showToast('No saved input found', 'info');
    }
}

function saveToCloud() {
    // Check if user is logged in
    if (!window.firebaseAuth || !window.firebaseAuth.currentUser) {
        showToast('Please login to save to cloud', 'error');
        showPage('login');
        return;
    }

    if (variations.length === 0) {
        showToast('No variations to save', 'warning');
        return;
    }

    Swal.fire({
        title: 'Coming Soon!',
        text: 'Cloud save feature will be available soon with Firestore integration',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

function showStats() {
    if (variations.length === 0) {
        showToast('Generate variations first', 'info');
        return;
    }

    const input = document.getElementById('gmailInput').value;
    const [username] = input.split('@');

    Swal.fire({
        title: 'Generation Statistics',
        html: `
            <div style="text-align: left; font-size: 16px; line-height: 1.8;">
                <p>📧 <b>Original Email:</b> ${input}</p>
                <p>🔢 <b>Total Variations:</b> ${variations.length.toLocaleString()}</p>
                <p>📏 <b>Username Length:</b> ${username.length} characters</p>
                <p>🔸 <b>Dot Variations:</b> ${document.getElementById('includeDot').checked ? 'Enabled' : 'Disabled'}</p>
                <p>➕ <b>Plus Variations:</b> ${document.getElementById('includePlus').checked ? 'Enabled' : 'Disabled'}</p>
                <p>🧹 <b>Duplicate Removal:</b> ${document.getElementById('removeDuplicates').checked ? 'Enabled' : 'Disabled'}</p>
                <hr style="margin: 1rem 0;">
                <p style="font-size: 14px; color: #666;">
                    💡 <i>All variations deliver to ${input}. Gmail ignores dots and text after plus sign.</i>
                </p>
            </div>
        `,
        icon: 'info',
        width: 500,
        confirmButtonText: 'Close'
    });
}

// ===== INITIALIZE APP =====
document.addEventListener('DOMContentLoaded', function () {
    console.log("🚀 App initialized");

    // Set current year in footer
    document.getElementById('currentYear').textContent = new Date().getFullYear();

    // Enter key support for generator input
    const gmailInput = document.getElementById('gmailInput');
    if (gmailInput) {
        gmailInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                generateVariations();
            }
        });
    }

    // Load last input if available
    loadLast();

    // Check if user is logged in on page load
    if (window.firebaseAuth) {
        const user = window.firebaseAuth.currentUser;
        if (user) {
            console.log("User already logged in:", user.email);
        }
    }

    // Show home page by default
    showPage('home');
});

// ===== GLOBAL FUNCTIONS =====
window.showPage = showPage;
window.generateVariations = generateVariations;
window.copyAll = copyAll;
window.clearResults = clearResults;
window.saveToLocal = saveToLocal;
window.loadLast = loadLast;
window.saveToCloud = saveToCloud;
window.showStats = showStats;
window.togglePassword = function (inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
};