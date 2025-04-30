#!/bin/bash

# Create the symbolic link from public/storage to storage/app/public
php artisan storage:link --force

echo "Storage link created successfully."

 