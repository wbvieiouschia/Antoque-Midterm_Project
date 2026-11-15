# Normalization — Agriculture Information System

## Unnormalized Form (UNF)

A single table storing all data, with repeating groups:

```
FARMER_RECORD (farmer_id, first_name, last_name, contact_number, address, date_registered,
  farm_name, location, area_hectares, soil_type, irrigation_type,
  crop_name, variety, crop_category, growing_period_days, recommended_season,
  planting_date, area_planted, seed_quantity, expected_harvest_date, status,
  harvest_date, quantity_kg, selling_price_per_kg, quality_grade, remarks)
```

**Problems:** repeating groups (one farmer has many farms; one farm has many plantings; one planting has many harvests), multivalued attributes, redundant data.

## First Normal Form (1NF)

- Remove repeating groups; each cell contains a single atomic value.
- Split into separate tables: FARMER, FARM, CROP, PLANTING, HARVEST.

```
FARMER (farmer_id, first_name, last_name, contact_number, address, date_registered)
FARM (farm_id, farmer_id, farm_name, location, area_hectares, soil_type, irrigation_type)
CROP (crop_id, crop_name, variety, crop_category, growing_period_days, recommended_season)
PLANTING (planting_id, farm_id, crop_id, planting_date, area_planted, seed_quantity, expected_harvest_date, status)
HARVEST (harvest_id, planting_id, harvest_date, quantity_kg, selling_price_per_kg, quality_grade, remarks)
```

## Second Normal Form (2NF)

- All non-key attributes fully depend on the primary key (no partial dependencies).
- Each table has a single-column PK (IDENTITY), so partial dependencies cannot occur. No change from 1NF.

## Third Normal Form (3NF)

- Remove transitive dependencies (non-key attributes depending on other non-key attributes).
- `crop_name`, `variety`, etc. depend on `crop_id`, not on `farm_id` or `planting_id`. All non-key attributes depend only on the PK.
- Controlled text values (soil type, irrigation type, crop category, season, planting status, quality grade, harvest remarks) are moved to lookup tables so that only their IDs are stored in the main tables.

```
SOIL_TYPE (soil_type_id, soil_type_name)
IRRIGATION_TYPE (irrigation_type_id, irrigation_type_name)
CROP_CATEGORY (crop_category_id, category_name)
SEASON (season_id, season_name)
PLANTING_STATUS (planting_status_id, status_name)
QUALITY_GRADE (quality_grade_id, grade_name)
HARVEST_REMARKS (remark_id, remark_description)
```

- `FARM` stores `soil_type_id` and `irrigation_type_id`.
- `CROP` stores `crop_category_id` and `season_id`.
- `PLANTING` stores `planting_status_id`.
- `HARVEST` stores `quality_grade_id` and `remark_id`.

## Authentication table

User accounts are stored separately from the agricultural data:

```
APP_USER (user_id, full_name, email, password_hash, role, created_at)
```

- PK `user_id` is a single column, so there are no partial dependencies (2NF).
- Every attribute depends only on `user_id`; `email` is also unique (a candidate key). There are no transitive dependencies (3NF).
- It does not reference any farm table.

## Final 3NF Tables

- FARMER, FARM, CROP, PLANTING, HARVEST
- SOIL_TYPE, IRRIGATION_TYPE, CROP_CATEGORY, SEASON, PLANTING_STATUS, QUALITY_GRADE, HARVEST_REMARKS (lookup)
- APP_USER (authentication)

## Relationships

- FARMER 1:M FARM
- FARM 1:M PLANTING
- CROP 1:M PLANTING
- PLANTING 1:M HARVEST
- SOIL_TYPE 1:M FARM
- IRRIGATION_TYPE 1:M FARM
- CROP_CATEGORY 1:M CROP
- SEASON 1:M CROP
- PLANTING_STATUS 1:M PLANTING
- QUALITY_GRADE 1:M HARVEST
- HARVEST_REMARKS 1:M HARVEST (lookup, optional)
- APP_USER has no relationships (authentication only)