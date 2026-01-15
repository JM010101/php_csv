# Vercel Deployment Guide

This guide will help you deploy the Time Clock System to Vercel.

## Prerequisites

1. A Vercel account (sign up at [vercel.com](https://vercel.com))
2. Vercel CLI installed (optional, but recommended)

## Quick Deployment

### Method 1: Using Vercel CLI

1. **Install Vercel CLI** (if not already installed):
   ```bash
   npm i -g vercel
   ```

2. **Navigate to the project directory**:
   ```bash
   cd php_csv
   ```

3. **Login to Vercel**:
   ```bash
   vercel login
   ```

4. **Deploy to preview**:
   ```bash
   vercel
   ```
   
   This will:
   - Ask you to link/create a project
   - Deploy to a preview URL
   - Give you a URL like: `https://your-project-xyz.vercel.app`

5. **Deploy to production**:
   ```bash
   vercel --prod
   ```

### Method 2: Using Vercel Dashboard

1. Go to [vercel.com](https://vercel.com) and sign in
2. Click "Add New Project"
3. Import your Git repository (GitHub, GitLab, or Bitbucket)
4. Vercel will automatically detect the PHP configuration
5. Click "Deploy"

## Important Notes

### Data Persistence

⚠️ **CRITICAL**: The `/tmp` directory on Vercel is **ephemeral**. This means:
- Data persists during function execution
- Data may be cleared between deployments
- Data is NOT shared across different function invocations in different regions

**For Production Use:**
- Consider migrating to a persistent database:
  - Vercel Postgres
  - Supabase
  - PlanetScale
  - MongoDB Atlas
  - Or any external database service

### Environment Variables

If you need to configure environment variables:

1. Go to your Vercel project dashboard
2. Navigate to Settings → Environment Variables
3. Add any required variables

The application automatically detects Vercel environment using the `VERCEL` environment variable.

### First-Time Setup

After deployment:

1. Visit your Vercel URL
2. Navigate to `/setup.php` to create your first admin user
3. **Important**: Delete `setup.php` after creating your admin user for security

Or manually create an admin user by accessing the CSV files (not recommended on Vercel due to ephemeral storage).

## Configuration Files

- `vercel.json` - Vercel configuration for PHP routing
- `.vercelignore` - Files to exclude from deployment
- `package.json` - Optional npm scripts for deployment

## Troubleshooting

### Issue: Data not persisting

**Solution**: The `/tmp` directory is ephemeral. For production, use a database.

### Issue: 500 errors

**Solution**: Check Vercel function logs in the dashboard. Ensure all PHP files are properly formatted.

### Issue: Session not working

**Solution**: Sessions work within the same execution environment. For better session management, consider using Vercel KV (Redis) or external session storage.

### Issue: File permissions

**Solution**: Vercel automatically handles permissions for `/tmp`. No manual configuration needed.

## Performance Considerations

- Vercel functions have a 10-second timeout on the Hobby plan
- Cold starts may occur on first request
- Consider using Vercel Edge Functions for better performance (requires refactoring)

## Security Recommendations

1. **Delete setup.php** after initial setup
2. **Use environment variables** for sensitive configuration
3. **Enable Vercel's DDoS protection**
4. **Consider adding rate limiting** for login endpoints
5. **Use HTTPS** (automatically enabled by Vercel)

## Support

For Vercel-specific issues, check:
- [Vercel PHP Documentation](https://vercel.com/docs/concepts/functions/serverless-functions/runtimes/php)
- [Vercel Community](https://github.com/vercel/vercel/discussions)
