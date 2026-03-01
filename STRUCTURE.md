# 60SecNews: Deep-Dive Project Documentation

This document provides a highly detailed, file-by-file explanation of the **60SecNews** project. It is designed specifically for junior developers to understand not just *what* each file is, but *how* the code logic works.

---

## 🏗️ 1. Core Architecture (The "Engine Room")

### 📂 `app/` (The Application Logic)
This folder contains the "brains" of the PHP application.

*   **`db.php`**
    *   **What it does:** Manages your connection to the MySQL database.
    *   **Deep Dive:** It uses the **Singleton Pattern**. This means it has a `static` method called `getInstance()`. Instead of opening a new database connection every time, it checks if one already exists and reuses it. This saves memory and prevents the server from crashing under high traffic.
    *   **Pattern:** `DB::getInstance()->getConnection()` returns a **PDO** object, which is the modern, secure way to talk to databases in PHP.

*   **`helpers.php`**
    *   **What it does:** A "Swiss Army Knife" of reusable functions.
    *   **Key Functions:**
        *   `e($string)`: Uses `htmlspecialchars` to prevent **XSS (Cross-Site Scripting)**. Never display user-submitted text without wrapping it in this!
        *   `generate_csrf_token()`: Creates a random secret key for forms. This prevents **CSRF (Cross-Site Request Forgery)**, where attackers try to submit forms on your behalf.
        *   `time_ago()`: Complex logic that calculates the difference between "now" and a "publish date" to return "5 hours ago" instead of a raw date.
    *   **Caching Logic:** Inside `clear_cache()`, it uses `glob()` to find all homepage cache files in the `storage/` folder and `unlink()` (deletes) them.

*   **`rbac.php`**
    *   **What it does:** Role-Based Access Control.
    *   **Deep Dive:** It checks columns in the `users` and `roles` table. When you visit an admin page, it calls `has_permission('edit_article')`. If the user's role doesn't have that permission in the database, it redirects them or shows an error.

*   **`media_svc.php`**
    *   **What it does:** Handles the "Heavy Lifting" for images.
    *   **Logic:** When you upload an image, this service generates a unique filename (using `uniqid()`), creates a folder if it doesn't exist, and moves the temporary file to its final home in `public/uploads/`.

---

## ⚙️ 2. Configuration (The "Site Dashboard")

### 📂 `config/`
*   **`config.php`**
    *   **What it does:** Sets up the environment.
    *   **Logic:** It uses `define()` to create global constants. For example, `BASE_URL` is used so that if you move the site from `localhost/60secnews` to `www.news.com`, you only change it in **one** place.
*   **`settings.json`**
    *   **What it does:** Stores user-editable settings without needing a database table for every single small thing.
    *   **In-Depth:** The `index.php` reads this file using `file_get_contents()` and `json_decode()`. This allows the Admin to change the "Site Title" instantly.

---

## 🌐 3. The Frontend (What the User Sees)

### 📂 `public/`
*   **`index.php` (The Homepage)**
    *   **The "Caching Layer":** At the very top, it checks if a file exists in `storage/cache/`. If it does, and it's less than 5 minutes old, it simply "echoes" that HTML and stops (`exit`). This makes the site feel lightning fast because it doesn't even talk to the database!
    *   **The Grid:** It uses **Masonry.js** (a JavaScript library) to align news cards of different heights into a beautiful, compact grid.
    *   **Filters:** It looks at `$_GET['category']`. If you click "Tech", it adds `WHERE category_slug = 'tech'` to the SQL query.

*   **`article.php` (The Story Page)**
    *   **The Logic:** It takes a `slug` (e.g., `tech-news-today`) from the URL, finds that article in the database, and displays it.
    *   **Reading Tools:** JavaScript code at the bottom allows users to click buttons to change the `rem` font size of the text dynamically.
    *   **Comments:** When you post a comment, it checks the IP address to prevent spam and saves it with a `pending` status if the admin has enabled moderation.

*   **`ajax_interaction.php`**
    *   **How it works:** This file doesn't have any HTML. It only returns **JSON**. When you click a Heart icon, JavaScript sends a `POST` request here. The PHP updates the database and sends back `{"success": true}`. The JavaScript then updates the UI without refreshing the page.

*   **`ajax_load_more.php`**
    *   **How it works:** This file handles fetching more news cards when you scroll or click "Load More." It receives a `page` number, finds the next set of articles in the database, and returns them as a **JSON** object containing the HTML to be added to the grid.

*   **`bookmarks.php`**
    *   **How it works:** This is a full page that displays a user's personal collection of stories. It looks at the database for any articles matching the current visitor's session ID and shows them in a clean list, allowing users to revisit news they liked or saved.

*   **`rss.php`**
    *   **How it works:** This is a data feed for other apps and websites. It generates an **XML** list of the latest 50 news stories. It doesn't have a visual design; instead, it provides structured data so people can follow your news using an RSS reader.

*   **`sitemap.php`**
    *   **How it works:** This is a "Map" for search engines like Google. It automatically lists every article and category on your site in an **XML** format. This helps Google find your pages faster so they appear in search results (SEO).

*   **`robots.txt`**
    *   **How it works:** This is a simple text file that gives instructions to web bots. It tells them which folders they are allowed to look at (like public news) and which ones they should stay out of (like your internal admin or cache folders).

### 📂 `public/auth/` (The Security Gate)
This folder handles everything related to who can enter the "Backstage" area of the site.

*   **`login.php`**
    *   **Security Logic:** This is where authors and admins sign in. It uses **`password_verify()`** to check passwords, which is impossible for hackers to "reverse engineer."
    *   **Lockout System:** To prevent "Brute Force" attacks (where someone tries thousands of passwords), it tracks failed attempts. After 5 wrong guesses, it locks the account for 15 minutes!
    *   **Session Security:** It uses `session_regenerate_id()` as soon as you log in. This prevents "Session Hijacking," a common way hackers try to steal your logged-in status.

*   **`register.php`**
    *   **Validation Logic:** It checks that the email is real, the username is clean, and the password is at least 8 characters long.
    *   **Encryption:** It uses **`password_hash()`** to turn your password into a long string of random characters before saving it to the database. Even if a hacker stole the database, they wouldn't know your password!
    *   **Pending Approval:** New accounts are set to `inactive` by default. A "Super Admin" must manually approve you in the Admin Panel before you can start writing.

*   **`logout.php`**
    *   **Logic:** It does more than just stop the site. It clears all session data, deletes the "Login Cookie" from your browser, and records a "Logout" event in the audit logs for security tracking.

---

## 🛠️ 4. The Administration (The "Workshop")

### 📂 `public/admin/`
*   **`index.php` (The Dashboard)**
    *   **Logic:** This is the administrative homepage. It pulls high-level statistics directly from the database, such as Total Views, Published Articles, and Pending Users. It also highlights any "Actions Required," like new user registrations that need approval.

*   **`articles.php` (The News List)**
    *   **Logic:** This file displays a sortable and filterable table of every news story in the system. It uses complex SQL joins to show the author's name, category, and a thumbnail of the featured image. It also handles the "Soft Delete" logic when you remove an article from the list.

*   **`article_edit.php` (The Writing Desk)**
    *   **Word Count Logic:** This is unique to 60SecNews. It has a JavaScript `input` listener on the editor box. It counts words using a regex and **disables the save button** if the count goes over 150. There is also a second check in PHP just in case someone bypasses the JavaScript.
    *   **Revisions:** Every time you click "Save", it takes a "snapshot" of the old content and saves it in the `article_versions` table. This allows the `revisions.php` page to show you exactly what changed.

*   **`categories.php` (The Organizer)**
    *   **Logic:** Manages the news sections (Tech, Sports, etc.). It automatically generates "Slugs" (URL-friendly names) from the text you type. It also prevents you from deleting a category if it still has articles assigned to it to prevent "Unknown" news.

*   **`comments.php` (The Mailroom)**
    *   **Logic:** This is the moderation hub. It shows a queue of every visitor comment. The admin can "Approve" (make it public), "Reject" (hide it), or "Delete" it permanently. It also logs the commenter's IP address for security against spam.

*   **`media.php` (The Photo Library)**
    *   **Logic:** A full gallery of every uploaded image. It allows for direct uploads separated into folders. It includes a "Usage Check"—if you try to delete an image that is currently being used as a "Featured Image" on an article, the system will block the deletion to prevent broken images.

*   **`revisions.php` (The Time Machine)**
    *   **Logic:** This page lets you browse the "Snapshot" history of any article. It compares the current version with older ones saved in the `article_versions` table, allowing editorial teams to track changes and see exactly who edited what.

*   **`users.php` (The Team List)**
    *   **Logic:** Manages the user accounts for your staff. It shows who is active, banned, or pending. For security, it prevents users from deleting themselves or the "Super Provider" (the main site owner).

*   **`user_edit.php` (The Account Manager)**
    *   **Logic:** A specialized form to create new team members or update their passwords and roles. It uses `password_hash()` to securely encrypt passwords before they ever touch the database pantry.

*   **`settings.php` (The Master Switch)**
    *   **Logic:** Controls the global site behaviors. It can turn on "Maintenance Mode," change the main Site Title, or decide if visitors need an account to comment. These settings are saved in the `settings.json` file for instant updates.

*   **`themes.php` (The Wardrobe)**
    *   **Logic:** Allows the admin to change the visual theme of the site (e.g., **Premium**, **Professional**, or the new **Midnight Deep**) with one click. It updates the `settings.json` file and then triggers an automatic `clear_cache()` so that the new colors are visible to every visitor immediately.

*   **`logs.php` (The Safety Tape)**
    *   **Logic:** A security-focused page that lists every major action taken in the admin area (e.g., "Admin X deleted Article Y"). It records the timestamp, the user, the action, and the IP address to ensure full accountability for the editorial team.

*   **`clear_cache.php` (The Reset Button)**
    *   **Logic:** This is a background script that manually deletes every cached HTML snapshot in the `storage/cache/` folder. It is important for times when you make a huge site design change and need it to show up instantly for everyone.

### 🍱 `public/admin/layout/` (The Skeleton)
These files act as the "Frame" for every page in your workshop.

*   **`header.php`**
    *   **Logic:** The most hardworking layout file. It performs a **Security Check** at the very top of every page. It also builds the Sidebar Menu dynamically—only showing buttons (like "Settings" or "Users") if your role has permission to see them.
    *   **Badge System:** It automatically checks the database for "Pending Users" and displays a yellow warning badge in the sidebar to alert the admin.
*   **`footer.php`**
    *   **Logic:** Simply closes the open HTML tags and loads the JavaScript libraries needed for dropdowns and popups.

*   **.htaccess**
    *   **The Magic:** This file tells the Apache server: "If someone asks for a page that doesn't exist, don't show a 404. Instead, send them to `index.php`." This allows for **Clean URLs** like `yoursite.com/article/my-story` instead of `yoursite.com/article.php?id=123`.

---

## 🎨 5. Design & Aesthetics

### 📂 `public/css/`
*   **`style.css` (The Fashion Designer)**
    *   **Logic:** This file is the source of all "Vibrancy" in the project.
    *   **Glassmorphism:** Uses `backdrop-filter: blur()` and transparent backgrounds (`rgba`) to create the frosted-glass effect on cards and menus. The `.glass-panel` class is the key to this premium look.
    *   **Theming:** Uses **CSS Variables** (`--primary-color`, etc.). When you change the theme in the Admin panel, the site simply swaps these variables, changing the entire color scheme instantly.
    *   **Mesh Gradients:** Creates moving, artistic backgrounds using CSS animations (the `mesh-move` animation).

---

## 🕒 6. Background Automation (The "Clockwork")

### 📂 `scripts/`
*   **`daily_digest.php`**
    *   **How it works:** This is a "Robot" script. It is designed to run once a day (usually at 8:00 AM) using a server tool called a **Cron Job**. 
    *   **Logic:** It searches the database for the Top 5 most important news stories published in the last 24 hours. It then builds a beautiful HTML email and sends it to every person in your `newsletter_subscribers` list automatically.
    *   **Security:** This script can only be run from the server's command line (CLI), not by a web visitor. This prevents outsiders from triggering mass emails.

---

## 🧪 7. Behind the Scenes (Everything Else)
*   **`database/schema.sql`**: The **"Blueprint."** This file contains the SQL instructions to build your database tables from scratch. 
*   **`storage/cache/`**: The folder where "Snapshots" (HTML files) are stored to make the site lightning fast.
*   **`test_paths.php`**: A small developer tool to check if your server folders are working correctly and have the right "Write Permissions."

---

## 🛠️ 8. Developer Workflow Summary

To maintain the high quality and performance of **60SecNews**, follow this 3-step workflow when building new parts of the site:

### 1. Adding a New Feature (The Lifecycle)
*   **Database First:** If your feature needs to store new data (like "Followers" or "Polls"), start by updating the `database/schema.sql`. This ensures your "Pantry" is ready before you start "Cooking" the logic in PHP.
*   **Logic Second:** Put your core logic in `app/` (using `helpers.php` for small tools or new service files for larger ones). By keeping logic separate from the HTML templates, your code stays clean and easy to maintain.
*   **UI Third:** Finally, build the visual part in the `public/` folder. Use native PHP to pull the data and the predefined CSS classes to style it. If the feature is interactive (like a "Like" button), use the AJAX patterns found in `ajax_interaction.php` to update the page instantly without refreshing.

### 2. Security First (The Three Golden Rules)
*   **Prevent XSS (Cross-Site Scripting):** Whenever you display text that came from a user or a database, always wrap it in the `e()` function. 
    *   *Bad:* `echo $user_input;` 
    *   *Good:* `echo e($user_input);` (This stops hackers from sticking malicious `<script>` tags into your site).
*   **Prevent CSRF (Cross-Site Request Forgery):** Every form that submits data (POST) MUST include `<?= csrf_field() ?>`. This creates a secret security token that prevents external sites from tricking your users into performing actions they didn't intend to.
*   **Prevent SQL Injection:** Never put a variable directly inside a database query string. 
    *   *Bad:* `query("SELECT * FROM users WHERE id = $id")`
    *   *Good:* Use Prepared Statements: `prepare("SELECT * FROM users WHERE id = ?")->execute([$id])`. This ensures data is treated as data, not as code.

### 3. Aesthetics (The Design System)
*   **Glassmorphism:** To keep the "Premium" look, always wrap your cards, headers, and modals in the `.glass-panel` CSS class. This adds the frosted-glass effect automatically.
*   **CSS Variables:** Do not hardcode experimental colors (like `#ff0000`). Instead, use the variables at the top of `public/css/style.css` (e.g., `primary-color`). This way, if you want to rebrand the entire site, you only change it in one file.
*   **Iconography:** Use **Bootstrap Icons** (e.g., `<i class="bi bi-clock"></i>`) for a consistent, professional look across all admin and public pages.
