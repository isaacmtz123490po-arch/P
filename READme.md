# 📧 Gmail Variation Generator

A modern, full-featured web application to generate all possible Gmail variations using dot (.) and plus (+) tricks. Built with Firebase Authentication and a beautiful, responsive UI.

![Gmail Generator](https://img.shields.io/badge/Gmail-Variation_Generator-blue)
![Firebase](https://img.shields.io/badge/Firebase-Authentication-orange)
![License](https://img.shields.io/badge/License-MIT-green)

## ✨ Features

### 🔐 Authentication
- **Firebase Email/Password Authentication**
- Secure login/registration system
- User session management
- Password reset functionality
- Protected dashboard access

### 📧 Email Variation Generator
- **Dot (.) Variations** - Generate all possible dot placements (test@gmail.com → t.est@gmail.com, te.st@gmail.com, etc.)
- **Plus (+) Variations** - Add custom tags after plus sign (test+shop@gmail.com, test+work@gmail.com)
- **Unlimited Generation** - No limits on variations
- **Duplicate Removal** - Automatically remove duplicate emails
- **Copy Features** - Copy single or all variations with one click
- **Enter Key Support** - Press Enter to generate
- **Local Storage** - Save last input and variations locally

### 🎨 Modern UI/UX
- **Dark/Light Mode** - Toggle between themes
- **Fully Responsive** - Works on mobile, tablet, and desktop
- **SweetAlert2 Integration** - Beautiful alerts and notifications
- **Font Awesome Icons** - Visual icons throughout
- **Clean Design** - Modern card-based layout
- **Loading Indicators** - Visual feedback for operations

### 💡 Additional Features
- **Statistics Dashboard** - View generation stats
- **Advanced Options** - Custom tags and settings
- **Cloud Save** (Coming Soon) - Save variations to Firebase Firestore
- **Local Save** - Save variations to browser storage
- **Demo Examples** - Live email variation demonstrations
- **Pro Tips** - Useful information and best practices

## 🚀 Live Demo

[Add your GitHub Pages URL here]

## 📁 Project Structure

```
gmail-variation-generator/
│
├── index.html          # Single HTML file (all pages)
├── style.css           # All styles (dark/light mode)
├── firebase.js         # Firebase authentication
├── script.js           # Application logic + generator
├── icon.png            # Logo (350x350px)
└── README.md           # This documentation
```

## 🛠️ Technologies Used

- **HTML5** - Semantic markup
- **CSS3** - Modern styling with CSS variables
- **JavaScript (ES6+)** - Client-side logic
- **Firebase v8.10.0** - Authentication
- **SweetAlert2** - Beautiful alerts
- **Font Awesome 6** - Icons
- **GitHub Pages** - Free hosting

## 🔧 Setup Instructions

### 1. Firebase Setup
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Create a new project or use existing
3. Enable **Authentication** → **Email/Password**
4. Copy your Firebase config and replace in `firebase.js`:
```javascript
const firebaseConfig = {
    apiKey: "YOUR_API_KEY",
    authDomain: "YOUR_AUTH_DOMAIN",
    projectId: "YOUR_PROJECT_ID",
    storageBucket: "YOUR_STORAGE_BUCKET",
    messagingSenderId: "YOUR_SENDER_ID",
    appId: "YOUR_APP_ID",
    measurementId: "YOUR_MEASUREMENT_ID"
};
```

### 2. Local Setup
1. Clone or download all files
2. Place all 5 files in same folder
3. Add `icon.png` (350x350px) or download from link
4. Open `index.html` in browser
5. Register new account and start generating!

### 3. GitHub Pages Deployment
1. Create GitHub repository
2. Upload all files
3. Go to Settings → Pages
4. Select branch: `main` and folder: `/ (root)`
5. Save - Your site will be live at: `https://codebanglailp.github.io/Gmail-Variation-Generator`

## 📖 How It Works

### Gmail Dot Trick
Gmail ignores dots in the username:
- `test@gmail.com`
- `t.est@gmail.com`
- `te.st@gmail.com`
- `t.e.s.t@gmail.com`

All deliver to the same inbox!

### Gmail Plus Trick
Gmail ignores text after plus sign:
- `test+shop@gmail.com`
- `test+work@gmail.com`
- `test+newsletter@gmail.com`

All deliver to `test@gmail.com` - perfect for filtering!

## 💡 Use Cases

1. **Online Shopping** - Use different variations for different stores
2. **Newsletters** - Track which service shares your email
3. **Privacy Protection** - Protect main email from spam
4. **Email Testing** - Test email functionality with variations
5. **Organization** - Filter emails by source using plus tags
6. **Account Management** - Create unique emails for different services

## 🎯 Features in Detail

### Home Page
- Eye-catching hero section
- 8 feature cards with icons
- Step-by-step guide
- Use cases showcase
- Statistics display
- Final call-to-action

### Authentication
- Clean login/register forms
- Password visibility toggle
- Form validation
- Error handling
- Password reset option

### Dashboard
- Welcome message with user info
- Email input with auto-focus
- Generation options checkboxes
- Results display with copy buttons
- Action buttons (Save, Clear, Load, Stats)
- Pro tips section

### Generator
- Real-time variation generation
- Performance optimized (shows 200 at a time)
- "Show All" option for all variations
- Copy individual or all variations
- Statistics view
- Local storage integration

## 📱 Responsive Design

- **Mobile (320px+)**: Single column layout, optimized touch targets
- **Tablet (768px+)**: Two column grids, larger fonts
- **Desktop (1024px+)**: Full multi-column layouts, side-by-side sections
- **Dark Mode**: Automatic theme detection, manual toggle

## 🔒 Security

- Firebase Authentication (Google managed)
- No sensitive data in local storage
- HTTPS recommended for production
- Password hashing (Firebase handled)
- Session management

## 🆓 Cost & Hosting

- **Total Cost**: $0 (completely free)
- **Firebase**: Free tier (Authentication)
- **Hosting**: GitHub Pages (free)
- **CDNs**: Firebase, SweetAlert2, Font Awesome (free)

## 🐛 Troubleshooting

### Common Issues:
1. **Firebase not working**: Check if Email/Password auth is enabled
2. **No popups/alerts**: Ensure SweetAlert2 CDN is loaded
3. **Not responsive**: Clear browser cache (Ctrl+Shift+R)
4. **No variations**: Check if Gmail ends with @gmail.com

### Browser Support:
- Chrome 60+ ✅
- Firefox 55+ ✅
- Safari 12+ ✅
- Edge 79+ ✅
- Opera 47+ ✅

## 🤝 Contributing

Contributions are welcome! Here's how:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Firebase** - For authentication services
- **SweetAlert2** - For beautiful alerts
- **Font Awesome** - For icons
- **GitHub** - For free hosting
- **Google** - For Gmail's dot/plus feature

## 📞 Support

For issues, questions, or suggestions:
1. Check the [GitHub Issues]([https://github.com/codebanglailp/Gmail-Variation-Generator](https://github.com/codebanglailp/Gmail-Variation-Generator))
2. Create a new issue with details
3. Email: [code.bangla.ilp@gmail.com]

## 🌟 Star History

If you find this project useful, please give it a star on GitHub!

---

Made with ❤️ for developers and email power users