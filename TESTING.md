# Testing Documentation for TK Service Task Management Application

This document outlines the testing strategy and test cases for the Task Management Application. The application uses PHPUnit for testing, with a combination of unit tests, feature tests, and integration tests.

## Testing Environment Setup

To run the tests, make sure you have the following requirements:

1. PHP 8.2 or higher with required extensions (including GD extension for image processing)
2. Composer installed
3. Database configuration set up in `.env.testing`

To run all tests:

```bash
php artisan test
```

To run a specific test file:

```bash
php artisan test tests/Feature/TaskTest.php
```

To run a specific test:

```bash
php artisan test --filter=test_task_can_be_created
```

## Test Suite Overview

The test suite is organized into the following categories:

### Unit Tests

Unit tests focus on testing individual components in isolation:

- `TaskModelTest`: Tests the Task model and its methods
- `TaskServiceTest`: Tests the TaskService class and its methods
- `CleanupTrashedTasksTest`: Tests the command for cleaning up trashed tasks

### Feature Tests

Feature tests focus on testing application features from controller to response:

- `TaskTest`: Tests all task-related functionality including CRUD operations, status updates, and subtasks
- `TaskTrashTest`: Tests soft deletion, restoration, and permanent deletion of tasks
- `AuthenticationTest`: Tests user authentication features
- `RegistrationTest`: Tests user registration functionality
- `DashboardTest`: Tests dashboard access control

## Task Feature Tests

### Task CRUD Operations

- `test_task_index_page_can_be_rendered`: Tests if the task index page loads correctly
- `test_task_create_page_can_be_rendered`: Tests if the task creation page loads correctly
- `test_task_show_page_can_be_rendered`: Tests if a single task can be viewed correctly
- `test_task_edit_page_can_be_rendered`: Tests if the task edit page loads correctly
- `test_task_can_be_created`: Tests task creation with all fields including image uploads
- `test_task_can_be_updated`: Tests task update functionality with all fields
- `test_task_status_can_be_updated`: Tests if task status can be changed
- `test_task_publication_status_can_be_toggled`: Tests task publish/unpublish functionality
- `test_user_cannot_access_others_tasks`: Tests authorization rules preventing access to others' tasks

### Subtask Operations

- `test_subtask_can_be_added_to_task`: Tests adding subtasks to a task
- `test_subtask_status_can_be_toggled`: Tests toggling subtask completion status
- `test_subtask_can_be_deleted`: Tests deleting subtasks from a task

### Task Filtering and Sorting

- `test_tasks_can_be_filtered`: Tests filtering tasks by status and search terms
- `test_tasks_can_be_sorted`: Tests sorting tasks by creation date and direction

### Task Trash Management

- `test_task_can_be_moved_to_trash`: Tests soft deletion of tasks
- `test_task_can_be_restored_from_trash`: Tests restoration of trashed tasks
- `test_task_can_be_permanently_deleted`: Tests permanent deletion of trashed tasks
- `test_user_can_view_trashed_tasks`: Tests access to the trash page
- `test_user_cannot_access_others_trashed_tasks`: Tests authorization rules for trashed tasks

## Authentication Tests

- `test_login_screen_can_be_rendered`: Tests if login page loads correctly
- `test_users_can_authenticate`: Tests user login with valid credentials
- `test_users_cannot_authenticate_with_invalid_password`: Tests login validation
- `test_users_can_logout`: Tests user logout functionality

## Test Data

Tests use factory classes to generate test data:

- `UserFactory`: Creates test users
- `TaskFactory`: Creates test tasks with various states

## Testing Best Practices

1. **Isolation**: Each test runs in isolation with a fresh database state
2. **Comprehensive**: Tests cover happy paths and edge cases
3. **Assertive**: Each test makes meaningful assertions about the system state
4. **Authorization**: Tests ensure proper authorization for all actions
5. **Validation**: Tests ensure proper validation for all inputs
6. **File Handling**: Tests cover file uploads and storage

## Common Testing Patterns

### Testing Authorization

```php
// Create another user's task
$anotherUser = User::factory()->create();
$task = Task::factory()->create([
    'user_id' => $anotherUser->id,
]);

// Try to access as the logged-in user
$response = $this->get(route('tasks.show', $task));
$response->assertStatus(403); // Should be forbidden
```

### Testing Validation

```php
// Missing required fields
$response = $this->post(route('tasks.store'), [
    'title' => 'Test Task',
    // Missing description and status
]);

$response->assertSessionHasErrors(['description', 'status']);
```

### Testing Redirection

```php
// After successful creation
$response = $this->post(route('tasks.store'), $validData);
$response->assertRedirect(route('tasks.index'));
$response->assertSessionHas('success');
```

## Troubleshooting Common Test Issues

1. **Image Processing Tests Failing**:
   - Ensure GD extension is installed and enabled in PHP
   - For Windows: Uncomment the line `;extension=gd` in php.ini

2. **Database-related Test Failures**:
   - Ensure your .env.testing has correct database credentials
   - Make sure the test database exists
   - Run `php artisan config:clear` before tests

3. **Authentication Test Failures**:
   - Ensure you're calling `$this->actingAs($user)` in tests that require authentication
   - Check that your auth middleware is correctly applied to routes 