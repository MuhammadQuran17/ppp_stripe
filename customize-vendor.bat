@echo off
REM PPP Stripe Package - Vendor Name Customization Script (Windows)
REM This script helps you quickly customize the vendor name throughout the package

echo ===================================================
echo PPP Stripe - Vendor Name Customization Script
echo ===================================================
echo.

if "%1"=="" (
    echo Usage: customize-vendor.bat YourVendorName
    echo.
    echo Examples:
    echo   customize-vendor.bat Acme
    echo   customize-vendor.bat MyCompany
    echo   customize-vendor.bat YourBrand
    echo.
    echo This will replace:
    echo   - YourVendor with your provided name (PascalCase)
    echo   - your-vendor with your provided name (kebab-case)
    echo.
    pause
    exit /b 1
)

setlocal enabledelayedexpansion
set VENDOR_NAME=%1
set VENDOR_NAME_KEBAB=%VENDOR_NAME%

REM Convert to lowercase and kebab-case
for /f "tokens=*" %%a in ('powershell -Command "$('%VENDOR_NAME%' -creplace '([A-Z])', '-$1').toLower().TrimStart('-')"') do set VENDOR_NAME_KEBAB=%%a

echo Customizing package with vendor name: %VENDOR_NAME%
echo Package name will be: %VENDOR_NAME_KEBAB%/ppp-stripe
echo.

REM Update composer.json using PowerShell
echo 📝 Updating composer.json...
powershell -Command "(Get-Content composer.json) -replace 'your-vendor', '%VENDOR_NAME_KEBAB%' -replace 'YourVendor', '%VENDOR_NAME%' | Set-Content composer.json"

REM Update all PHP files
echo 📝 Updating PHP files...
for /r src %%f in (*.php) do (
    powershell -Command "(Get-Content '%%f') -replace 'YourVendor', '%VENDOR_NAME%' | Set-Content '%%f'"
)

REM Update documentation files
echo 📝 Updating documentation...
for /r . %%f in (*.md) do (
    powershell -Command "(Get-Content '%%f') -replace 'YourVendor', '%VENDOR_NAME%' -replace 'your-vendor', '%VENDOR_NAME_KEBAB%' | Set-Content '%%f'"
)

echo.
echo ✅ Customization complete!
echo.
echo Next steps:
echo 1. Verify the changes: git diff
echo 2. Run: composer validate
echo 3. Run: composer dump-autoload
echo 4. Test in a Laravel app: composer require ../ppp-stripe
echo.
echo Documentation:
echo - README.md - Full documentation
echo - QUICKSTART.md - Quick start guide
echo - USAGE_EXAMPLES.md - Code examples
echo.
pause
