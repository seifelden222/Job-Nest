# Portfolio API

Base path: `/api/auth/portfolio`  
Authentication: Sanctum (Bearer token)

## 1. List My Portfolio Items

- Method: `GET`  
- Endpoint: `/api/auth/portfolio`  
- Description: Returns all portfolio items belonging to the authenticated user.

Response Example:

```json
{
  "message": "Portfolio items retrieved successfully.",
  "data": [
    {
      "id": 1,
      "title": "E-commerce Mobile App",
      "description": "Built a Flutter e-commerce application with Laravel backend.",
      "project_url": "https://example.com/project",
      "github_url": "https://github.com/example/project",
      "image": null,
      "technologies": ["Flutter", "Laravel", "MySQL"],
      "role": "Full Stack Developer",
      "start_date": "2025-01-01",
      "end_date": "2025-03-01",
      "is_featured": true,
      "created_at": "2026-05-19T10:00:00.000000Z",
      "updated_at": "2026-05-19T10:00:00.000000Z"
    }
  ]
}
```

## 2. Create Portfolio Item

- Method: `POST`  
- Endpoint: `/api/auth/portfolio`  
- Description: Create a new portfolio item for the authenticated user.

Request Body Example (JSON):

```json
{
  "title": "E-commerce Mobile App",
  "description": "Built a Flutter e-commerce application with Laravel backend.",
  "project_url": "https://example.com/project",
  "github_url": "https://github.com/example/project",
  "technologies": ["Flutter", "Laravel", "MySQL"],
  "role": "Full Stack Developer",
  "start_date": "2025-01-01",
  "end_date": "2025-03-01",
  "is_featured": true
}
```

Response Example (201 Created):

```json
{
  "message": "Portfolio item created successfully.",
  "data": {
    "id": 1,
    "title": "E-commerce Mobile App",
    "description": "Built a Flutter e-commerce application with Laravel backend.",
    "project_url": "https://example.com/project",
    "github_url": "https://github.com/example/project",
    "image": null,
    "technologies": ["Flutter", "Laravel", "MySQL"],
    "role": "Full Stack Developer",
    "start_date": "2025-01-01",
    "end_date": "2025-03-01",
    "is_featured": true,
    "created_at": "2026-05-19T10:00:00.000000Z",
    "updated_at": "2026-05-19T10:00:00.000000Z"
  }
}
```

## 3. Show Portfolio Item

- Method: `GET`  
- Endpoint: `/api/auth/portfolio/{portfolio}`  
- Description: Retrieve a single portfolio item belonging to the authenticated user.

Response Example:

```json
{
  "message": "Portfolio item retrieved successfully.",
  "data": {
    "id": 1,
    "title": "E-commerce Mobile App",
    "description": "Built a Flutter e-commerce application with Laravel backend.",
    "project_url": "https://example.com/project",
    "github_url": "https://github.com/example/project",
    "image": "https://example.com/storage/portfolio/item-1.png",
    "technologies": ["Flutter", "Laravel", "MySQL"],
    "role": "Full Stack Developer",
    "start_date": "2025-01-01",
    "end_date": "2025-03-01",
    "is_featured": true,
    "created_at": "2026-05-19T10:00:00.000000Z",
    "updated_at": "2026-05-19T10:00:00.000000Z"
  }
}
```

## 4. Update Portfolio Item

- Method: `PUT`  
- Endpoint: `/api/auth/portfolio/{portfolio}`  
- Description: Update an existing portfolio item.

Request Body Example: same fields as Create (send only fields to update).

Response Example:

```json
{
  "message": "Portfolio item updated successfully.",
  "data": {
    "id": 1,
    "title": "Updated E-commerce Mobile App",
    "description": "Updated portfolio description.",
    "project_url": "https://example.com/updated-project",
    "github_url": "https://github.com/example/updated-project",
    "image": "https://example.com/storage/portfolio/item-1.png",
    "technologies": ["Flutter", "Laravel", "PostgreSQL"],
    "role": "Lead Developer",
    "start_date": "2025-01-01",
    "end_date": "2025-04-01",
    "is_featured": false,
    "created_at": "2026-05-19T10:00:00.000000Z",
    "updated_at": "2026-05-19T11:00:00.000000Z"
  }
}
```

## 5. Delete Portfolio Item

- Method: `DELETE`  
- Endpoint: `/api/auth/portfolio/{portfolio}`  
- Description: Delete a portfolio item.

Response Example:

```json
{
  "message": "Portfolio item deleted successfully."
}
```

---

# My Applications API

Base path: `/api/auth/my-applications`  
Authentication: Sanctum (Bearer token)

## List My Applications

- Method: `GET`  
- Endpoint: `/api/auth/my-applications`  
- Description: Returns all job applications created by the authenticated user.

Response Example:

```json
{
  "message": "My applications retrieved successfully.",
  "data": [
    {
      "id": 5,
      "status": "under_review",
      "cover_letter": "I am interested in this opportunity.",
      "created_at": "2026-05-19T09:00:00.000000Z",
      "updated_at": "2026-05-19T09:30:00.000000Z",
      "job": {
        "id": 12,
        "title": "Backend Developer",
        "location": "Cairo",
        "employment_type": "full_time",
        "salary_min": 10000,
        "salary_max": 15000,
        "company": {
          "id": 7,
          "name": "Tech Corp"
        }
      },
      "document": {
        "id": 3,
        "title": "Updated CV",
        "type": "cv",
        "file_url": "https://example.com/storage/documents/cv.pdf"
      }
    }
  ]
}
```
