# Prerequisit

Please make sure you :

-   already install postgresql
-   install [PHP](https://www.php.net/downloads.php) (version 8.2)
-   activate extension for postgresql in `php.ini` by removing the ';' in 'extension=pdo_mysql'

# Getting Started

After clone this repo, you need to run ;

```bash
# install dependencies
composer install
# migrate the database
php artisan migrate
# run server
php artisan serve

```

# API Documentation 📝

**Base Endpoint : `/api`**

## Auth Endpoint

> [!NOTE]
> Before using auth endpoint especially for login, your SPA's "login" page should first make a request to the `/sanctum/csrf-cookie` endpoint to initialize CSRF protection for the application:.
>
> ```
> axios.get('/sanctum/csrf-cookie').then(response => {
>    // Login...
> });
> ```
>
> During this request, Laravel will set an `XSRF-TOKEN` cookie containing the current CSRF token. This token should then be passed in an `X-XSRF-TOKEN` header on subsequent requests, which some HTTP client libraries like Axios and the Angular HttpClient will do automatically for you. If your JavaScript HTTP library does not set the value for you, you will need to manually set the `X-XSRF-TOKEN` header to match the value of the `XSRF-TOKEN` cookie that is set by this route.

| Method | Endpoint    | Params Required | Need Login | Body                                                                                                                        | Description                    |
| ------ | ----------- | --------------- | ---------- | --------------------------------------------------------------------------------------------------------------------------- | ------------------------------ |
| GET    | `/profile`  | No params       | Yes        | -                                                                                                                           | Get profile authenticated user |
| POST   | `/login`    | No params       | No         | `{"email": string\|required, "password": string\|required}`                                                                 | Login existing user            |
| POST   | `/register` | No params       | No         | `{"email": string\|required, "name": string\|required, "password": string\|required, "confirm_password": string\|required}` | Registration user              |
| POST   | `/logout`   | No params       | Yes        |                                                                                                                             | Logout authenticated user      |

## Post Endpoint

**Endpoint : [base](#api-documentation-) + /post**

| Method | Endpoint   | Params Required | Need Login | Body                                                    | Description                                       |
| ------ | ---------- | --------------- | ---------- | ------------------------------------------------------- | ------------------------------------------------- |
| GET    | `/get-all` | No params       | No         | -                                                       | Get all posts                                     |
| GET    | `/`        | No params       | Yes        | -                                                       | Get all posts belonging to the authenticated user |
| GET    | `/{id}`    | Yes             | No         | -                                                       | Get detail one post                               |
| POST   | `/`        | No params       | Yes        | `{"title": string\|required, "body": string\|required}` | Create a new post for authenticated user          |
| PATCH  | `/{id}`    | Yes             | Yes        | `{"title": string\|optional, "body": string\|optional}` | Update a existing post for authenticated user     |
| DELETE | `/{id}`    | Yes             | Yes        | -                                                       | Delete a existing post for authenticated user     |

## Comment Endpoint

**Endpoint : [base](#api-documentation-) + /comment**

| Method | Endpoint       | Params Required | Need Login | Body                         | Description                                                     |
| ------ | -------------- | --------------- | ---------- | ---------------------------- | --------------------------------------------------------------- |
| GET    | `/user`        | No params       | Yes        | -                            | Get all comments that user have                                 |
| GET    | `/{postId}`    | Yes             | No         | -                            | Get all comments on detail post                                 |
| POST   | `/{postId}`    | Yes             | Yes        | `{"body": string\|required}` | Create a new comment on detail post for authenticated user      |
| PATCH  | `/{commentId}` | Yes             | Yes        | `{"body": string\|optional}` | Update a existing comment on detail post for authenticated user |
| DELETE | `/{commentId}` | Yes             | Yes        | -                            | Delete a existing comment on detail post for authenticated user |
