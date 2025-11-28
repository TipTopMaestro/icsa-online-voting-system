# 🐛 Bug Fix: Column 'position_id' Not Found

## Issue
When testing the Elections module, encountered error:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'candidates.position_id' in 'on clause'
```

## Root Cause
The `candidates` table had a `position` column (string) but the Election model was trying to use `position_id` (foreign key) for the `hasManyThrough` relationship.

## Solution Applied ✅

### 1. Created Migration to Add `position_id`
**File**: `database/migrations/2025_11_24_074031_add_position_id_to_candidates_table.php`

```php
public function up(): void
{
    Schema::table('candidates', function (Blueprint $table) {
        $table->foreignId('position_id')->nullable()->after('election_id')->constrained('positions')->onDelete('cascade');
    });
}
```

### 2. Ran the Migration
```bash
php artisan migrate
```

### 3. Updated Models

#### Candidate Model
Added `position_id` to fillable and added `position()` relationship:
```php
protected $fillable = [
    'user_id',
    'election_id',
    'position_id',  // ← Added
    'position',     // ← Kept for backward compatibility
    'partylist',
    'platform',
    'votes_count',
    'photo',
];

public function position()
{
    return $this->belongsTo(Position::class);
}
```

#### Election Model
Fixed `hasManyThrough` relationship with explicit foreign keys:
```php
public function candidates()
{
    return $this->hasManyThrough(
        Candidate::class, 
        Position::class, 
        'election_id',   // Foreign key on positions table
        'position_id',   // Foreign key on candidates table
        'id',           // Local key on elections table
        'id'            // Local key on positions table
    );
}
```

#### Position Model
Explicitly defined foreign keys:
```php
public function candidates()
{
    return $this->hasMany(Candidate::class, 'position_id');
}

public function votes()
{
    return $this->hasMany(Vote::class, 'position_id');
}
```

### 4. Cleared Cache
```bash
php artisan optimize:clear
```

## Testing ✅
Confirmed the fix works:
```
Testing Elections query...
✅ Success! Found 0 elections
```

## Database Schema Update

The `candidates` table now has:
```
candidates
├── id
├── user_id (FK → users)
├── election_id (FK → elections)
├── position_id (FK → positions) ← NEW
├── position (string) ← Kept for backward compatibility
├── partylist
├── platform
├── photo
├── votes_count
└── timestamps
```

## Important Notes

1. **Both fields exist**: `position` (string) and `position_id` (FK)
   - `position_id` should be used going forward
   - `position` kept for backward compatibility

2. **When adding candidates**: Use `position_id` not `position`
   ```php
   Candidate::create([
       'user_id' => $userId,
       'election_id' => $electionId,
       'position_id' => $positionId,  // ← Use this
       'partylist' => 'Party Name',
       'platform' => 'Platform text',
   ]);
   ```

3. **Candidates Controller**: Will need to validate `position_id` when implemented

## Status
✅ **FIXED** - Elections module now works correctly!

## Next Steps for Candidates Module
When implementing the Candidates module:
1. Use `position_id` in forms (dropdown of positions)
2. Validate `position_id` exists in positions table
3. Ensure position belongs to the selected election
4. Optional: Consider removing the old `position` string column in future

---

**Fixed**: 2025-11-24  
**Migration**: `2025_11_24_074031_add_position_id_to_candidates_table.php`
