# Time Clock System - User Guide

## Quick Start

### First Time Setup

1. **Access the Admin Dashboard**
   - Go to `/admin.php` (currently auto-login is enabled for testing)
   - The dashboard will load directly

2. **Create Your First Admin User** (if needed)
   - Go to `/setup.php` to create an admin account
   - Or use the default credentials:
     - **Email:** `admin@timeclock.local`
     - **Password:** `1234`

## How to Use Each Role

### 👤 Employee

**Login:**
1. Go to the login page (`/`)
2. Enter your email address and 4-digit password
3. Click "Login"

**Clock In/Out:**
1. You'll see a simple screen with two buttons:
   - **CLOCK IN** - Click when you start work
   - **CLOCK OUT** - Click when you finish work
2. After clicking, you'll see a confirmation message for 5 seconds
3. The page will automatically log you out after 5 seconds

**Rules:**
- You can only clock in during your scheduled window (e.g., 08:00-08:30)
- If you're late, you'll see an error message - only your manager can override this
- You cannot clock out after your default clock-out time
- You can clock in/out twice per day
- You can clock in one day and clock out the next day (for overnight shifts)

### 👨‍💼 Manager

**Login:**
1. Go to the login page (`/`)
2. Enter your email address and 4-digit password
3. Click "Login"

**View Clocked-In Employees:**
1. After login, you'll see the Manager Dashboard
2. The page shows a table of all employees currently clocked in
3. Information displayed:
   - Employee Name
   - Clock In Time
   - Date
4. The page auto-refreshes every 30 seconds to show current status

**Note:** Managers can only access from their designated IP addresses (up to 5 IPs set by admin)

### 👑 Admin

**Login:**
1. Go to `/admin.php` (or login at `/`)
2. Enter your email address and 4-digit password
3. Click "Login"

**Admin Dashboard Sections:**

#### 1. Time Records (Dashboard)

**View All Records:**
- See all clock in/out records for all employees
- Records show: User ID, Name, Date, Clock In, Clock Out, Hours

**Filter Records:**
- **Filter by User ID:** Enter a user's email to see only their records
- **Search by Name:** Type part of a name to find matching records
- **Filter by Date:** Select a specific date
- Click "Filter" to apply, or "Clear" to reset

**Sort Records:**
- Click any column header to sort ascending
- Click again to sort descending
- Works for: User ID, Name, Date, Clock In, Clock Out, Hours

**Export to CSV:**
- Apply any filters you want
- Click "Export CSV" button
- A CSV file will download with the filtered records

**Add/Edit/Delete Records:**
- **Add:** Click "Add Time Record" button, fill in the form
- **Edit:** Click "Edit" button on any record
- **Delete:** Click "Delete" button (with confirmation)

#### 2. Users

**View All Users:**
- See list of all users (Admin, Manager, Employee)
- Shows: User ID, Name, Role, IP Addresses

**Search Users:**
- Type in the search box to filter users by name or user ID
- Results update as you type

**Add User:**
1. Click "Add User" button
2. Fill in the form:
   - **User ID (Email):** Employee's email address
   - **Password:** 4-digit numeric password
   - **Name:** Full name
   - **Role:** Select Admin, Manager, or Employee
   - **IP Addresses 1-5:** Enter allowed IP addresses (required for Manager/Employee, optional for Admin)
3. Click "Add User"

**Edit User:**
1. Click "Edit" button next to a user
2. Modify any fields
3. Click "Update User"

**Edit User Schedule:**
1. Click "Schedule" button next to a user
2. Set times for each day of the week:
   - **Clock In From:** Earliest time they can clock in
   - **Clock In To:** Latest time they can clock in
   - **Clock Out:** Default clock-out time (cannot clock out after this)
3. Click "Update Schedule"

**Clone User:**
1. Click "Clone" button next to a user
2. A new user form will open with the same settings
3. Change the email and name, then add

**Delete User:**
1. Click "Delete" button
2. Confirm deletion
3. **Warning:** This will also delete all their time records!

#### 3. Payroll

**Generate Payroll Report:**
1. Go to "Payroll" tab
2. Select date range:
   - **Date From:** Start date
   - **Date To:** End date
3. Click "Generate Report"

**Report Shows:**
- All time records within the date range
- Sorted by employee name, then by date
- Hours worked for each record
- **Subtotals** showing total hours per employee at the end of each employee's records

## Common Tasks

### Setting Up a New Employee

1. **Add the Employee:**
   - Go to Admin → Users
   - Click "Add User"
   - Enter email, password (4 digits), name, select "Employee" role
   - Enter their allowed IP addresses (up to 5)

2. **Set Their Schedule:**
   - Click "Schedule" button next to the new employee
   - Set Clock In From, Clock In To, and Clock Out for each day
   - Example: Monday 08:00-08:30 clock-in window, 17:00 clock-out

3. **Employee Can Now:**
   - Login with their email and password
   - Clock in/out according to their schedule

### Overriding a Late Clock-In

If an employee is late and tries to clock in outside their window:

1. Go to Admin → Time Records
2. Click "Add Time Record"
3. Select the employee
4. Enter the date and clock-in time manually
5. This overrides the window restriction

### Generating a Payroll Report

1. Go to Admin → Payroll
2. Enter the date range (e.g., first day to last day of the month)
3. Click "Generate Report"
4. Review the report showing:
   - All clock in/out records
   - Hours worked per day
   - Subtotals per employee

### Exporting Data

1. Go to Admin → Time Records
2. Apply any filters you want (optional)
3. Click "Export CSV"
4. The CSV file will download with all filtered records

## Tips & Best Practices

1. **IP Addresses:**
   - Managers and Employees must access from their designated IPs
   - Admins can access from anywhere
   - Get IP addresses from: https://whatismyipaddress.com/

2. **Schedules:**
   - Set realistic clock-in windows (e.g., 08:00-08:30)
   - Default clock-out time prevents employees from clocking out too late
   - Different schedules for different days (e.g., shorter hours on Friday)

3. **Passwords:**
   - Keep 4-digit passwords simple but secure
   - Employees can't change their own passwords (admin must do it)

4. **Time Records:**
   - Use filters to find specific records quickly
   - Export regularly for backup
   - Delete records carefully (cannot undo)

5. **Overnight Shifts:**
   - Employee clocks in at 22:00 on Monday
   - Employee clocks out at 06:00 on Tuesday
   - System calculates hours correctly (8 hours)

## Troubleshooting

**"Access denied: You are not authorized to access from this IP address"**
- Your IP address is not in your allowed list
- Contact admin to add your current IP address

**"You are late and cannot work today"**
- You're trying to clock in outside your scheduled window
- Contact your manager to manually clock you in

**"Cannot clock out after default clockout time"**
- You're trying to clock out after your scheduled time
- Clock out before your default time, or contact admin

**Blank page or errors:**
- Clear browser cookies
- Try a different browser
- Check if you're logged in (try logging out and back in)

## Security Notes

- Change default admin password after first login
- Don't share passwords
- Log out when done (especially on shared computers)
- Admins: Delete setup.php after initial setup

## Need Help?

- Check the README.md for technical details
- Review REQUIREMENTS_CHECKLIST.md for feature list
- Contact your system administrator for access issues
