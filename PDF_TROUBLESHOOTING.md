# PDF Generation Troubleshooting Guide

## Issue
When clicking "Generate PDF" button, you get "Failed to load PDF document" error.

## What I've Done to Fix It

### 1. **Created Debug Tools**
- **[debug_quotation_pdf.php](debug_quotation_pdf.php)** - Comprehensive debugging tool
- **[simple_quotation_pdf.php](simple_quotation_pdf.php)** - Simple PDF test
- Added debug buttons to the quotation form

### 2. **Simplified PDF Generation**
- Removed complex CSS positioning that might cause issues
- Changed from 'DejaVu Sans' to 'Arial' font (more reliable)
- Disabled PHP execution in HTML (security best practice)
- Simplified HTML structure to avoid DomPDF parsing issues
- Removed absolute positioning and complex layouts

### 3. **Fixed Potential Issues**
- Simplified DomPDF options to avoid configuration conflicts
- Removed potentially problematic font subsetting
- Streamlined CSS to basic styling only
- Removed complex table layouts that might cause rendering issues

## How to Test and Fix

### Step 1: Run Debug Test
1. Go to `http://localhost/xxx/debug_quotation_pdf.php`
2. This will show you exactly what's going wrong
3. Look for error messages or failed steps

### Step 2: Test Simple PDF
1. Go to `http://localhost/xxx/simple_quotation_pdf.php`
2. If this works, the issue is with complex HTML
3. If this fails, the issue is with DomPDF setup

### Step 3: Check PHP Extensions
The debug page will show if GD extension is missing:
- If GD is missing, edit `php.ini`
- Uncomment `extension=gd`
- Restart Apache

### Step 4: Test Updated Quotation
1. Go to `http://localhost/xxx/sales_quotation.php`
2. Fill the form and click "Generate Quotation"
3. The PDF should now work with simplified structure

## Common Causes and Solutions

### 1. **Missing GD Extension**
**Error**: Images can't be processed
**Solution**: Enable GD in php.ini

### 2. **Complex CSS/HTML**
**Error**: DomPDF can't parse complex layouts
**Solution**: Use simplified HTML (already done)

### 3. **Memory Issues**
**Error**: Script times out or runs out of memory
**Solution**: Increase memory_limit in php.ini

### 4. **Font Issues**
**Error**: Font not found or can't be loaded
**Solution**: Use basic fonts like Arial (already done)

### 5. **File Permissions**
**Error**: Can't save PDF file
**Solution**: Check directory permissions

## Files Modified

1. **generate_quotation_pdf.php** - Simplified and made more reliable
2. **sales_quotation.php** - Added debug buttons
3. **debug_quotation_pdf.php** - New debugging tool
4. **simple_quotation_pdf.php** - Simple test tool

## Next Steps

1. **Test the debug tools first** to identify the exact issue
2. **If debug shows success**, the main file should now work
3. **If still failing**, check the error messages in debug output
4. **Report specific error messages** for further troubleshooting

The simplified PDF generation should now work reliably without the complex styling that was causing issues.