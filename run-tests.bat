@echo off
echo TK Service Task Management - Test Runner
echo ----------------------------------------
echo.

if "%1"=="" (
    echo Running all tests...
    php artisan test
    goto end
)

if "%1"=="task" (
    echo Running Task tests...
    php artisan test tests/Feature/TaskTest.php
    goto end
)

if "%1"=="trash" (
    echo Running Task Trash tests...
    php artisan test tests/Feature/TaskTrashTest.php
    goto end
)

if "%1"=="auth" (
    echo Running Authentication tests...
    php artisan test tests/Feature/Auth
    goto end
)

if "%1"=="unit" (
    echo Running Unit tests...
    php artisan test tests/Unit
    goto end
)

if "%1"=="feature" (
    echo Running Feature tests...
    php artisan test tests/Feature
    goto end
)

if "%1"=="filter" (
    if "%2"=="" (
        echo Please provide a filter name
        goto end
    )
    echo Running filtered test: %2
    php artisan test --filter=%2
    goto end
)

echo Unknown parameter: %1
echo.
echo Available options:
echo   task     - Run Task tests
echo   trash    - Run Task Trash tests
echo   auth     - Run Authentication tests
echo   unit     - Run all Unit tests
echo   feature  - Run all Feature tests
echo   filter X - Run tests matching filter X

:end
echo.
echo Test execution completed. 