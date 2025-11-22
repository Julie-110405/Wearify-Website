# Wearify Backend Setup - Home Page Functionality

## Overview
This document describes the backend functionality added for the home page of the Wearify application.

## Database Setup

### New Tables Required
Run the SQL in `database_schema.sql` in phpMyAdmin to create the following tables:

1. **outfits** - Stores saved outfits
   - `outfit_id` (Primary Key, Auto Increment)
   - `user_id` (Foreign Key to users table)
   - `created_at` (DateTime)

2. **outfit_items** - Junction table linking outfits to items
   - `outfit_item_id` (Primary Key, Auto Increment)
   - `outfit_id` (Foreign Key to outfits table)
   - `item_id` (Foreign Key to items table)
   - Unique constraint on (`outfit_id`, `item_id`)

### Important Note
Make sure your `items` table has a column named `image_url` (not `image_path`). If your database uses `image_path`, you'll need to either:
- Rename the column to `image_url` in phpMyAdmin, OR
- Update all references from `image_url` to `image_path` in the PHP files

## New API Endpoints

### 1. `/api/v1/outfits.php`

#### POST - Save Outfit
**Request:**
```json
{
  "action": "save",
  "items": [1, 2, 3, 4]
}
```
**Response:**
```json
{
  "success": true,
  "message": "Outfit saved successfully!",
  "outfit_id": 5
}
```

#### GET - List Outfits
**Request:** `GET /api/v1/outfits.php?action=list`
**Response:**
```json
{
  "success": true,
  "data": [
    {
      "outfit_id": 1,
      "created_at": "2024-01-01 12:00:00",
      "items": [
        {
          "item_id": 1,
          "category": "upper",
          "image_url": "public/uploads/..."
        }
      ]
    }
  ]
}
```

#### GET - Randomize Outfit
**Request:** `GET /api/v1/outfits.php?action=randomize`
**Response:**
```json
{
  "success": true,
  "data": [
    {
      "item_id": 1,
      "category": "upper",
      "image_url": "public/uploads/..."
    }
  ],
  "message": "Random outfit generated successfully!"
}
```

#### DELETE - Delete Outfit
**Request:** `DELETE /api/v1/outfits.php`
**Body:** `outfit_id=1`
**Response:**
```json
{
  "success": true,
  "message": "Outfit deleted successfully."
}
```

### 2. Updated `/api/v1/closet.php`

#### GET - Get Items by Category
**Request:** `GET /api/v1/closet.php?category=upper`
**Response:**
```json
{
  "success": true,
  "data": [
    {
      "item_id": 1,
      "category": "upper",
      "image_url": "public/uploads/...",
      "created_at": "2024-01-01 12:00:00"
    }
  ]
}
```

## Frontend Integration

### New Functions in `home.js`

1. **`fetchItemsByCategory(category)`** - Fetches items for a specific category
2. **`displayItems(category, items)`** - Displays items in the slide container
3. **`selectItem(category, item)`** - Selects an item for the current outfit
4. **`updateItemDisplay(category, item)`** - Updates the avatar display with selected item
5. **`removeItem(category)`** - Removes an item from the current outfit
6. **`saveOutfit()`** - Saves the current outfit to the database
7. **`randomizeOutfit()`** - Generates a random outfit from user's items

### New UI Elements

- **Save Outfit Button** - Located at top of page, saves current outfit
- **Randomize Button** - Located at top of page, generates random outfit
- **Item Cards** - Display items in each category slide with selection highlighting
- **Remove Icons** - Show/hide when items are selected/deselected

## How It Works

1. **Loading Items**: On page load, items are fetched for all categories
2. **Selecting Items**: Click on an item card to select it for the outfit
3. **Displaying Outfit**: Selected items appear on the avatar figure
4. **Saving Outfits**: Click "Save Outfit" to save the current selection
5. **Randomizing**: Click "Randomize" to generate a random outfit from available items

## Testing

1. Make sure XAMPP Apache and MySQL are running
2. Run the SQL schema in phpMyAdmin
3. Ensure you have some items in the `items` table (add via the edit page)
4. Open `home.html` in your browser
5. Test:
   - Selecting items from different categories
   - Saving outfits
   - Randomizing outfits
   - Removing items

## Notes

- Currently uses hardcoded `user_id = 1` for testing
- In production, implement proper session management
- The accessory category maps to `accessory1` slot (accessory2 can be added later)
- All API endpoints return JSON responses
- Error handling is included for all operations






