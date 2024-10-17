# Advanced Task Management System

## Overview

The Advanced Task Management System is a Laravel-based application designed to manage tasks efficiently. It includes advanced features like task assignment, filters, role-based authorization, and daily reports. This system allows teams to handle a large number of tasks, automate certain processes, and improve task management efficiency.

## Features

### Features

- Task Management: Create, update, delete, and filter tasks based on various attributes such as type, status, due date, priority, and assigned user.
- Role-based Authorization: Manage user permissions by role, allowing specific actions such as assigning, updating, or closing tasks.
- Advanced Filters: Apply multiple filters to retrieve tasks based on type, status, assigned user, priority, etc.
- Task Dependency: Manage dependencies between tasks to handle task workflows.
- Database Indexing: Indexing is applied to improve the performance of search and filtering queries.
- Error Logging: Errors are logged and stored for later analysis to improve the application’s performance.
- Daily Task Report: Generate daily reports for tasks, including completed, overdue, or tasks by user.
- Background Job Queues: Use job queues to handle performance analysis and reports in the background for improved scalability.


## Requirements

- PHP 8.0 or higher
- Composer
- Laravel 10
- MySQL or any compatible database


## Installation

1. **Clone the Repository:**
```
git clone https://github.com/amalSheikhdaher/Advanced_Task_Management_System.git
```

2. **Install Dependencies:**
```
composer install
```

3. **Set up the environment:**

   Copy the `.env.example` file and configure the database settings and other environment variables.
```
cp .env.example .env
php artisan key:generate
```

Then, set up the database connection and other necessary variables:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

4. **Run Migrations Run migrations to create the necessary tables.**

```
 php artisan migrate
```

5. **Seed the database:**


```
php artisan db:seed
```

6. **Set up Database (For Queue):** 
    Ensure Database is installed and running on your system. Then configure Database in your `.env`:

```
QUEUE_CONNECTION=database
```

7. **Run Queues (For Performance Reports):** 
    Use the Laravel queue worker to handle background jobs:

```
php artisan queue:work
```

8. **Serve the application:**

```
php artisan serve
```

Your application will be accessible at `http://localhost:8000`.

## Usage

### API Endpoints

1. **View All Tasks (With Advanced Filters):**

```
GET /api/tasks?type=Bug&status=Open&assigned_to=2&due_date=2024-09-30&priority=High
```

2. **Add a New Task:**

```
POST /api/tasks
Body:
{
    "title": "New Task",
    "description": "Description of the task",
    "type": "Feature",
    "status": "Open",
    "priority": "Medium",
    "due_date": "2024-10-15",
    "assigned_to": 1
}
```

3. **Update a Task:**

```
PUT /api/tasks/{taskId}
Body:
{
    "title": "Updated Task Title",
    "status": "In Progress"
}
```

4. **Delete a Task:**

```
DELETE /api/tasks/{taskId}
```

5. **Generate Daily Task Report:**

```
GET /api/reports/daily-tasks
```

## Error Handling

-All errors are logged into separate tables to allow for later analysis. You can analyze these logs by accessing the `error_logs` table in your database.

## Task Scheduling & Background Jobs

- Daily Task Reports are automatically generated and queued using Laravel's job queues.
- Schedule the job by adding the following to `app/Console/Kernel`.php:

```
$schedule->job(new DailyTaskReportJob)->daily();
```

## Performance Improvements
Database Indexing: Indexes are created on commonly filtered columns like type, status, and assigned_to to enhance query performance.


## Testing

Run unit and feature tests to ensure the application works as expected using Postman:

## Contributing

If you'd like to contribute to this project, feel free to fork the repository, make changes, and submit a pull request.

## License

This project is open-sourced under the [MIT License](LICENSE).
