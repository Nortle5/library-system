# PCNL Library System

A web-based library system where students search and borrow books, and an administrator manages the collection.

- **Student:** [Kristine Hyacinth G. Rualo]
- **Course:** Web Application Development, Final Project
- **Live site:** [https://hyacinth-rualo-library-system.onrender.com]
- **Repository:** [(https://github.com/Nortle5/library-system)]

## Admin test login

Use this account to try the administrator side:

| Username | Password |
| --- | --- |
| `admin` | `administrator` |

Students create their own accounts from the **Register** page. Anyone can register, and registration always creates a student account, never an admin.

## Features

**Students**
- Register and log in
- Search books by title, author, or genre, with results that update as you type
- View a book's details in a pop-up and borrow it
- See current loans and full borrowing history on a profile page, and return books

**Administrator**
- Add, edit, and delete books from a pop-up form
- See how many copies of each book are on the shelf or on loan, and who has them
- Review every borrowing, filtered by All, Borrowed, or Returned
- Search the book table instantly

**Rules the system enforces**
- A student can't borrow a book when no copies are left, or borrow the same title twice at once
- Copies can't be taken twice at the same moment (the check and the update are one atomic database step)
- A book can't be deleted while any copy is borrowed
- Total copies can't be lowered below the number currently on loan

## Tech stack

| Part | Technology |
| --- | --- |
| Frontend | HTML5, Tailwind CSS v4, vanilla JavaScript |
| Backend | PHP 8.2 |
| Database | MongoDB Atlas (free tier) through the `mongodb/mongodb` PHP library |
| Hosting | Render (Docker, free plan) |

## Security

- Passwords are stored as hashes (`password_hash` / `password_verify`), never as text.
- Sessions are renewed at login, and the session cookie is `HttpOnly`, `SameSite=Lax`, and `Secure` on HTTPS.
- Every protected page checks the user's role on the server (`requireLogin()` and `requireAdmin()`).
- All output is escaped with `htmlspecialchars`, which prevents cross-site scripting.
- Every form is validated twice: in JavaScript for quick feedback, and again in PHP, which is the check that counts.
- **SQL injection:** this project uses MongoDB, so there is no SQL and no prepared statements. The equivalent protection is that queries have a fixed structure and user input is only ever used as a typed value (an `ObjectId`, a validated number, or an escaped search term), never as part of a query operator.
- `username` and `studentId` have unique database indexes, so duplicates are rejected even if two people register at the same instant.
- Database credentials are kept out of the repository. Locally they are in a git-ignored `config.php`; on Render they are environment variables.

## Project structure

```
index.php, login.php, register.php, logout.php, home.php
student/    books.php, profile.php
admin/      books.php, borrowings.php
actions/    borrow.php, return.php, delete-book.php, search-books.php
includes/   shared code: database connection, sessions, header and footer,
            icons, routes, book and borrowing logic
js/         script.js (all client-side JavaScript)
images/     logo and background photo
database/   schema.json, data exports, and setup scripts
Dockerfile  how Render builds the site
```

## Database

MongoDB database `library` with three collections. Field-by-field details are in `database/schema.json`.

| Collection | Purpose |
| --- | --- |
| `users` | Students and the single admin account |
| `books` | Title, author, genre, total copies, available copies |
| `borrowings` | One record per borrow (`userId`, `bookId`, dates, `status` of `borrowed` or `returned`) |

The `database/` folder also holds an export of each collection (`books.json`, `users.json`, `borrowings.json`) so the data can be loaded into a fresh database.
