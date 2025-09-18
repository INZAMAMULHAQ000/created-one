# Supermarket Billing System

A modern billing system with GST calculation and PDF invoice generation.

## Features

- Modern neon UI design
- Admin login system
- Material management
- GST calculation (SGST + CGST / IGST)
- PDF invoice generation
- Professional invoice layout

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- FPDF library
- Web server (Apache/Nginx)

## Installation

1. Clone this repository to your web server directory
2. Import the database schema:
   ```sql
   mysql -u root -p < database.sql
   ```
3. Download FPDF library:
   ```bash
   mkdir fpdf
   cd fpdf
   wget http://www.fpdf.org/en/download/fpdf184.tgz
   tar -xvzf fpdf184.tgz
   ```
4. Update database configuration in `config/database.php` if needed
5. Access the application through your web browser

## Default Login Credentials

- Username: admin
- Password: admin123

## Usage

1. Login using the default credentials
2. Add materials through the "Manage Materials" page
3. Generate bills using the materials from the dropdown
4. Print professional PDF invoices

## License

MIT License 