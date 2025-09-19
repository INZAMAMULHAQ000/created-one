# Sales Quotation PDF Generator - Updated to Match Invoice Design

## Summary of Changes

The sales quotation PDF generator has been completely rewritten to match the design and layout of the Malar invoice PDF (`generate_malar_invoice_fpdf.php`).

### Key Changes Made:

1. **Replaced DomPDF with FPDF**
   - Switched from DomPDF library to FPDF for consistency with invoice system
   - Better control over layout and positioning
   - Consistent styling across all documents

2. **Created MalarQuotationPDF Class**
   - Extended FPDF to create a custom PDF class similar to MalarInvoicePDF
   - Maintains the same structure and methods as the invoice generator
   - Ensures consistent branding and layout

3. **Header Design**
   - Company logo (Sun.jpeg) positioned on the left
   - Company information (MALAR PAPER BAGS) in the center-left
   - QR code (QR.jpg) positioned on the right  
   - Same positioning as invoice: Logo at X=10, Company details at X=55, QR at X=175

4. **Document Structure**
   - Sales Quotation title with light gray background
   - Quotation details table with quotation number, date, contact person, valid until
   - Customer details section with full company information
   - Materials table with S.No, Name/Description, HSN Code, Quantity, Rate, Amount
   - Total section showing the quotation total
   - Amount in words conversion
   - Terms & Conditions section with quotation-specific terms
   - Bank details with G-Pay QR code at the bottom

5. **Styling Consistency**
   - Light gray (220,220,220) backgrounds for headers
   - Medium gray (200,200,200) for table headers
   - Darker gray (180,180,180) for total emphasis
   - Consistent font sizes and spacing throughout

6. **Bottom Layout**
   - Three-column layout at page bottom
   - Bank details on the left
   - G-Pay QR code in the middle with "Scan to Pay via G-Pay" text
   - Note section on the right with GST information

### Database Integration

- Stores quotation data in `sales_quotations` table
- Saves PDF in `quotations/` directory
- Redirects to quotation history page on successful generation
- Handles duplicate quotation number validation

### Files Modified:

- `generate_quotation_pdf.php` - Complete rewrite using FPDF structure
- `sales_quotation.php` - Form remains the same, works with new generator
- `quotation_history.php` - Already supports the new system

### Testing

A test file `test_new_quotation_pdf.php` has been created to validate the functionality.

### How to Test:

1. Access the sales quotation form at `http://localhost/xxx/sales_quotation.php`
2. Fill in customer details and select materials
3. Generate the quotation PDF
4. Verify the PDF matches the invoice design and layout
5. Check that the quotation appears in the history page

The new quotation PDF generator now produces documents that are visually consistent with the invoice system while maintaining quotation-specific content and terms.