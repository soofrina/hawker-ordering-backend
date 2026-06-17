# HawkerGo User Guide

This guide explains how to use the **Customer**, **Hawker**, and **Admin** parts of HawkerGo.

---

## 1. Getting started

### Requirements
- XAMPP (Apache + MySQL)
- Database `hawker` imported from `DATABASE FILE/hawker.sql`

### Main URLs
| Role | URL |
|------|-----|
| Customer | `/Hawkergo/customer/` |
| Hawker | `/Hawkergo/hawker/` |
| Admin | `/Hawkergo/admin/` |

---

## 2. Customer portal

**Purpose:** Browse hawker stalls, order food, manage your account.

### Browse & order
1. Open the customer home page.
2. Click **Hawker Stalls** or pick a stall from the home page.
3. View dishes, add items to **My Cart**.
4. Open **My Cart** → adjust quantities → **Checkout**.
5. Sign in, register, or continue as **guest** (guest checkout supported).
6. Choose dine-in or take-away, payment method, and place the order.
7. Note your **queue number** on the thank-you page.

### Account
- **Register** — Create account (username, name, email, phone, password).
- **Login** — Sign in to save details and view order history.
- **Forgot password** — Reset via username or email (reset link shown on screen).
- **My Orders** — Track status; leave **reviews** after order is completed.
- **My Account** — View profile details.
- **Track Guest Order** — Look up a guest order without an account.

### Order status (customer view)
- **In process** — Stall is preparing your order.
- **Ready for collection** — Pick up at the stall.
- **Cancelled** — Order was rejected.

---

## 3. Hawker portal

**Purpose:** Each hawker stall manages its own orders, menu, and sales.

### Sign in
1. Go to **Hawker portal** login.
2. **Select your stall** (name and photo from the database).
3. Enter your password.

### Register (new stall account)
1. Open **Register new hawker**.
2. Pick an **available stall** (stalls that already have an account are greyed out).
3. Choose username, optional email, and password.

### Forgot password
1. Select your **stall name**.
2. Generate a reset link and set a new password.

### Dashboard & menu
- **Dashboard** — Today’s orders and quick stats.
- **Orders** — Accept, mark preparing, complete, or cancel orders.
- **Menu** — Mark dishes as sold out.
- **Stall settings** — Opening hours and details.
- **Statements** — Daily/monthly sales reports.
- **Report payment issue** — Flag a customer payment problem for admin.

### Ordering control
Admin can disable ordering for a stall globally; hawkers see status on their dashboard.

---

## 4. Admin portal

**Purpose:** Manage the whole HawkerGo system.

### Sign in
- Use your **admin username and password** (created via Register or seeded in the database).

### Forgot / register admin
- **Register new admin** — Create another admin account.
- **Forgot password** — Reset using the admin **email** on file.

### Main tasks
| Section | What you can do |
|---------|-----------------|
| **Dashboard** | Overview counts (stalls, dishes, users, orders) |
| **Users** | View, edit, delete customer accounts |
| **Hawker Stalls** | Add/edit stalls, categories, upload stall images |
| **Menu** | Add/edit dishes and photos |
| **Orders** | View all orders, update status, delete |
| **Ordering Control** | Turn ordering on/off per stall |
| **Payment Issues** | Review reports from hawkers |
| **Statements** | Revenue reports by day/month and stall |

### Adding a new hawker stall
1. **Add Category** (if needed).
2. **Add Hawker Stall** — title, contact, hours, image.
3. Hawker registers at the Hawker portal and links to that stall, **or** a seed account may exist until claimed.

---

## 5. Reviews

- Customers can review **dishes** and **stalls** from **My Orders** after an order is **completed**.
- Reviews appear on the home page, stall pages, and dish listings.

---

## 6. Folder reference

```
customer/     All customer-facing pages
hawker/       Hawker stall portal
admin/        Admin panel + uploaded images (Res_img/)
includes/     Shared PHP (orders, reviews, auth, schema)
connection/   Database config
docs/         This user guide
```

---

## 7. Troubleshooting

| Problem | Check |
|---------|--------|
| Blank page / DB error | MySQL running; database `hawker` imported; `connection/connect.php` settings |
| Images missing | Files exist in `admin/Res_img/` and `admin/Res_img/dishes/` |
| Cart quantity resets | Use **My Cart** checkout flow; quantities sync automatically |
| Hawker cannot register | Stall may already have an account — use **Forgot password** or add a new stall in admin |
| Payment issue not visible | Hawker must submit from **Report payment**; admin checks **Payment Issues** |

---

## 8. Support flow summary

```
Customer orders → Hawker prepares order → Customer collects
                      ↓
              Payment issue? → Hawker reports → Admin resolves
```

For technical setup details, see **README.md** in the project root.
