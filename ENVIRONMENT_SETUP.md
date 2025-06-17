# Environment Setup Guide

## Security Notice

This project uses environment variables to store sensitive information like API keys and database credentials. **Never commit your `.env` file to version control.**

## Initial Setup

1. **Copy the environment file:**

    ```bash
    cp .env.example .env
    ```

2. **Generate application key:**

    ```bash
    php artisan key:generate
    ```

3. **Configure your environment variables in `.env`:**

### Required Environment Variables

#### Database Configuration

```env
DB_CONNECTION=mysql
DB_HOST=your_database_host
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

#### OpenAI API Configuration

```env
OPENAI_API_KEY=your_openai_api_key_here
```

Get your API key from: https://platform.openai.com/api-keys

#### Session Configuration

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

#### Sanctum Configuration (for API authentication)

```env
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1
```

## Database Setup

1. **Create database tables:**

    ```bash
    php artisan migrate
    ```

2. **Seed database (optional):**
    ```bash
    php artisan db:seed
    ```

## Security Best Practices

1. **Never commit sensitive files:**

    - `.env` files are automatically ignored by git
    - Keep API keys and passwords in environment variables only

2. **Use strong passwords:**

    - Database passwords should be complex
    - Generate unique API keys for each environment

3. **Environment-specific configurations:**
    - Use different API keys for development/staging/production
    - Use different database credentials for each environment

## Verification

To verify your setup is secure and working:

1. **Check configuration:**

    ```bash
    php artisan config:show openai
    ```

2. **Test database connection:**

    ```bash
    php artisan migrate:status
    ```

3. **Verify git ignores sensitive files:**
    ```bash
    git status
    ```
    (Your `.env` file should not appear in the output)

## Troubleshooting

-   If you get "OpenAI API key not configured" errors, ensure `OPENAI_API_KEY` is set in your `.env` file
-   If database connections fail, verify your database credentials and server access
-   Clear config cache if changes don't take effect: `php artisan config:clear`
