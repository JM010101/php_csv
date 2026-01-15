# Quick Start Guide - How to Test Employee Page

## The Issue

If you're seeing the admin page after login, it's because you're logging in with an **admin account**. The system redirects you based on your role:
- **Admin** → `/admin.php`
- **Manager** → `/manager.php`  
- **Employee** → `/employee.php`

## How to See the Employee Clock In/Out Page

### Option 1: Create an Employee Account

1. **Go to Admin Dashboard** (`/admin.php`)
2. **Click "Users" tab**
3. **Click "Add User" button**
4. **Fill in the form:**
   - **User ID (Email):** `employee@test.com`
   - **Password:** `1234` (4 digits)
   - **Name:** `Test Employee`
   - **Role:** Select `Employee`
   - **IP Addresses:** Leave empty for now (or add your current IP)
5. **Click "Add User"**
6. **Set Schedule:**
   - Click "Schedule" button next to the new employee
   - Set times for each day (e.g., Clock In From: 08:00, Clock In To: 08:30, Clock Out: 17:00)
   - Click "Update Schedule"

7. **Logout** (click logout link)
8. **Login with employee credentials:**
   - Email: `employee@test.com`
   - Password: `1234`
9. **You'll be redirected to `/employee.php`** - the clock in/out page!

### Option 2: Test Directly

1. **Create an employee account** (as above)
2. **Go directly to** `/employee.php`
3. **If not logged in, you'll be redirected to login**
4. **Login with employee credentials**
5. **You'll see the employee clock in/out page**

## What You'll See on Employee Page

- **Welcome message** with your name
- **Status:** Shows if you're "Clocked In" or "Clocked Out"
- **Clock In Time** (if clocked in)
- **Large button:**
  - **"CLOCK IN"** (green) - if you're not clocked in
  - **"CLOCK OUT"** (red) - if you're clocked in
- **Logout link** at the bottom

## Testing the Clock In/Out

1. **Click "CLOCK IN"**
   - You'll see: "Clocked in successfully at [time]"
   - Status changes to "Clocked In"
   - Button changes to "CLOCK OUT"
   - After 5 seconds, page auto-refreshes and logs you out

2. **Click "CLOCK OUT"**
   - You'll see: "Clocked out successfully at [time]"
   - Status changes to "Clocked Out"
   - Button changes back to "CLOCK IN"
   - After 5 seconds, page auto-refreshes and logs you out

## Important Notes

- **IP Address:** If you get "Access denied" error, you need to add your IP address to the employee's allowed IPs in the admin panel
- **Clock-in Window:** You can only clock in during your scheduled window (e.g., 08:00-08:30)
- **Clock-out Time:** You cannot clock out after your default clock-out time

## Current Status

- **Admin page** (`/admin.php`) has auto-login enabled for testing
- **Employee page** (`/employee.php`) requires proper employee login
- **Login page** (`/`) redirects based on role after login

Try creating an employee account and logging in with those credentials to see the employee clock in/out page!
