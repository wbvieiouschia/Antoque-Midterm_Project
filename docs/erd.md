# Entity-Relationship Diagram (ERD)

## Tables and Relationships

### FARMER
- **farmer_id** (PK, IDENTITY)
- first_name, last_name, contact_number, address, date_registered, created_at, updated_at, created_by, updated_by

### FARM
- **farm_id** (PK, IDENTITY)
- **farmer_id** (FK → FARMER.farmer_id)
- **soil_type_id** (FK → SOIL_TYPE.soil_type_id)
- **irrigation_type_id** (FK → IRRIGATION_TYPE.irrigation_type_id)
- farm_name, location, area_hectares, created_at, updated_at, created_by, updated_by

### CROP
- **crop_id** (PK, IDENTITY)
- **crop_category_id** (FK → CROP_CATEGORY.crop_category_id)
- **season_id** (FK → SEASON.season_id)
- crop_name, variety, growing_period_days, created_at, updated_at, created_by, updated_by

### PLANTING
- **planting_id** (PK, IDENTITY)
- **farm_id** (FK → FARM.farm_id)
- **crop_id** (FK → CROP.crop_id)
- **planting_status_id** (FK → PLANTING_STATUS.planting_status_id)
- planting_date, area_planted, seed_quantity, expected_harvest_date, created_at, updated_at, created_by, updated_by

### HARVEST
- **harvest_id** (PK, IDENTITY)
- **planting_id** (FK → PLANTING.planting_id)
- **quality_grade_id** (FK → QUALITY_GRADE.quality_grade_id)
- harvest_date, quantity_kg, selling_price_per_kg, remark_id (FK → HARVEST_REMARKS.remark_id), remarks, created_at, updated_at, created_by, updated_by

### HARVEST_REMARKS (lookup)
- **remark_id** (PK)
- remark_description

### SOIL_TYPE (lookup)
- **soil_type_id** (PK, IDENTITY)
- soil_type_name (UNIQUE)

### IRRIGATION_TYPE (lookup)
- **irrigation_type_id** (PK, IDENTITY)
- irrigation_type_name (UNIQUE)

### CROP_CATEGORY (lookup)
- **crop_category_id** (PK, IDENTITY)
- category_name (UNIQUE)

### SEASON (lookup)
- **season_id** (PK, IDENTITY)
- season_name (UNIQUE)

### PLANTING_STATUS (lookup)
- **planting_status_id** (PK, IDENTITY)
- status_name (UNIQUE)

### QUALITY_GRADE (lookup)
- **quality_grade_id** (PK, IDENTITY)
- grade_name (UNIQUE)

### APP_USER (authentication)
- **user_id** (PK, IDENTITY)
- full_name, email (UNIQUE), password_hash, role ('administrator' or 'staff'), created_at
- Stores accounts created from the Create Account page. It has no foreign keys and is not linked to the farm tables.

## Relationships Summary

```
FARMER (1) ──< FARM (M)
FARM   (1) ──< PLANTING (M)
CROP   (1) ──< PLANTING (M)
PLANTING (1) ──< HARVEST (M)
HARVEST_REMARKS (1) ──< HARVEST (M) [optional]
```

## Notes

- All primary keys are integer IDENTITY columns (auto-increment).
- Foreign keys enforce referential integrity.
- `created_at` and `updated_at` audit fields are included on FARMER, FARM, PLANTING, and HARVEST.
- Planting `status` values are: Planned, Planted, Growing, Harvested, Failed (defined in `includes/config.php`; the column has no CHECK constraint).
- Passwords are never stored in plain text; `password_hash` holds a PHP `password_hash()` value.
- RLS is enabled on all tables; permissive policies are provided for the school demo. Tighten for production.