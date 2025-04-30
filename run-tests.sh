#!/bin/bash

echo "TK Service Task Management - Test Runner"
echo "----------------------------------------"
echo

if [ -z "$1" ]; then
    echo "Running all tests..."
    php artisan test
    exit 0
fi

case "$1" in
    "task")
        echo "Running Task tests..."
        php artisan test tests/Feature/TaskTest.php
        ;;
    "trash")
        echo "Running Task Trash tests..."
        php artisan test tests/Feature/TaskTrashTest.php
        ;;
    "auth")
        echo "Running Authentication tests..."
        php artisan test tests/Feature/Auth
        ;;
    "unit")
        echo "Running Unit tests..."
        php artisan test tests/Unit
        ;;
    "feature")
        echo "Running Feature tests..."
        php artisan test tests/Feature
        ;;
    "filter")
        if [ -z "$2" ]; then
            echo "Please provide a filter name"
            exit 1
        fi
        echo "Running filtered test: $2"
        php artisan test --filter="$2"
        ;;
    *)
        echo "Unknown parameter: $1"
        echo
        echo "Available options:"
        echo "  task     - Run Task tests"
        echo "  trash    - Run Task Trash tests"
        echo "  auth     - Run Authentication tests"
        echo "  unit     - Run all Unit tests"
        echo "  feature  - Run all Feature tests"
        echo "  filter X - Run tests matching filter X"
        exit 1
        ;;
esac

echo
echo "Test execution completed." 