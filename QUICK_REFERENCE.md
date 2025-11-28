# Quick Reference: Backend Implementation

## 🚀 **Fast Track Implementation**

Use this as a quick checklist when implementing any module.

---

## ✅ **Implementation Checklist**

### Before You Start
- [ ] Check if database migration exists
- [ ] Review existing models
- [ ] Check if controller exists
- [ ] Identify relationships needed

### Implementation Steps

#### 1️⃣ **Model** (`app/Models/[Module].php`)
```php
protected $fillable = ['field1', 'field2']; // ← Add all fields
protected $casts = ['field' => 'type'];     // ← datetime, boolean, json
// Add relationships
// Add helper methods
```

#### 2️⃣ **Controller** (`app/Http/Controllers/[Module]Controller.php`)
```php
public function index()   { /* List items */ }
public function store()   { /* Create with validation */ }
public function update()  { /* Update with business rules */ }
public function destroy() { /* Delete with business rules */ }
```

#### 3️⃣ **Routes** (`routes/web.php`)
```php
Route::get('[module]', [...]);
Route::post('[module]', [...]);
Route::put('[module]/{id}', [...]);
Route::delete('[module]/{id}', [...]);
```

#### 4️⃣ **Vue Component** (`resources/js/pages/admin/[module].vue`)
```typescript
// 1. Define interface
// 2. defineProps<{ items: Type[] }>()
// 3. const form = useForm({ ... })
// 4. Implement methods
// 5. Build template with modals
```

#### 5️⃣ **Test**
```bash
php artisan route:clear
npm run dev
# Test in browser
```

---

## 📋 **Code Templates**

### Controller Template
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\[Module];

class [Module]Controller extends Controller
{
    public function index()
    {
        $items = [Module]::latest()->get();
        return Inertia::render('admin/[module]', ['items' => $items]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'field' => 'required|string|max:255',
        ]);
        [Module]::create($validated);
        return redirect()->back()->with('success', 'Created successfully');
    }

    public function update(Request $request, [Module] $item)
    {
        $validated = $request->validate([...]);
        $item->update($validated);
        return redirect()->back()->with('success', 'Updated successfully');
    }

    public function destroy([Module] $item)
    {
        $item->delete();
        return redirect()->back()->with('success', 'Deleted successfully');
    }
}
```

### Vue Template
```vue
<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

interface Item {
    id: number;
    field: string;
}

const props = defineProps<{ items: Item[] }>();

const open = ref(false);
const editMode = ref(false);
const selectedItem = ref<Item | null>(null);

const form = useForm({ field: '' });

const openCreateModal = () => {
    editMode.value = false;
    form.reset();
    open.value = true;
};

const submitForm = () => {
    if (editMode.value && selectedItem.value) {
        form.put(route('admin.[module].update', selectedItem.value.id), {
            preserveScroll: true,
            onSuccess: () => { open.value = false; }
        });
    } else {
        form.post(route('admin.[module].store'), {
            preserveScroll: true,
            onSuccess: () => { open.value = false; }
        });
    }
};
</script>

<template>
    <AppLayout>
        <!-- Header + Button -->
        <!-- Modal -->
        <!-- List -->
    </AppLayout>
</template>
```

---

## 🎯 **Module Requirements Summary**

### ✅ Elections (DONE)
- Create, edit, delete, activate, deactivate
- Cannot edit after start
- One active at a time

### 🔄 Voters (NEXT)
- Import approved students manually
- Activate/deactivate
- Email verification required
- Can add during active election

### 📸 Candidates
- Add to positions with photo upload
- Party & platform info
- Cannot modify after election starts
- Local storage for photos

### 📢 Announcements
- Create for voters/candidates
- Publish/unpublish
- Rich text content

### 📊 Results
- Automatic vote counting
- Winner determination
- Visible to all after election ends
- Export capability

---

## 🔐 **Validation Rules Reference**

### Common Rules
```php
'required'              // Must be present
'nullable'             // Can be null
'string'               // Must be string
'max:255'              // Max length
'email'                // Valid email
'unique:table,column'  // Must be unique
'exists:table,column'  // Must exist in table
'date'                 // Valid date
'after:field'          // After another date
'before:field'         // Before another date
'integer'              // Must be integer
'min:value'            // Minimum value
'max:value'            // Maximum value
'in:value1,value2'     // Must be one of
'boolean'              // true/false
'image'                // Must be image
'mimes:jpg,png'        // Allowed file types
'max:2048'             // Max file size (KB)
```

### Example Validation
```php
$request->validate([
    'title' => 'required|string|max:255',
    'email' => 'required|email|unique:users,email',
    'start_date' => 'required|date|after:now',
    'photo' => 'nullable|image|mimes:jpg,png|max:2048',
    'status' => 'required|in:active,inactive',
]);
```

---

## 🎨 **UI Components Reference**

### Button Colors
```vue
<!-- Primary -->
<Button variant="default">Primary</Button>

<!-- Success -->
<Button class="bg-green-500 hover:bg-green-600">Success</Button>

<!-- Danger -->
<Button class="bg-red-500 hover:bg-red-600">Danger</Button>

<!-- Ghost -->
<Button variant="ghost">Ghost</Button>
```

### Status Badges
```vue
<!-- Active/Success -->
<span class="bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400 px-2 py-1 rounded-md text-xs font-medium">
    Active
</span>

<!-- Inactive/Default -->
<span class="bg-muted text-muted-foreground px-2 py-1 rounded-md text-xs font-medium">
    Inactive
</span>
```

### Icons
```vue
<Icon name="plus" />       <!-- Add -->
<Icon name="edit" />       <!-- Edit -->
<Icon name="trash" />      <!-- Delete -->
<Icon name="users" />      <!-- Users -->
<Icon name="calendar" />   <!-- Calendar -->
<Icon name="checkCircle" /> <!-- Success -->
<Icon name="xCircle" />    <!-- Error -->
```

---

## 🐛 **Common Issues & Fixes**

### Issue: 404 on route
```bash
php artisan route:clear
php artisan route:cache
```

### Issue: Props not received
- Check controller: `return Inertia::render('page', ['prop' => $data])`
- Check Vue: `defineProps<{ prop: Type }>()`

### Issue: Form not submitting
- Verify route name: `route('admin.module.store')`
- Check method: `form.post()` or `form.put()`

### Issue: Validation errors not showing
```vue
<p v-if="form.errors.field" class="text-xs text-red-500 mt-1">
    {{ form.errors.field }}
</p>
```

### Issue: Styles not applying
```bash
npm run build
# or
npm run dev
```

---

## 📊 **Relationship Types**

### One-to-Many
```php
// Parent Model
public function children()
{
    return $this->hasMany(Child::class);
}

// Child Model
public function parent()
{
    return $this->belongsTo(Parent::class);
}

// Usage
$parent->children;      // Get all children
$child->parent;         // Get parent
```

### Many-to-Many
```php
// Model 1
public function items()
{
    return $this->belongsToMany(Item::class);
}

// Model 2
public function owners()
{
    return $this->belongsToMany(Owner::class);
}

// Usage
$owner->items;
$item->owners;
```

### Has Many Through
```php
// e.g., Election -> Position -> Candidate
public function candidates()
{
    return $this->hasManyThrough(
        Candidate::class,
        Position::class
    );
}
```

---

## 🚀 **Quick Commands**

```bash
# Create Model
php artisan make:model ModelName -m

# Create Controller
php artisan make:controller ControllerName

# Create Migration
php artisan make:migration create_table_name

# Run Migrations
php artisan migrate

# Rollback
php artisan migrate:rollback

# Clear Everything
php artisan optimize:clear

# List Routes
php artisan route:list

# Tinker (Test Database)
php artisan tinker
> User::all()
> User::find(1)

# Storage Link
php artisan storage:link

# Build Assets
npm run build    # Production
npm run dev      # Development
```

---

## 💡 **Pro Tips**

1. **Use Eager Loading**
   ```php
   // Bad (N+1 problem)
   $items = Item::all();
   foreach ($items as $item) {
       echo $item->related->name;
   }
   
   // Good
   $items = Item::with('related')->all();
   ```

2. **Use Route Model Binding**
   ```php
   // Automatic
   public function update(Request $request, Election $election)
   {
       // $election is automatically loaded
   }
   ```

3. **Use Form Helpers**
   ```vue
   <Input v-model="form.field" />
   <p v-if="form.errors.field">{{ form.errors.field }}</p>
   ```

4. **Preserve Scroll on Actions**
   ```typescript
   form.post(route('...'), {
       preserveScroll: true,
       onSuccess: () => { /* ... */ }
   });
   ```

5. **Use Computed Properties**
   ```typescript
   const filteredItems = computed(() => 
       props.items.filter(item => item.status === 'active')
   );
   ```

---

## 📦 **File Upload Pattern**

### Backend
```php
public function store(Request $request)
{
    $request->validate([
        'photo' => 'required|image|mimes:jpg,png|max:2048',
    ]);

    $path = $request->file('photo')->store('candidates', 'public');

    Candidate::create([
        'photo' => $path,
        // other fields
    ]);
}
```

### Frontend
```vue
<input 
    type="file" 
    @change="form.photo = $event.target.files[0]"
    accept="image/*"
/>
```

---

## 🎯 **Next Steps**

1. ✅ Review this quick reference
2. 🔄 Open `BACKEND_WORKFLOW_GUIDE.md` for detailed workflow
3. 📖 Check `ELECTION_SETUP_COMPLETE.md` for working example
4. 🚀 Start implementing **Voters Module**
5. ✅ Follow the 7-step workflow
6. 🧪 Test thoroughly
7. 📝 Document your changes

---

**Good luck with your implementation! 🎉**
