#!/bin/bash

LOCKFILE="/tmp/laravel-queue-worker.lock"

# Check if the lock file exists
if [ -f "$LOCKFILE" ]; then
    echo "Queue worker already running. Exiting..."
    exit 1
fi

# Create a lock file
touch "$LOCKFILE"

# Run the queue worker
/usr/bin/php /home/u521432036/domains/theqa-team.com/public_html/th-back/artisan queue:work --stop-when-empty >> /dev/null 2>&1

# Remove the lock file after the worker finishes
rm -f "$LOCKFILE"
