// ===== FIREBASE CONFIGURATION =====
const firebaseConfig = {
    apiKey: "AIzaSyDS10H_W21ImFgtRPtDgYyNvDFx11YRocQ",
    authDomain: "generator-b8244.firebaseapp.com",
    projectId: "generator-b8244",
    storageBucket: "generator-b8244.firebasestorage.app",
    messagingSenderId: "799162613894",
    appId: "1:799162613894:web:f63582bd8544fb01961107",
    measurementId: "G-668PZYX5KK"
};

// ===== INITIALIZE FIREBASE =====
if (!firebase.apps.length) {
    firebase.initializeApp(firebaseConfig);
    console.log("✅ Firebase initialized");
} else {
    console.log("✅ Firebase already initialized");
}

// ===== FIREBASE SERVICES =====
const auth = firebase.auth();
console.log("✅ Firebase Auth loaded");

// ===== AUTH STATE LISTENER =====
auth.onAuthStateChanged((user) => {
    console.log("Auth state changed:", user ? "Logged in" : "Logged out");

    if (user) {
        // User is signed in
        document.getElementById('logoutBtn').style.display = 'flex';
        document.getElementById('loginLink').style.display = 'none';
        document.getElementById('registerLink').style.display = 'none';

        // Update user info
        const userNameElement = document.getElementById('userName');
        const userEmailElement = document.getElementById('userEmailDisplay');

        if (userNameElement) {
            userNameElement.textContent = user.displayName || user.email.split('@')[0];
        }

        if (userEmailElement) {
            userEmailElement.textContent = user.email;
        }

        // Show dashboard
        showPage('dashboard');
        showToast(`Welcome back, ${user.displayName || user.email}!`, 'success');

    } else {
        // User is signed out
        document.getElementById('logoutBtn').style.display = 'none';
        document.getElementById('loginLink').style.display = 'inline-flex';
        document.getElementById('registerLink').style.display = 'inline-flex';

        // Show home if on dashboard
        const dashboardPage = document.getElementById('dashboardPage');
        if (dashboardPage && dashboardPage.classList.contains('active')) {
            showPage('home');
        }
    }
});

// ===== LOGIN FUNCTION =====
document.getElementById('loginForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value;

    if (!email || !password) {
        showToast('Please enter email and password', 'error');
        return;
    }

    try {
        showLoading(true, 'Logging in...');
        const userCredential = await auth.signInWithEmailAndPassword(email, password);
        console.log("Login successful:", userCredential.user.email);
        showToast('Login successful!', 'success');
    } catch (error) {
        console.error("Login error:", error.code, error.message);

        let errorMessage = 'Login failed. Please check credentials.';

        switch (error.code) {
            case 'auth/user-not-found':
                errorMessage = 'No account found with this email.';
                break;
            case 'auth/wrong-password':
                errorMessage = 'Incorrect password. Please try again.';
                break;
            case 'auth/invalid-email':
                errorMessage = 'Invalid email format.';
                break;
            case 'auth/user-disabled':
                errorMessage = 'This account has been disabled.';
                break;
            case 'auth/too-many-requests':
                errorMessage = 'Too many attempts. Please try again later.';
                break;
        }

        showToast(errorMessage, 'error');
    } finally {
        showLoading(false);
    }
});

// ===== REGISTER FUNCTION =====
document.getElementById('registerForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const name = document.getElementById('registerName').value.trim();
    const email = document.getElementById('registerEmail').value.trim();
    const password = document.getElementById('registerPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    // Validation
    if (!name || !email || !password || !confirmPassword) {
        showToast('Please fill in all fields', 'error');
        return;
    }

    if (password.length < 6) {
        showToast('Password must be at least 6 characters', 'error');
        return;
    }

    if (password !== confirmPassword) {
        showToast('Passwords do not match', 'error');
        return;
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showToast('Please enter a valid email address', 'error');
        return;
    }

    try {
        showLoading(true, 'Creating account...');

        // Create user
        const userCredential = await auth.createUserWithEmailAndPassword(email, password);

        // Update profile with name
        await userCredential.user.updateProfile({
            displayName: name
        });

        console.log("Registration successful:", userCredential.user.email);
        showToast('Account created successfully! Welcome!', 'success');

    } catch (error) {
        console.error("Registration error:", error.code, error.message);

        let errorMessage = 'Registration failed. Please try again.';

        switch (error.code) {
            case 'auth/email-already-in-use':
                errorMessage = 'This email is already registered.';
                break;
            case 'auth/invalid-email':
                errorMessage = 'Invalid email format.';
                break;
            case 'auth/weak-password':
                errorMessage = 'Password is too weak. Please use a stronger password.';
                break;
            case 'auth/operation-not-allowed':
                errorMessage = 'Email/password accounts are not enabled.';
                break;
        }

        showToast(errorMessage, 'error');
    } finally {
        showLoading(false);
    }
});

// ===== LOGOUT FUNCTION =====
function logout() {
    Swal.fire({
        title: 'Logout?',
        text: 'Are you sure you want to logout?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, logout!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            auth.signOut()
                .then(() => {
                    showToast('Logged out successfully', 'info');
                })
                .catch((error) => {
                    showToast('Logout failed: ' + error.message, 'error');
                });
        }
    });
}

// ===== PASSWORD RESET =====
function showForgotPassword() {
    Swal.fire({
        title: 'Reset Password',
        input: 'email',
        inputLabel: 'Enter your email address',
        inputPlaceholder: 'you@example.com',
        showCancelButton: true,
        confirmButtonText: 'Send Reset Link',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        preConfirm: (email) => {
            return auth.sendPasswordResetEmail(email)
                .then(() => {
                    return 'Password reset email sent!';
                })
                .catch((error) => {
                    Swal.showValidationMessage(`Failed: ${error.message}`);
                });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Email Sent!',
                text: 'Check your inbox for password reset instructions',
                timer: 3000
            });
        }
    });
}

// ===== HELPER FUNCTIONS =====
function showLoading(show, message = 'Loading...') {
    if (show) {
        Swal.fire({
            title: message,
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
    } else {
        Swal.close();
    }
}

function showToast(message, type = 'info') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });

    Toast.fire({
        icon: type,
        title: message
    });
}

// ===== TOGGLE PASSWORD VISIBILITY =====
function togglePassword(inputId) {
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
}

// ===== INITIALIZE AUTH FORMS =====
document.addEventListener('DOMContentLoaded', function () {
    // Add password toggle buttons
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'show-password';
        toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
        toggleBtn.onclick = () => togglePassword(input.id);

        // Wrap input and button
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        wrapper.appendChild(toggleBtn);
    });

    console.log("✅ Firebase.js initialized successfully");
});

// ===== EXPORT FOR OTHER FILES =====
window.firebaseAuth = auth;
window.logout = logout;
window.showToast = showToast;
window.showLoading = showLoading;
window.showForgotPassword = showForgotPassword;