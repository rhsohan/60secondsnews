# ⚡ 60SecNews: The Fast-Paced News Engine

![60SecNews Logo](https://img.shields.io/badge/Status-Premium-blueviolet?style=for-the-badge) ![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777bb4?style=for-the-badge&logo=php) ![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql)

**60SecNews** is a high-performance, ultra-vibrant Content Management System designed for the era of rapid consumption. It enforces a strict **150-word editorial limit**, ensuring your audience gets the story they need in exactly one minute.

Custom-built with **Vanilla PHP** and **Glassmorphism aesthetics**, it combines raw speed with a premium, state-of-the-art user experience.

---

## ✨ Premium Features

*   **💎 Glassmorphism UI**: A stunning "frosted glass" interface with dynamic mesh gradients and smooth micro-animations.
*   **⏱️ One-Minute Stories**: Strict word-count enforcement (150 words) to guarantee lightning-fast reading.
*   **🚀 Caching Layer**: Automated HTML caching serves pages in milliseconds, bypassing the database for repeat visitors.
*   **🛡️ Multi-Level RBAC**: 5 specialized roles (Admin, Publisher, Editor, Writer, Media) with granular permission control.
*   **🕰️ Revision History**: A digital "Time Machine" that tracks every single change to every news article.
*   **📱 Reading Optimization**: Built-in "Reading Mode," dynamic font-resizing, and Masonry grid layouts.
*   **📧 Automated Digests**: Background Cron scripts that blast daily top stories to your subscribers.

---

## 🛠️ Tech Stack

### **Backend Logic**
*   **Core**: Vanilla PHP 8.2+ (Framework-free for maximum efficiency).
*   **Database**: MySQL 8.0/MariaDB with PDO Prepared Statements.
*   **Patterns**: Singleton (DB), Service Layer (Media), and Centralized Helpers.

### **Frontend Aesthetics**
*   **Style**: Modern CSS3 with Variables, Glass Panels, and Interactive Blurs.
*   **UI Framework**: Bootstrap 5.3 + Bootstrap Icons.
*   **Interactions**: Vanilla JavaScript (Fetch API) for non-blocking Likes & Saves.
*   **Grid**: Custom Masonry.js integration for visual impact.

---

## 🚀 Quick Setup

1.  **Environment**: Place the project folder in your `xampp/htdocs` directory.
2.  **Database**:
    *   Create a database named `60secnews`.
    *   Import the blueprint from `database/schema.sql`.
3.  **Configuration**:
    *   Set your site credentials in `config/config.php`.
4.  **Permissions**:
    *   Ensure `/storage/cache` and `/public/uploads` have write permissions.
5.  **Enjoy**: Visit the root URL to see the high-vibrancy frontend!

---

## 🔐 Master Accounts

| Role | Username | Password | Access Level |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `sohan` | `1234567890` | Full System Control |
| **Field Writer** | `field_writer` | `password123` | Content Creation Only |
| **Lead Publisher** | `lead_publisher` | `password123` | Final Editorial Review |

---

## 📖 Technical Documentation

For a deep-dive, file-by-file explanation of how the entire codebase works (ideal for junior developers), check out:
👉 [**STRUCTURE.md - The Full Technical Guide**](STRUCTURE.md)

---

## 🛡️ Security Posture
*   **CSRF Validation**: Every POST form is protected by unique, unpredictable tokens.
*   **Brute-Force Shield**: IP-level tracking that locks accounts after 5 failed attempts.
*   **XSS Neutralization**: Centralized `e()` function to sanitize all user output.
*   **SQL Isolation**: 100% Prepared Statements for all database interactions.

---

## 📄 License
This project is open-source. Build something fast. Build something beautiful.
