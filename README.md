# Time Clock System

A simple PHP-based time clock application with CSV file storage. This system allows employees to clock in/out, managers to view who's currently working, and administrators to manage users and generate payroll reports.

## Features

- **Three User Roles:**
  - **Admin**: Full system access, can manage users, time records, and generate reports
  - **Manager**: Can view currently clocked-in employees
  - **Employee**: Can clock in/out (only screen they can access)

- **Security Features:**
  - IP address restrictions for Manager and Employee roles (up to 5 IPs)
  - Admin can access from any IP
  - Auto-logout after 120 seconds for Admin and Manager
  - 4-digit numeric passwords
  - Email-based user IDs (stored in lowercase)

- **Time Clock Features:**
  - Clock-in window enforcement (must clock in within scheduled window)
  - Default clock-out time (cannot clock out after default time)
  - Separate schedules for all 7 days of the week
  - Support for overnight shifts (clock in one day, clock out next day)
  - Can clock in/out twice per day
  - Military time (24-hour format) throughout

- **Admin Features:**
  - User management (Add/Update/Delete/Clone)
  - Schedule management per user
  - Time record management (Add/Update/Delete)
  - Filter and search time records
  - Export time records to CSV
  - Payroll reports with date range filtering and subtotals by employee

## Requirements

- PHP 7.0 or higher
- Web server (Apache, Nginx, or PHP built-in server)
- Write permissions for the `data` directory

## Installation

1. Upload all files to your web server directory

2. Ensure the web server has write permissions for the `data` directory:
   ```bash
   chmod 777 data
   chmod 777 data/schedules
   ```

3. Access the application through your web browser:
   ```
   http://your-domain/index.php
   ```

## Initial Setup

The system will automatically create the necessary CSV files and directories on first run. However, you'll need to create an initial admin user manually.

### Creating the First Admin User

You can create the first admin user by manually adding a record to `data/users.csv`:

1. The CSV file should have this header:
   ```
   userid,password,name,role,ip1,ip2,ip3,ip4,ip5
   ```

2. Add an admin user (example):
   ```
   admin@example.com,1234,Administrator,Admin,,,,
   ```

   - `userid`: Email address (will be converted to lowercase)
   - `password`: 4-digit numeric password
   - `name`: Full name
   - `role`: Admin, Manager, or Employee
   - `ip1-ip5`: Allowed IP addresses (leave empty for Admin, required for Manager/Employee)

3. Create a schedule file for the admin at `data/schedules/admin@example.com.csv`:
   ```
   day,clockin_from,clockin_to,clockout
   Monday,08:00,08:30,17:00
   Tuesday,08:00,08:30,17:00
   Wednesday,08:00,08:30,17:00
   Thursday,08:00,08:30,17:00
   Friday,08:00,08:30,17:00
   Saturday,08:00,08:30,17:00
   Sunday,08:00,08:30,17:00
   ```

Alternatively, you can use the admin interface to create users after logging in with the manually created admin account.

## Usage

### Employee Login
1. Go to the login page
2. Enter your email (userid) and 4-digit password
3. You'll be taken to the clock in/out screen
4. Click "CLOCK IN" or "CLOCK OUT" as needed
5. The screen will show a confirmation message for 5 seconds, then auto-logout

### Manager Login
1. Login with manager credentials
2. View the list of currently clocked-in employees
3. The page auto-refreshes every 30 seconds

### Admin Login
1. Login with admin credentials
2. Access three main sections:
   - **Time Records**: View, filter, search, add, edit, delete time records. Export to CSV.
   - **Users**: Manage users, set schedules, clone user profiles
   - **Payroll**: Generate payroll reports with date range filtering

## File Structure

```
php_csv/
├── index.php              # Login page
├── employee.php           # Employee clock in/out interface
├── manager.php            # Manager dashboard
├── admin.php              # Admin dashboard
├── logout.php             # Logout handler
├── config.php             # Configuration and initialization
├── functions.php          # Core functions
├── get_schedule.php       # API endpoint for schedule data
├── get_user.php           # API endpoint for user data
├── style.css              # Stylesheet
├── data/                  # Data directory (auto-created)
│   ├── users.csv          # User database
│   ├── timeclock.csv      # Time records database
│   └── schedules/         # User schedules directory
│       └── {userid}.csv  # Individual user schedules
└── README.md              # This file
```

## Important Notes

1. **IP Address Validation**: Manager and Employee users must access from one of their 5 designated IP addresses. Admin users can access from any IP.

2. **Clock-in Window**: Employees must clock in within their scheduled window (CLOCKIN-FROM to CLOCKIN-TO). If they're late, only a manager can manually clock them in through the admin interface.

3. **Clock-out Time**: Employees cannot clock out after their default clock-out time. They can clock out earlier.

4. **Overnight Shifts**: The system supports overnight shifts. If an employee clocks in on one day and clocks out the next day, the hours are calculated correctly.

5. **Case Sensitivity**: All user IDs are stored in lowercase to avoid case-sensitivity issues.

6. **Session Timeout**: Admin and Manager sessions expire after 120 seconds of inactivity. Employee sessions do not timeout automatically.

7. **Data Storage**: All data is stored in CSV files. For production use with many users, consider migrating to a proper database.

## Troubleshooting

- **Permission Errors**: Ensure the `data` directory and its subdirectories have write permissions
- **IP Access Denied**: Check that your IP address is in the user's allowed IP list (for Manager/Employee roles)
- **Cannot Clock In**: Verify you're within the clock-in window for today
- **Cannot Clock Out**: Ensure you're clocked in and it's before your default clock-out time

## Security Considerations

This is a simple time clock system designed for internal use. For production environments, consider:

- Using a proper database instead of CSV files
- Implementing password hashing
- Adding HTTPS/SSL encryption
- Implementing more robust session management
- Adding audit logging
- Implementing rate limiting

## License

This project is provided as-is for internal use.
