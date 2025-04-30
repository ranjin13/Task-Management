#!/bin/bash

# Show current environment for debugging
echo "Current environment: $(php artisan env)"
echo "Current storage path: $(php artisan storage:path)"

# Create the symbolic link from public/storage to storage/app/public
php artisan storage:link --force

echo "Storage link created successfully."

# List the symbolic links for verification
if [ -L "public/storage" ]; then
  echo "Symbolic link exists:"
  ls -la public/storage
else
  echo "WARNING: Symbolic link does not exist!"
  ls -la public/
fi

# Clear caches to ensure config is refreshed
php artisan config:clear
echo "Configuration cache cleared."

 