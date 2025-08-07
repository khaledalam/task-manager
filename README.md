# Yii2 Task Manager API

A backend-focused Task Manager built with Yii2 (Basic Template), offering a complete RESTful API with JSON endpoints and a minimal frontend to demonstrate functionality.

---

<img src="./screenshot.png" />

## Features

- RESTful JSON API for Task CRUD operations
- Filtering by status, priority, title, due date range
- Pagination and sorting
- Validation with error codes (200, 201, 422, 404)
- Minimal frontend using HTML + Bootstrap + Axios
- Task view, edit, and delete with popup modal

---

## Project Structure

/models/Task.php<br />
/migrations/m250807_085720_create_task_table.php<br />
/controllers/TaskController.php<br />
/web/frontend/index.html<br />
/config/web.php<br />


## Setup Instructions

1. Clone or unzip the repo
2. Install dependencies:
```bash
composer install
```
   
3. Create database and configure DB settings in config/db.php
4. Run migration:
```bash
php yii migrate
```

Start server:
```bash
php yii serve
```
Visit the frontend:
```bash
http://localhost:8080
```