# API Reference

Base URL: `http://localhost:8000/api`

## Authentication

All endpoints require authentication via Laravel Sanctum API tokens, except where noted. Include the token in the `Authorization` header:

```http
Authorization: Bearer {your-token}
```

### Register User

Creates a new user account. Rate limited to 5 requests per minute.

**Endpoint:** `POST /api/v1/auth/register`

**Authentication:** None (rate limited)

**Request Body:**
```json
{
  "name": "string (required, max: 255)",
  "email": "string (required, email, max: 255, unique)",
  "password": "string (required, confirmed, min: 8)"
}
```

**Success Response (201):**
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "activated": false,
    "activated_at": null,
    "email_verified_at": null,
    "created_at": "2026-10-16T12:00:00.000000Z",
    "updated_at": "2026-10-16T12:00:00.000000Z"
  }
}
```

**Error Responses:**

| Status | Description | Response |
|--------|-------------|----------|
| 422 | Validation error | `{"errors": {"name": ["The name field is required."], "email": ["The email field is required."], "password": ["The password field is required."]}`
| 429 | Rate limit exceeded | `{"message": "Too Many Attempts."}` |

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

---

### Login

Authenticates a user and returns an API token. Rate limited to 5 requests per minute.

**Endpoint:** `POST /api/v1/auth/login`

**Authentication:** None (rate limited)

**Request Body:**
```json
{
  "email": "string (required, email)",
  "password": "string (required)"
}
```

**Success Response (200):**
```json
{
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "activated": true,
      "activated_at": "2026-10-16T12:00:00.000000Z",
      "email_verified_at": "2026-10-16T12:00:00.000000Z",
      "created_at": "2026-10-16T12:00:00.000000Z",
      "updated_at": "2026-10-16T12:00:00.000000Z"
    },
    "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz"
  }
}
```

**Error Responses:**

| Status | Description | Response |
|--------|-------------|----------|
| 422 | Invalid credentials | `{"errors": {"email": ["The provided credentials are incorrect."]}}`
| 422 | Account not activated | `{"errors": {"email": ["Your account is not activated. Please contact support."]}}`
| 429 | Rate limit exceeded | `{"message": "Too Many Attempts."}` |

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

---

### Logout

Revokes the current API token. Requires a valid Sanctum token.

**Endpoint:** `POST /api/v1/auth/logout`

**Authentication:** Required (Sanctum)

**Success Response (200):**
```json
{
  "message": "Successfully logged out"
}
```

**Error Responses:**

| Status | Description | Response |
|--------|-------------|----------|
| 401 | Unauthenticated | `{"message": "Unauthenticated"}` |

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Authorization: Bearer 1|AbCdEfGhIjKlMnOpQrStUvWxYz"
```

---

## User

### Get Authenticated User

Returns the currently authenticated user's information.

**Endpoint:** `GET /api/user`

**Authentication:** Required (Sanctum)

**Success Response (200):**
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "activated": true,
    "activated_at": "2026-10-16T12:00:00.000000Z",
    "email_verified_at": "2026-10-16T12:00:00.000000Z",
    "created_at": "2026-10-16T12:00:00.000000Z",
    "updated_at": "2026-10-16T12:00:00.000000Z"
  }
}
```

**Error Responses:**

| Status | Description | Response |
|--------|-------------|----------|
| 401 | Unauthenticated | `{"message": "Unauthenticated"}` |

**cURL Example:**
```bash
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer 1|AbCdEfGhIjKlMnOpQrStUvWxYz"
```

---

## Knowledge Management

### List Knowledge Entries

Returns paginated list of the authenticated user's knowledge entries. Supports filtering by type and tag, and searching.

**Endpoint:** `GET /api/v1/knowledge`

**Authentication:** Required (Sanctum)

**Query Parameters:**
- `type` (optional): Filter by type (`note`, `solution`, `command`, `snippet`)
- `tag` (optional): Filter by tag slug
- `search` (optional): Search query

**Success Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Laravel Tips",
      "content": "How to use Laravel effectively...",
      "summary": "A collection of Laravel tips",
      "type": "note",
      "metadata": null,
      "is_public": false,
      "captured_at": null,
      "created_at": "2026-10-16T12:00:00.000000Z",
      "updated_at": "2026-10-16T12:00:00.000000Z",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "activated": true,
        "activated_at": "2026-10-16T12:00:00.000000Z",
        "email_verified_at": "2026-10-16T12:00:00.000000Z",
        "created_at": "2026-10-16T12:00:00.000000Z",
        "updated_at": "2026-10-16T12:00:00.000000Z"
      },
      "git_context": {
        "id": 1,
        "repository_name": "my-project",
        "branch_name": "main",
        "commit_hash": "abc123"
      },
      "tags": [
        {
          "id": 1,
          "name": "Laravel",
          "slug": "laravel",
          "description": null,
          "color": "#FFCC00",
          "knowledge_count": 5,
          "created_at": "2026-10-16T12:00:00.000000Z",
          "updated_at": "2026-10-16T12:00:00.000000Z"
        }
      ]
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "links": [],
    "path": "http://localhost:8000/api/v1/knowledge",
    "per_page": 15,
    "to": 1,
    "total": 1
  }
}
```

**Error Responses:**

| Status | Description | Response |
|--------|-------------|----------|
| 401 | Unauthenticated | `{"message": "Unauthenticated"}` |

**cURL Example:**
```bash
curl -X GET "http://localhost:8000/api/v1/knowledge?type=note&tag=laravel&search=guide" \
  -H "Authorization: Bearer 1|AbCdEfGhIjKlMnOpQrStUvWxYz"
```

---

### Create Knowledge Entry

Creates a new knowledge entry. Requires a valid Sanctum token.

**Endpoint:** `POST /api/v1/knowledge`

**Authentication:** Required (Sanctum)

**Request Body:**
```json
{
  "title": "string (required, max: 255)",
  "content": "string (required)",
  "summary": "string (optional)",
  "type": "string (optional, enum: note,solution,command,snippet)",
  "metadata": "object (optional)",
  "is_public": "boolean (optional, default: false)",
  "tag_ids": "array (optional, of existing tag IDs)",
  "git_context": {
    "repository_url": "string (optional, url)",
    "repository_name": "string (optional)",
    "branch_name": "string (optional)",
    "commit_hash": "string (optional)"
  }
}
```

**Success Response (201):**
```json
{
  "data": {
    "id": 1,
    "title": "Laravel Tips",
    "content": "How to use Laravel effectively...",
    "summary": "A collection of Laravel tips",
    "type": "note",
    "metadata": null,
    "is_public": false,
    "captured_at": null,
    "created_at": "2026-10-16T12:00:00.000000Z",
    "updated_at": "2026-10-16T12:00:00.000000Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "activated": true,
      "activated_at": "2026-10-16T12:00:00.000000Z",
      "email_verified_at": "2026-10-16T12:00:00.000000Z",
      "created_at": "2026-10-16T12:00:00.000000Z",
      "updated_at": "2026-10-16T12:00:00.000000Z"
    },
    "git_context": {
      "id": 1,
      "repository_name": "my-project",
      "branch_name": "main",
      "commit_hash": "abc123"
    },
    "tags": []
  }
}
```

**Error Responses:**

| Status | Description | Response |
|--------|-------------|----------|
| 401 | Unauthenticated | `{"message": "Unauthenticated"}` |
| 422 | Validation error | `{"errors": {"title": ["The title field is required."], "content": ["The content field is required."], "type": ["The selected type is invalid."]}}` |

**cURL Example:**
```bash
curl -X POST http://localhost:8000/api/v1/knowledge \
  -H "Authorization: Bearer 1|AbCdEfGhIjKlMnOpQrStUvWxYz" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Laravel Tips",
    "content": "How to use Laravel effectively...",
    "type": "note",
    "is_public": false
  }'
```

---

### View Knowledge Entry

Returns a specific knowledge entry. Requires a valid Sanctum token and proper authorization (owner or public).

**Endpoint:** `GET /api/v1/knowledge/{id}`

**Authentication:** Required (Sanctum)

**Authorization:** User must own the entry or it must be public

**Success Response (200):**
```json
{
  "data": {
    "id": 1,
    "title": "Laravel Tips",
    "content": "How to use Laravel effectively...",
    "summary": "A collection of Laravel tips",
    "type": "note",
    "metadata": null,
    "is_public": false,
    "captured_at": null,
    "created_at": "2026-10-16T12:00:00.000000Z",
    "updated_at": "2026-10-16T12:00:00.000000Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "activated": true,
      "activated_at": "2026-10-16T12:00:00.000000Z",
      "email_verified_at": "2026-10-16T12:00:00.000000Z",
      "created_at": "2026-10-16T12:00:00.000000Z",
      "updated_at": "2026-10-16T12:00:00.000000Z"
    },
    "git_context": {
      "id": 1,
      "repository_name": "my-project",
      "branch_name": "main",
      "commit_hash": "abc123"
    },
    "tags": []
  }
}
```

**Error Responses:**

| Status | Description | Response |
|--------|-------------|----------|
| 401 | Unauthenticated | `{"message": "Unauthenticated"}` |
| 403 | Forbidden (not owner and not public) | (Laravel default 403 response) |

**cURL Example:**
```bash
curl -X GET http://localhost:8000/api/v1/knowledge/1 \
  -H "Authorization: Bearer 1|AbCdEfGhIjKlMnOpQrStUvWxYz"
```

---

### Update Knowledge Entry

Updates an existing knowledge entry. Requires a valid Sanctum token and ownership.

**Endpoint:** `PUT /api/v1/knowledge/{id}`

**Authentication:** Required (Sanctum)

**Authorization:** User must be the owner

**Request Body:** Same as create (title, content, summary, type, metadata, is_public, tag_ids, git_context)

**Success Response (200):**
```json
{
  "data": {
    "id": 1,
    "title": "Updated Title",
    "content": "Updated content...",
    "summary": null,
    "type": "note",
    "metadata": null,
    "is_public": false,
    "captured_at": null,
    "created_at": "2026-10-16T12:00:00.000000Z",
    "updated_at": "2026-10-16T12:00:00.000000Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "activated": true,
      "activated_at": "2026-10-16T12:00:00.000000Z",
      "email_verified_at": "2026-10-16T12:00:00.000000Z",
      "created_at": "2026-10-16T12:00:00.000000Z",
      "updated_at": "2026-10-16T12:00:00.000000Z"
    },
    "git_context": {
      "id": 1,
      "repository_name": "my-project",
      "branch_name": "main",
      "commit_hash": "abc123"
    },
    "tags": []
  }
}
```

**Error Responses:**

| Status | Description | Response |
|--------|-------------|----------|
| 401 | Unauthenticated | `{"message": "Unauthenticated"}` |
| 403 | Forbidden (not owner) | (Laravel default 403 response) |
| 422 | Validation error | `{"errors": {"title": ["The title field is required."], "content": ["The content field is required."]}}` |

**cURL Example:**
```bash
curl -X PUT http://localhost:8000/api/v1/knowledge/1 \
  -H "Authorization: Bearer 1|AbCdEfGhIjKlMnOpQrStUvWxYz" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Updated Title",
    "content": "Updated content...",
    "tag_ids": [1, 2]
  }'
```

---

### Delete Knowledge Entry

Deletes a knowledge entry. Requires a valid Sanctum token and ownership.

**Endpoint:** `DELETE /api/v1/knowledge/{id}`

**Authentication:** Required (Sanctum)

**Authorization:** User must be the owner

**Success Response (200):**
```json
{
  "message": "Knowledge deleted successfully"
}
```

**Error Responses:**

| Status | Description | Response |
|--------|-------------|----------|
| 401 | Unauthenticated | `{"message": "Unauthenticated"}` |
| 403 | Forbidden (not owner) | (Laravel default 403 response) |

**cURL Example:**
```bash
curl -X DELETE http://localhost:8000/api/v1/knowledge/1 \
  -H "Authorization: Bearer 1|AbCdEfGhIjKlMnOpQrStUvWxYz"
```

---

## Search

### Search Knowledge

Searches across accessible knowledge entries (public or user's own). Requires a valid Sanctum token.

**Endpoint:** `GET /api/v1/search/knowledge`

**Authentication:** Required (Sanctum)

**Query Parameters:**
- `q` (optional): Search query string

**Success Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Laravel Tips",
      "content": "How to use Laravel effectively...",
      "summary": null,
      "type": "note",
      "metadata": null,
      "is_public": false,
      "captured_at": null,
      "created_at": "2026-10-16T12:00:00.000000Z",
      "updated_at": "2026-10-16T12:00:00.000000Z",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "activated": true,
        "activated_at": "2026-10-16T12:00:00.000000Z",
        "email_verified_at": "2026-10-16T12:00:00.000000Z",
        "created_at": "2026-10-16T12:00:00.000000Z",
        "updated_at": "2026-10-16T12:00:00.000000Z"
      },
      "git_context": null,
      "tags": []
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "links": [],
    "path": "http://localhost:8000/api/v1/search/knowledge",
    "per_page": 10,
    "to": 1,
    "total": 1
  }
}
```

**Error Responses:**

| Status | Description | Response |
|--------|-------------|----------|
| 401 | Unauthenticated | `{"message": "Unauthenticated"}` |

**cURL Example:**
```bash
curl -X GET "http://localhost:8000/api/v1/search/knowledge?q=Laravel" \
  -H "Authorization: Bearer 1|AbCdEfGhIjKlMnOpQrStUvWxYz"
```

---

## Tags

### List All Tags

Returns all available tags in the system.

**Endpoint:** `GET /api/v1/tags`

**Authentication:** Required (Sanctum)

**Success Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Laravel",
      "slug": "laravel",
      "description": null,
      "color": "#FFCC00",
      "knowledge_count": 5,
      "created_at": "2026-10-16T12:00:00.000000Z",
      "updated_at": "2026-10-16T12:00:00.000000Z"
    }
  ]
}
```

**Error Responses:**

| Status | Description | Response |
|--------|-------------|----------|
| 401 | Unauthenticated | `{"message": "Unauthenticated"}` |

**cURL Example:**
```bash
curl -X GET http://localhost:8000/api/v1/tags \
  -H "Authorization: Bearer 1|AbCdEfGhIjKlMnOpQrStUvWxYz"
```

---

## Webhook

### Deploy Webhook

GitHub webhook endpoint for deployments. No authentication required, but verifies webhook signature.

**Endpoint:** `POST /webhook/deploy`

**Authentication:** None (signature verification only)

**Headers:**
- `X-Hub-Signature-256`: GitHub webhook signature (required)

**Request Body (GitHub payload):**
```json
{
  "ref": "refs/heads/master",
  "after": "a1b2c3d4e5f6...",
  "repository": {
    "full_name": "owner/repo",
    "default_branch": "master"
  }
}
```

**Success Response (200):**
```json
{
  "status": "deployment_queued",
  "commit": "a1b2c3d",
  "message": "Deployment started successfully"
}
```

**Error Responses:**

| Status | Description | Response |
|--------|-------------|----------|
| 401 | Invalid webhook signature | `Unauthorized` |
| 422 | Invalid JSON | (Laravel validation error) |

**cURL Example:**
```bash
curl -X POST http://localhost:8000/webhook/deploy \
  -H "X-Hub-Signature-256: sha256=..." \
  -H "Content-Type: application/json" \
  -d '{
    "ref": "refs/heads/master",
    "after": "a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0",
    "repository": {"full_name": "owner/repo"}
  }'
```

---

## Error Response Formats

### Validation Errors (422)

Laravel returns validation errors in the following format:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message 1", "Error message 2"]
  }
}
```

### Authentication Errors (401)

```json
{
  "message": "Unauthenticated."
}
```

### Authorization Errors (403)

Laravel's default 403 response (no custom message defined).

### Rate Limiting Errors (429)

```json
{
  "message": "Too Many Attempts."
}
```
