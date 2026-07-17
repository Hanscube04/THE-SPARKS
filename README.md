# THE SPARKS — Computer Sales, Maintenance & Repair System

CBE — BIT 2 — Internet and Web Development (Individual Assignment)

## 1. Problem Background
Small computer shops in Tanzania handle sales, spare parts, and repair
services largely on paper or via WhatsApp, leading to lost records, no
audit trail on repairs, and no online presence for customers. THE SPARKS
digitizes: (1) computer/parts **sales**, and (2) **maintenance & repair**
request tracking, with separate customer and staff portals.

## 2. Objectives
- Allow customers to browse/search products, place orders, and submit repair requests online.
- Allow staff (Admin) to manage products, process orders, and manage the repair workflow.
- Restrict staff account creation to a Super Admin only (no public admin signup).
- Apply OOP principles and secure, encrypted data storage per assignment requirements.

## 3. User Roles
| Role | How account is created | Key permissions |
|---|---|---|
| **User (Customer)** | Self-registers via `/views/public/register.php` | Browse/search products, place orders, submit & track repair requests |
| **Admin** | Created only by a Super Admin | Manage products, update order status, manage repair workflow |
| **Super Admin** | Seeded once via `database/create_first_superadmin.php` | Everything Admin can do + create/disable/enable Admin accounts |

## 4. OOP Concepts Implemented
- **Abstraction** — `classes/Person.php` is an abstract class defining the contract (`getDashboardUrl()`, `getRole()`) every account type must fulfil.
- **Encapsulation** — properties are `protected`/`private`; access only via getters or controlled methods (e.g. `setPassword()`).
- **Inheritance** — `User`, `Admin` extend `Person`; `SuperAdmin` extends `Admin` (multi-level inheritance).
- **Polymorphism** — `getDashboardUrl()` and `getRole()` behave differently in `User`, `Admin`, `SuperAdmin`.
- **Singleton** — `config/Database.php` ensures a single shared PDO connection.

## 5. Database Design (3NF)
See `database/thesparks_schema.sql`. Ten tables: `users`, `admins`, `categories`,
`products`, `orders`, `order_items`, `repair_requests`,
`repair_status_history`, `payments`, `activity_logs`. Repeating groups are
split into child tables (e.g. `order_items`, `repair_status_history`) and
every non-key column depends only on its table's primary key.

## 6. Security / Encryption Approach
- Passwords: hashed with **bcrypt** (`password_hash`/`password_verify`) — never stored or compared in plain text.
- Sensitive PII (phone, address): encrypted with **AES-256-CBC** in `config/Encryption.php` before being written to the database, and decrypted only when an authorized view needs to display it.
- **Key management**: the secret key currently lives as a constant in `Encryption.php` for simplicity; for production/AWS deployment, move it to an environment variable (e.g. `getenv('THESPARKS_SECRET_KEY')`) so it is never committed to Git.
- A random IV is generated per encryption call and stored alongside the ciphertext (base64), so identical plaintexts never produce identical ciphertexts.
- All DB queries use **PDO prepared statements** to prevent SQL injection.
- Role-based route guarding via `Auth::requireRole()` on every controller action.

## 7. Setup (local / XAMPP)
1. Copy the `TheSparks` folder into `C:\xampp\htdocs\`.
2. Import `database/thesparks_schema.sql` via phpMyAdmin (or `mysql -u root thesparks_db < thesparks_schema.sql`).
3. Update credentials in `config/Database.php` if needed.
4. Run once: `php database/create_first_superadmin.php` to create the first Super Admin (prints email/password).
5. Visit `http://localhost/TheSparks/` — customer site. Staff login at `views/public/admin_login.php`.

## 8. AWS Deployment Notes
1. Launch an EC2 Ubuntu instance; install Apache, PHP 8+, MySQL (or use RDS).
2. Upload the project (e.g. via `git clone` or `scp`) to `/var/www/html/TheSparks`.
3. **Watch for Linux case-sensitivity**: ensure every `require`/`include` path matches the exact case of the file/class name (this caused issues on previous CBE projects when moving from Windows/XAMPP to Ubuntu).
4. Import the schema into MySQL/RDS, run the super-admin seed script.
5. Set folder permissions for `public/uploads` (`chmod 755`, owned by `www-data`).
6. Point the EC2 security group to allow inbound HTTP (80) and HTTPS (443).
7. Test the live URL, then use it for your presentation demo.

## 9. Testing Evidence (fill in during testing)
- [ ] Customer registration & login
- [ ] Duplicate email rejected on registration
- [ ] Admin/Super Admin login (staff cannot self-register — verified no public form exists for staff)
- [ ] Super Admin can create/disable/enable Admin accounts
- [ ] Product CRUD (create/edit/delete) by Admin
- [ ] Product search by keyword
- [ ] Order placed reduces stock correctly; insufficient stock is rejected
- [ ] Repair request submitted, status updated through workflow, history logged
- [ ] Encrypted fields (phone/address) unreadable directly in DB, decrypt correctly in app

## 10. Challenges & Recommendations
Document here after testing: e.g. Linux case-sensitivity fixes needed,
AWS security group configuration, image upload path issues, etc.
