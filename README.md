# TK Service Task Management Application

A modern and responsive task management application built with Laravel 12 and Vue.js. This application allows users to create, manage, and organize their tasks with a user-friendly interface and robust backend.

## Features

### Task Management
- Create, edit, update, and delete tasks
- Mark tasks as completed or in-progress
- Filter tasks by status (to-do, in-progress, done)
- Search tasks by title
- Publish/unpublish tasks to control visibility
- Subtasks with progress tracking and auto-completion
- Task validation with custom rules
- Image attachment support for tasks
- Trash bin for deleted tasks with restoration and permanent deletion options
- Automatic cleanup of trashed tasks after 30 days

### User Interface
- Responsive design that works on desktop and mobile devices
- Dark mode support
- Modern and intuitive UI with a sidebar for navigation
- Pagination for large task lists
- Sort tasks by creation date or title
- Adjustable number of tasks per page (10, 20, 50, 100 options)
- Dedicated trash page to manage deleted tasks

### Technical Implementation

#### Backend
- **Laravel 12 Framework**: Robust PHP framework for the backend
- **Service Layer Architecture**: Separation of concerns with dedicated service classes
- **Form Request Validation**: Advanced validation rules for task creation and updates
- **Inertia-based Communication**: Server-side controllers that communicate directly with the frontend via Inertia.js instead of traditional API endpoints
- **Image Storage**: Secure image upload and storage for task attachments
- **Laravel Debugbar**: Enhanced debugging tools for development
- **Authentication System**: Secure user authentication and authorization
- **Soft Deletes**: Laravel's SoftDeletes trait for trash management
- **Scheduled Commands**: Automatic cleanup via Laravel's task scheduler

#### Frontend
- **Vue.js 3**: Progressive JavaScript framework for building the UI
- **TypeScript**: Type-safe code to reduce runtime errors
- **Inertia.js**: Modern stack that allows server-side rendering without building an API
- **Tailwind CSS**: Utility-first CSS framework for responsive design
- **Lucide Icons**: Beautiful SVG icons for enhanced UI

#### Database
- **PostgreSQL**: Powerful, open-source object-relational database
- **Migrations**: Version-controlled database schema changes
- **Seeders**: Sample data for testing and development
- **Soft Delete Columns**: Database structure optimized for trash functionality

## Task Features
- **Title**: Required, unique, maximum 100 characters
- **Description**: Required field for task details
- **Status**: Must be one of: to-do, in-progress, done
- **Image Attachments**: Optional, accepts standard image formats, max 4MB
- **Publication Status**: Toggle for controlling task visibility
- **Subtasks**: JSON-based subtasks stored in the same table
  - Progress tracking with completion percentage
  - Automatic task completion when all subtasks are done
- **Trash Management**:
  - Soft deletion that moves tasks to trash instead of immediate removal
  - Restore option to recover tasks from trash
  - Permanent deletion option
  - Auto-cleanup after 30 days with countdown display

## Architecture
The application follows a service-oriented architecture with an Inertia.js monolith approach:
- **Controllers**: Handle HTTP requests and return Inertia responses that render Vue components
- **Services**: Contain business logic for task operations
- **Form Requests**: Validate incoming data
- **Models**: Represent database entities and relationships
- **Vue Components**: Frontend views rendered via Inertia.js without separate API endpoints
- **Console Commands**: Scheduled tasks for maintenance operations

## Testing

The application is extensively tested with PHPUnit to ensure reliability and correctness. Our test suite includes:

- **Unit Tests**: Testing individual components in isolation
- **Feature Tests**: Testing application features from controller to response
- **Task Tests**: Testing all CRUD operations, status updates, filtering, and sorting
- **Subtask Tests**: Testing adding, toggling, and deleting subtasks
- **Trash Management Tests**: Testing soft deletion, restoration, and permanent deletion
- **Authorization Tests**: Ensuring users can only access their own tasks

### Running Tests

Use our convenient test runner scripts:

**Windows:**
```bash
# Run all tests
.\run-tests.bat

# Run specific test groups
.\run-tests.bat task    # Run Task tests
.\run-tests.bat trash   # Run Task Trash tests
.\run-tests.bat auth    # Run Authentication tests
.\run-tests.bat unit    # Run all Unit tests
.\run-tests.bat feature # Run all Feature tests

# Run a specific test
.\run-tests.bat filter test_task_can_be_created
```

**Unix/Linux/Mac:**
```bash
# Make the script executable
chmod +x run-tests.sh

# Run all tests
./run-tests.sh

# Run specific test groups
./run-tests.sh task    # Run Task tests
./run-tests.sh trash   # Run Task Trash tests
./run-tests.sh auth    # Run Authentication tests
./run-tests.sh unit    # Run all Unit tests
./run-tests.sh feature # Run all Feature tests

# Run a specific test
./run-tests.sh filter test_task_can_be_created
```

Or use the standard Laravel command:
```bash
php artisan test
```

For detailed information about our testing approach, test cases, and troubleshooting, see [TESTING.md](TESTING.md).

## Development Workflow
- ESLint and TypeScript for code quality
- GitHub Actions for automated testing and linting
- Debugbar for performance monitoring
- Vue DevTools integration for frontend debugging


---

Developed as part of the TK Service Assessment.