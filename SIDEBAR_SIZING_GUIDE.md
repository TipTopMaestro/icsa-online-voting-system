# Sidebar Font Size, Icon Size, Spacing & Margins Guide

## File Locations

### 1. Navigation Items (Dashboard, Result, Election, etc.)
**File:** `resources/js/components/ui/sidebar/index.ts`
**Line 39:** `sidebarMenuButtonVariants`

### 2. "Platform" Label
**File:** `resources/js/components/ui/sidebar/SidebarGroupLabel.vue`
**Line 19:** Class definitions

### 3. Sidebar Group Wrapper
**File:** `resources/js/components/NavMain.vue`
**Line 21:** `<SidebarGroup class="px-2 py-0">`

---

## Current Styling Breakdown

### Navigation Menu Items (Dashboard, Result, etc.)

**Base classes (Line 39 in index.ts):**
```
gap-2          → Gap between icon and text (0.5rem = 8px)
p-2            → Padding all sides (0.5rem = 8px)
text-sm        → Font size (0.875rem = 14px)
[&>svg]:size-4 → Icon size (1rem = 16px)
```

**Size variants (Lines 48-50):**
```css
default: h-8 text-sm     → Height 32px, Font 14px
sm:      h-7 text-xs     → Height 28px, Font 12px
lg:      h-12 text-sm    → Height 48px, Font 14px
```

### "Platform" Label

**Classes (SidebarGroupLabel.vue, Line 19):**
```
h-8       → Height (2rem = 32px)
px-2      → Horizontal padding (0.5rem = 8px)
text-xs   → Font size (0.75rem = 12px)
font-medium → Font weight 500
[&>svg]:size-4 → Icon size if any (1rem = 16px)
```

### Sidebar Group Container

**Classes (NavMain.vue, Line 21):**
```
px-2      → Horizontal padding (0.5rem = 8px)
py-0      → Vertical padding (0px)
```

---

## Customization Examples

### Option 1: Edit in Component Files (Recommended for global changes)

#### Make Navigation Items Larger

**File:** `resources/js/components/ui/sidebar/index.ts` (Line 39)

Change:
```typescript
// FROM:
gap-2 p-2 text-sm [&>svg]:size-4

// TO (larger):
gap-3 p-3 text-base [&>svg]:size-5
```

Result:
- Gap: 12px (was 8px)
- Padding: 12px (was 8px)
- Font: 16px (was 14px)
- Icon: 20px (was 16px)

#### Make "Platform" Label Larger

**File:** `resources/js/components/ui/sidebar/SidebarGroupLabel.vue` (Line 19)

Change:
```typescript
// FROM:
h-8 px-2 text-xs font-medium

// TO (larger):
h-10 px-3 text-sm font-semibold
```

Result:
- Height: 40px (was 32px)
- Padding: 12px horizontal (was 8px)
- Font: 14px (was 12px)
- Weight: 600 (was 500)

#### Adjust Sidebar Group Spacing

**File:** `resources/js/components/NavMain.vue` (Line 21)

Change:
```vue
<!-- FROM: -->
<SidebarGroup class="px-2 py-0">

<!-- TO (more spacing): -->
<SidebarGroup class="px-4 py-2">
```

Result:
- Horizontal padding: 16px (was 8px)
- Vertical padding: 8px (was 0px)

---

### Option 2: Override in NavMain.vue (Recommended for specific changes)

You can add custom classes directly in NavMain.vue:

**File:** `resources/js/components/NavMain.vue`

```vue
<template>
    <SidebarGroup class="px-4 py-2">
        <!-- Make Platform label bigger -->
        <SidebarGroupLabel class="text-sm font-semibold px-3">
            Platform
        </SidebarGroupLabel>
        
        <SidebarMenu class="space-y-1">
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    size="lg"  <!-- Use lg size for bigger items -->
                    :is-active="urlIsActive(item.href, page.url)"
                    :tooltip="item.title"
                    class="text-base [&>svg]:size-5"  <!-- Override font and icon -->
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
```

---

## Tailwind CSS Size Reference

### Font Sizes
```
text-xs   → 0.75rem (12px)
text-sm   → 0.875rem (14px)
text-base → 1rem (16px)
text-lg   → 1.125rem (18px)
text-xl   → 1.25rem (20px)
```

### Spacing (Padding/Margin/Gap)
```
p-0 / px-0 / py-0 / gap-0  → 0px
p-1 / px-1 / py-1 / gap-1  → 0.25rem (4px)
p-2 / px-2 / py-2 / gap-2  → 0.5rem (8px)
p-3 / px-3 / py-3 / gap-3  → 0.75rem (12px)
p-4 / px-4 / py-4 / gap-4  → 1rem (16px)
p-5 / px-5 / py-5 / gap-5  → 1.25rem (20px)
```

### Icon Sizes
```
size-3  → 0.75rem (12px)
size-4  → 1rem (16px)
size-5  → 1.25rem (20px)
size-6  → 1.5rem (24px)
```

### Heights
```
h-7   → 1.75rem (28px)
h-8   → 2rem (32px)
h-10  → 2.5rem (40px)
h-12  → 3rem (48px)
```

### Font Weights
```
font-normal   → 400
font-medium   → 500
font-semibold → 600
font-bold     → 700
```

---

## Quick Preset Combinations

### Compact Sidebar
```
Menu items: gap-1 p-1.5 text-xs [&>svg]:size-3.5
Platform:   h-7 px-2 text-xs
Group:      px-1 py-0
```

### Default (Current)
```
Menu items: gap-2 p-2 text-sm [&>svg]:size-4
Platform:   h-8 px-2 text-xs
Group:      px-2 py-0
```

### Comfortable
```
Menu items: gap-3 p-3 text-base [&>svg]:size-5
Platform:   h-10 px-3 text-sm font-semibold
Group:      px-4 py-2
```

### Spacious
```
Menu items: gap-4 p-4 text-lg [&>svg]:size-6
Platform:   h-12 px-4 text-base font-bold
Group:      px-5 py-3
```
