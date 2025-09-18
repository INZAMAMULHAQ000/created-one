# Daily Expenses - Automatic Purchase Order Total Calculation

## Overview
The Daily Expenses page now automatically calculates and displays the total amount of all Purchase Orders for a selected date.

## How It Works

### 1. **Date Selection**
- When you select a date in the "Date" field, the system automatically:
  - Searches the Purchase Order database for all POs created on that date
  - Calculates the total amount of all POs found
  - Displays the total in the "Purchase Order Total" field

### 2. **Real-time Calculation**
- The calculation happens instantly when you change the date
- Shows a loading spinner while calculating
- Displays detailed breakdown of found POs

### 3. **Example Usage**
If you select date "2025-09-18" and there are:
- PO-1: ₹500
- PO-2: ₹450  
- PO-3: ₹310

The system will automatically show ₹1,260 in the Purchase Order Total field.

## Features

### 🔄 **Automatic Calculation**
- No manual input needed
- Updates instantly when date changes
- Shows real-time loading states

### 📊 **Detailed Breakdown**
- Displays number of POs found
- Shows individual PO numbers and amounts
- Total amount calculation

### ✅ **User-Friendly Interface**
- Read-only field (prevents accidental changes)
- Clear visual feedback
- Error handling for invalid dates

### 🔍 **Smart Search**
- Searches by exact date match
- Fast database queries
- Handles empty results gracefully

## Technical Details

### API Endpoint
- **File**: `get_po_total_by_date.php`
- **Method**: GET
- **Parameter**: `date` (YYYY-MM-DD format)
- **Response**: JSON with total amount and PO details

### Database Integration
- Queries `purchase_orders` table
- Uses `po_date` and `total_amount` fields
- Handles date validation and error cases

### Frontend Features
- jQuery AJAX calls
- Real-time form updates
- Bootstrap styling integration
- Font Awesome icons for loading states

## Benefits

1. **Accuracy**: Eliminates manual calculation errors
2. **Efficiency**: Saves time in data entry
3. **Transparency**: Shows detailed breakdown of calculations
4. **Automation**: Works without user intervention

## Usage Instructions

1. Navigate to Daily Expenses page
2. Select any date using the date picker
3. Watch as the Purchase Order Total field automatically populates
4. Review the breakdown details shown below the field
5. Continue with other expense entries as normal

The feature is now fully integrated and ready to use!