# TK Service Task Management Application

A modern and responsive task management application built with Laravel and Vue.js. This application allows users to create, manage, and organize their tasks with a user-friendly interface and robust backend.

## Features

### Task Management
- Create, edit, update, and delete tasks
- Mark tasks as completed or in-progress
- Filter tasks by status (to-do, in-progress, done)
- Search tasks by title
- Publish/unpublish tasks to control visibility
- Task validation with custom rules
- Image attachment support for tasks

### User Interface
- Responsive design that works on desktop and mobile devices
- Dark mode support
- Modern and intuitive UI with a sidebar for navigation
- Pagination for large task lists
- Sort tasks by creation date or title
- Adjustable number of tasks per page (10, 20, 50, 100 options)

### Technical Implementation

#### Backend
- **Laravel Framework**: Robust PHP framework for the backend
- **Service Layer Architecture**: Separation of concerns with dedicated service classes
- **Form Request Validation**: Advanced validation rules for task creation and updates
- **RESTful API**: Well-structured API endpoints for task operations
- **Image Storage**: Secure image upload and storage for task attachments
- **Laravel Debugbar**: Enhanced debugging tools for development
- **Authentication System**: Secure user authentication and authorization

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

## Task Features
- **Title**: Required, unique, maximum 100 characters
- **Description**: Required field for task details
- **Status**: Must be one of: to-do, in-progress, done
- **Image Attachments**: Optional, accepts standard image formats, max 4MB
- **Publication Status**: Toggle for controlling task visibility

## Architecture
The application follows a service-oriented architecture:
- **Controllers**: Handle HTTP requests and responses
- **Services**: Contain business logic for task operations
- **Form Requests**: Validate incoming data
- **Models**: Represent database entities and relationships
- **Views**: Built with Vue.js components for a reactive UI

## Development Workflow
- ESLint and TypeScript for code quality
- GitHub Actions for automated testing and linting
- Debugbar for performance monitoring
- Vue DevTools integration for frontend debugging


---

Developed as part of the TK Service Assessment.