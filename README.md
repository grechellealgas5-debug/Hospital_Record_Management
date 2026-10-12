# MediRecord - Hospital / Patient Record System

A full-stack web app where hospital staff manage patient information and medical records.

**Stack:** HTML5, CSS3 + Bootstrap 5, vanilla JavaScript, PHP (mysqli), MySQL (XAMPP).

## Features
- Staff registration / login with `password_hash()` and sessions (`session_regenerate_id` on login)
- Role-based access: **admin** and **staff**
  - Staff: add / view / edit patients, add records, edit or delete **their own** records
  - Admin: everything staff can do, plus delete patients, edit or delete any record, manage staff accounts
- Full CRUD on **patients** and on **medical records** (one patient has many records, linked by foreign key)
- Live search + filters (status, gender, blood type) using `fetch()` - no page reload
- Patient profile page with allergy warning, record history and a Print button
- Client-side validation (HTML5 + JS) and server-side validation (PHP)
- Security: prepared statements, `htmlspecialchars` / `textContent` against XSS, CSRF tokens,
  patient data hidden from anyone not logged in
- Responsive Bootstrap layout

## Run locally with XAMPP
1. Install XAMPP and start **Apache** and **MySQL**.
2. Copy this folder to `C:\xampp\htdocs\hospital` (macOS: `/Applications/XAMPP/htdocs/hospital`).
3. Open `http://localhost/phpmyadmin` > **Import** > choose `database/schema.sql` > **Go**.
   (This creates the `hospital_db` database, tables, test accounts and sample patients.)
4. Open `http://localhost/hospital/`.

Database settings are at the top of `config.php` (host `localhost`, user `root`, empty password).

## Test credentials
| Role | Email | Password |
|---|---|---|
| Admin | `admin@test.com` | `Admin@123` |
| Staff | `staff@test.com` | `Staff@123` |

If a login fails (e.g. the database was imported earlier or edited), open
`http://localhost/hospital/reset_admin.php` once, then delete that file.

## Files
| File | Purpose |
|---|---|
| `config.php` | DB connection, session, helpers |
| `header.php`, `footer.php`, `style.css` | Shared layout and theme |
| `register.php`, `login.php`, `logout.php` | Authentication |
| `index.php`, `search.php`, `app.js` | Patient list + live search |
| `patient_form.php`, `patient_view.php`, `patient_delete.php` | Patient CRUD |
| `record_form.php`, `record_delete.php` | Medical record CRUD |
| `admin.php` | Admin: manage staff accounts |
| `reset_admin.php` | One-time admin password reset (delete after use) |
| `database/schema.sql` | Database structure, test accounts, sample data |
