# Quick Test Checklist - Routing & Middleware

## ✅ Issue 1: Candidate Results Election Selector

**Test Steps:**
1. Login as candidate
2. Navigate to: `http://localhost/candidate/results`
3. Click "Change Election" button
4. Select a different election from the modal
5. Click the election

**Expected Result:** ✅ Page loads new election results without 403 error

**Old Behavior:** ❌ 403 Unauthorized. Voter access only.

---

## ✅ Issue 2: Auto-Redirect for Authenticated Users

### Test A: Voter Already Logged In
1. Login as voter
2. Visit: `http://localhost/voter/dashboard`
3. Close the browser tab (DO NOT LOGOUT)
4. Open new tab
5. Visit: `http://localhost/login`

**Expected Result:** ✅ Automatically redirects to `/voter/dashboard`

**Old Behavior:** ❌ Shows login page

### Test B: Candidate Already Logged In
1. Login as candidate
2. Visit: `http://localhost/candidate/dashboard`
3. Close the browser tab (DO NOT LOGOUT)
4. Open new tab
5. Visit: `http://localhost/login`

**Expected Result:** ✅ Automatically redirects to `/candidate/dashboard`

**Old Behavior:** ❌ Shows login page

### Test C: Admin Already Logged In
1. Login as admin
2. Visit: `http://localhost/admin/dashboard`
3. Close the browser tab (DO NOT LOGOUT)
4. Open new tab
5. Visit: `http://localhost/login`

**Expected Result:** ✅ Automatically redirects to `/admin/dashboard`

**Old Behavior:** ❌ Shows login page

---

## ✅ Issue 3: /dashboard Route Redirect

### Test D: Voter Visits /dashboard
1. Login as voter
2. Visit: `http://localhost/dashboard`

**Expected Result:** ✅ Redirects to `/voter/dashboard`

**Old Behavior:** ❌ Tries to show admin dashboard (error)

### Test E: Candidate Visits /dashboard
1. Login as candidate
2. Visit: `http://localhost/candidate/dashboard`

**Expected Result:** ✅ Redirects to `/candidate/dashboard`

**Old Behavior:** ❌ Tries to show admin dashboard (error)

### Test F: Admin Visits /dashboard
1. Login as admin
2. Visit: `http://localhost/dashboard`

**Expected Result:** ✅ Redirects to `/admin/dashboard`

**Old Behavior:** ✅ Already worked (no change needed)

---

## Additional Tests

### Test G: Role Protection Still Works
1. Login as voter
2. Try to visit: `http://localhost/admin/dashboard`

**Expected Result:** ✅ 403 Unauthorized. Admin access only.

### Test H: Candidate Can't Access Voter Routes
1. Login as candidate
2. Try to visit: `http://localhost/voter/vote`

**Expected Result:** ✅ 403 Unauthorized. Voter access only.

### Test I: Voter Can't Access Candidate Routes
1. Login as voter
2. Try to visit: `http://localhost/candidate/dashboard`

**Expected Result:** ✅ 403 Unauthorized. Candidate access only.

---

## Success Criteria

✅ **All tests pass**
✅ **No 403 errors on legitimate actions**
✅ **Auto-redirect works for all roles**
✅ **Role protection still enforced**
✅ **Login flow works correctly**

---

## Quick Debug Commands

If something doesn't work:

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild assets
npm run build

# Check routes
php artisan route:list
```

---

## Common Issues & Solutions

### Issue: Still seeing 403 on candidate results
**Solution:** Clear browser cache and rebuild assets
```bash
npm run build
```

### Issue: Not redirecting when logged in
**Solution:** Check if session is active
```bash
php artisan cache:clear
# Then logout and login again
```

### Issue: /dashboard not redirecting
**Solution:** Clear route cache
```bash
php artisan route:clear
```

---

## Browser Testing

Test in multiple scenarios:
- ✅ Fresh login
- ✅ Session persisted (tab closed, not logged out)
- ✅ Incognito/Private mode
- ✅ Different browsers

---

## Final Verification

Run through this quick flow:

1. **Voter Flow:**
   - Login → Dashboard → Close tab → Reopen `/login` → Auto-redirect ✅

2. **Candidate Flow:**
   - Login → Results → Change election → Works ✅
   - Close tab → Reopen `/login` → Auto-redirect ✅

3. **Admin Flow:**
   - Login → Dashboard → Close tab → Reopen `/login` → Auto-redirect ✅

**All pass?** 🎉 Everything is working perfectly!
