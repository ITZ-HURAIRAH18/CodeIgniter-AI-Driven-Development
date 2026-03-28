# Login Troubleshooting Guide

## Problem
When clicking "Sign In", nothing happens and no error is displayed.

## Root Cause
Your **CodeIgniter backend at `http://localhost:8081` is not running or not responding**.

## Solution

### Step 1: Start the CodeIgniter Backend

Open a terminal and run:

```bash
cd e:\My Projects\CodeIgniter + AI-Driven Development

# Start the development server on port 8081
php spark serve --port 8081
```

You should see:
```
CodeIgniter v4.x.x Command Line Tool - Server Time: 2026-03-28 12:00:00 UTC

Server running on http://127.0.0.1:8081
Press Control + C to stop
```

### Step 2: Verify Backend is Running

Open your browser and navigate to:
```
http://localhost:8081/
```

You should see:
```json
{
  "status": "API Running",
  "version": "1.0.0",
  "endpoint": "/api/v1"
}
```

If you see a connection error, the backend is NOT running. Go back to Step 1.

### Step 3: Test the Auth Endpoint

With the backend running, try the test endpoint:
```
http://localhost:8081/api/v1/test/health
```

You should see:
```json
{
  "success": true,
  "message": "Health check passed",
  "data": {
    "status": "ok",
    "timestamp": "2026-03-28T12:00:00Z"
  }
}
```

### Step 4: Open Browser Console

When testing login:
1. Open DevTools: Press **F12**
2. Go to **Console** tab
3. Look for messages like:
   - `🔐 Login attempt:` - shows email
   - `❌ Full error object:` - shows actual error
   - `✅ Login successful:` - success

### Step 5: Test Login Again

1. Keep backend running in one terminal
2. In browser, go to `http://localhost:5173/login`
3. Click a demo account (e.g., "System Admin")
4. Click "Sign In"
5. Check the console for messages

## Common Errors

### "Cannot POST /api/v1/auth/login"
- ✅ Backend is running but route not found
- Check: Did you run migrations and seed database?

### "Connection refused" or "ECONNREFUSED"
- ✅ Backend is NOT running
- Solution: Run `php spark serve --port 8081` in terminal

### "Empty response" or "No data returned"
- ✅ Backend might be returning wrong format
- Check: AuthService might not have user data
- Solution: Check `app/Services/AuthService.php`

### CORS Error in Console
- ✅ Frontend and backend ports don't match
- Current: Frontend = 5173, Backend = 8081 ✅ Correct

## File Locations

**Backend:**
- Routes: `app/Config/Routes.php`
- Auth Controller: `app/Controllers/Api/V1/AuthController.php`
- Auth Service: `app/Services/AuthService.php`

**Frontend:**
- API Config: `frontend/src/api/axios.js`
- Auth Store: `frontend/src/store/auth.store.js`
- Login View: `frontend/src/views/Auth/LoginView.vue`
- API URL: `frontend/.env` → `VITE_API_URL=http://localhost:8081/api/v1`

## Next Steps

1. ✅ Start backend with `php spark serve --port 8081`
2. ✅ Verify it's running at `http://localhost:8081/`
3. ✅ Open browser console (F12 → Console tab)
4. ✅ Try logging in with demo credentials
5. ✅ Share the console error message if it still doesn't work

---

**If backend is running but still getting errors**, share the console error message starting with `❌ Full error object:` and I'll help debug further! 🚀
