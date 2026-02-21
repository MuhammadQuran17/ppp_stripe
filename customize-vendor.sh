#!/bin/bash

# PPP Stripe Package - Vendor Name Customization Script
# This script helps you quickly customize the vendor name throughout the package

echo "==================================================="
echo "PPP Stripe - Vendor Name Customization Script"
echo "==================================================="
echo ""

# Check if vendor name is provided
if [ -z "$1" ]; then
    echo "Usage: bash customize-vendor.sh <YourVendorName>"
    echo ""
    echo "Examples:"
    echo "  bash customize-vendor.sh Acme"
    echo "  bash customize-vendor.sh MyCompany"
    echo "  bash customize-vendor.sh YourBrand"
    echo ""
    echo "This will replace:"
    echo "  - YourVendor with your provided name (PascalCase)"
    echo "  - your-vendor with your provided name (kebab-case)"
    echo ""
    exit 1
fi

VENDOR_NAME=$1
VENDOR_NAME_KEBAB=$(echo "$VENDOR_NAME" | sed 's/\B[A-Z]/-\L&/g' | tr '[:upper:]' '[:lower:]')

echo "Customizing package with vendor name: $VENDOR_NAME"
echo "Package name will be: $VENDOR_NAME_KEBAB/ppp-stripe"
echo ""

# Find and replace in composer.json
echo "📝 Updating composer.json..."
sed -i "s/\"your-vendor\/ppp-stripe\"/\"$VENDOR_NAME_KEBAB\/ppp-stripe\"/g" composer.json
sed -i "s/YourVendor/$VENDOR_NAME/g" composer.json

# Find and replace in all PHP files
echo "📝 Updating PHP files..."
find src -name "*.php" -type f -exec sed -i "s/YourVendor/$VENDOR_NAME/g" {} \;

# Update README files
echo "📝 Updating documentation..."
find . -name "*.md" -type f -exec sed -i "s/YourVendor/$VENDOR_NAME/g" {} \;
find . -name "*.md" -type f -exec sed -i "s/your-vendor/$VENDOR_NAME_KEBAB/g" {} \;

echo ""
echo "✅ Customization complete!"
echo ""
echo "Next steps:"
echo "1. Verify the changes: git diff"
echo "2. Run: composer validate"
echo "3. Run: composer dump-autoload"
echo "4. Test in a Laravel app: composer require ../ppp-stripe"
echo ""
echo "Documentation:"
echo "- README.md - Full documentation"
echo "- QUICKSTART.md - Quick start guide"
echo "- USAGE_EXAMPLES.md - Code examples"
echo ""
