# Logout Function Added! 🚪

## What Was Added:

### **Logout Button on Voter Dashboard**
- ✅ Located in the top-right navigation bar
- ✅ Styled with outline variant for secondary action
- ✅ Uses Inertia.js router for seamless logout

### **Logout Button on Candidate Dashboard**
- ✅ Same implementation as voter dashboard
- ✅ Consistent UI/UX across both dashboards

---

## How It Works:

### **Frontend (Vue)**
```typescript
import { router } from '@inertiajs/vue3';

const handleLogout = () => {
    router.post('/logout');
};
```

### **Backend (Laravel Fortify)**
- Route: `POST /logout`
- Handled by: `Laravel\Fortify\Http\Controllers\AuthenticatedSessionController`
- Action:
  1. Invalidates user session
  2. Regenerates CSRF token
  3. Redirects to homepage or login

---

## Testing the Logout:

### Test 1: Voter Logout
1. Login as voter: `voter@icsa.com` / `password`
2. You'll be at: `/voter/dashboard`
3. Click the **"Logout"** button in top-right
4. Expected: Redirected to homepage, session cleared

### Test 2: Candidate Logout
1. Login as candidate: `candidate@icsa.com` / `password`
2. You'll be at: `/candidate/dashboard`
3. Click the **"Logout"** button in top-right
4. Expected: Redirected to homepage, session cleared

### Test 3: Verify Session Cleared
1. After logout, try accessing: `/voter/dashboard`
2. Expected: Redirected to `/login` (because not authenticated)

---

## UI Location:

```
┌─────────────────────────────────────────────────┐
│  ICSA Voting System    [Voter Dashboard] [Logout] │
└─────────────────────────────────────────────────┘
```

The logout button is in the **top-right navigation bar**, next to the dashboard label.

---

## Files Modified:

1. **`resources/js/pages/voter/Dashboard.vue`**
   - Added logout button
   - Added handleLogout function
   - Imported Button component and router

2. **`resources/js/pages/candidate/Dashboard.vue`**
   - Added logout button
   - Added handleLogout function
   - Imported Button component and router

---

## Features:

✅ **One-Click Logout** - Simple button click to logout  
✅ **Session Invalidation** - Properly clears user session  
✅ **CSRF Protection** - Fortify handles CSRF token automatically  
✅ **Redirect to Home** - After logout, user goes to homepage  
✅ **Consistent UI** - Same logout implementation for both roles  

---

## Next Steps (Optional Enhancements):

### 1. Add Logout Confirmation Dialog:
```vue
<script setup>
import { ref } from 'vue';

const showLogoutDialog = ref(false);

const confirmLogout = () => {
    router.post('/logout');
    showLogoutDialog.value = false;
};
</script>
```

### 2. Add Loading State:
```vue
<script setup>
import { ref } from 'vue';

const isLoggingOut = ref(false);

const handleLogout = () => {
    isLoggingOut.value = true;
    router.post('/logout', {}, {
        onFinish: () => {
            isLoggingOut.value = false;
        }
    });
};
</script>
```

### 3. Show User Info Before Logout:
```vue
<template>
    <div class="flex items-center space-x-4">
        <span>{{ $page.props.auth.user?.name }}</span>
        <Button @click="handleLogout">Logout</Button>
    </div>
</template>
```

---

## Admin Dashboard Note:

The **admin dashboard** likely already has a logout button since it uses the `AppLayout` component. If you need to add it there too, let me know!

---

**Your voter and candidate dashboards now have working logout functionality! 🎉**

Ready to test it out?
