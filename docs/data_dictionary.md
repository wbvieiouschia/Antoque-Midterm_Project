# Data Dictionary — Agriculture Information System

## FARMER
| Field | Type | Key | Description |
|---|---|---|---|
| farmer_id | integer | PK | Unique farmer ID |
| first_name | varchar(50) | | Farmer's first name |
| last_name | varchar(50) | | Farmer's last name |
| contact_number | varchar(20) | | Contact number |
| address | varchar(150) | | Address |
| date_registered | date | | Registration date |
| created_at | timestamp | | Record creation timestamp |
| updated_at | timestamp | | Last update timestamp |
| created_by | varchar(100) | | User who created the record |
| updated_by | varchar(100) | | User who last updated the record |

## FARM
| Field | Type | Key | Description |
|---|---|---|---|
| farm_id | integer | PK | Unique farm ID |
| farmer_id | integer | FK → FARMER.farmer_id | Owner farmer |
| farm_name | varchar(100) | | Farm name |
| location | varchar(150) | | Farm location |
| area_hectares | numeric(8,2) | | Area in hectares (>0) |
| soil_type_id | integer | FK → SOIL_TYPE.soil_type_id | Soil type |
| irrigation_type_id | integer | FK → IRRIGATION_TYPE.irrigation_type_id | Irrigation type |
| created_at | timestamp | | Record creation timestamp |
| updated_at | timestamp | | Last update timestamp |
| created_by | varchar(100) | | User who created the record |
| updated_by | varchar(100) | | User who last updated the record |

## CROP
| Field | Type | Key | Description |
|---|---|---|---|
| crop_id | integer | PK | Unique crop ID |
| crop_name | varchar(80) | | Crop name |
| variety | varchar(80) | | Crop variety |
| crop_category_id | integer | FK → CROP_CATEGORY.crop_category_id | Crop category |
| growing_period_days | integer | | Growing period in days (>0) |
| season_id | integer | FK → SEASON.season_id | Recommended season |
| created_at | timestamp | | Record creation timestamp |
| updated_at | timestamp | | Last update timestamp |
| created_by | varchar(100) | | User who created the record |
| updated_by | varchar(100) | | User who last updated the record |

## PLANTING
| Field | Type | Key | Description |
|---|---|---|---|
| planting_id | integer | PK | Unique planting ID |
| farm_id | integer | FK → FARM.farm_id | Farm where planted |
| crop_id | integer | FK → CROP.crop_id | Crop planted |
| planting_date | date | | Planting date |
| area_planted | numeric(8,2) | | Area planted in hectares (>0) |
| seed_quantity | numeric(8,2) | | Seed quantity in kg (>=0) |
| expected_harvest_date | date | | Expected harvest date |
| planting_status_id | integer | FK → PLANTING_STATUS.planting_status_id | Planting status |
| created_at | timestamp | | Record creation timestamp |
| updated_at | timestamp | | Last update timestamp |
| created_by | varchar(100) | | User who created the record |
| updated_by | varchar(100) | | User who last updated the record |

## HARVEST
| Field | Type | Key | Description |
|---|---|---|---|
| harvest_id | integer | PK | Unique harvest ID |
| planting_id | integer | FK → PLANTING.planting_id | Source planting |
| harvest_date | date | | Harvest date |
| quantity_kg | numeric(10,2) | | Harvested quantity in kg (>0) |
| selling_price_per_kg | numeric(10,2) | | Selling price per kg (>=0) |
| quality_grade_id | integer | FK → QUALITY_GRADE.quality_grade_id | Quality grade |
| remark_id | integer | FK → HARVEST_REMARKS.remark_id (optional) | Harvest remark |
| remarks | varchar(200) | | Additional remarks |
| created_at | timestamp | | Record creation timestamp |
| updated_at | timestamp | | Last update timestamp |
| created_by | varchar(100) | | User who created the record |
| updated_by | varchar(100) | | User who last updated the record |

## Lookup tables

### SOIL_TYPE
| Field | Type | Key | Description |
|---|---|---|---|
| soil_type_id | integer | PK | Unique soil type ID |
| soil_type_name | varchar(50) | UNIQUE | Soil type name |

### IRRIGATION_TYPE
| Field | Type | Key | Description |
|---|---|---|---|
| irrigation_type_id | integer | PK | Unique irrigation type ID |
| irrigation_type_name | varchar(50) | UNIQUE | Irrigation type name |

### CROP_CATEGORY
| Field | Type | Key | Description |
|---|---|---|---|
| crop_category_id | integer | PK | Unique crop category ID |
| category_name | varchar(50) | UNIQUE | Crop category name |

### SEASON
| Field | Type | Key | Description |
|---|---|---|---|
| season_id | integer | PK | Unique season ID |
| season_name | varchar(30) | UNIQUE | Season name |

### PLANTING_STATUS
| Field | Type | Key | Description |
|---|---|---|---|
| planting_status_id | integer | PK | Unique planting status ID |
| status_name | varchar(30) | UNIQUE | Planting status name |

### QUALITY_GRADE
| Field | Type | Key | Description |
|---|---|---|---|
| quality_grade_id | integer | PK | Unique quality grade ID |
| grade_name | varchar(30) | UNIQUE | Quality grade name |

### HARVEST_REMARKS
| Field | Type | Key | Description |
|---|---|---|---|
| remark_id | integer | PK | Unique remark ID |
| remark_description | varchar(100) | | Remark description |

## APP_USER
| Field | Type | Key | Description |
|---|---|---|---|
| user_id | integer | PK | Unique user ID |
| full_name | varchar(100) | | Full name |
| email | varchar(150) | UNIQUE | Email address |
| password_hash | varchar(255) | | Hashed password |
| role | varchar(30) | | Role (administrator/staff) |
| created_at | timestamp | | Account creation timestamp |