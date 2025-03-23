# Job Listing API

A modern and flexible API for a job board application built with Laravel. This API provides comprehensive job listing functionality with advanced filtering capabilities.

## Features

- **Job Listings Management**: Create, read, update, and delete job postings with rich metadata.
- **Advanced Filtering System**: Powerful query capabilities for jobs based on various criteria.
- **EAV (Entity-Attribute-Value) Pattern**: Flexible custom attributes for job listings.
- **Relationship Support**: Associate jobs with categories, locations, and programming languages.

## Data Models

### Core Models

- **Job**: The central entity with standard job listing fields:
  - Title, description, company name
  - Salary range (min/max)
  - Remote work option
  - Job type (full-time, part-time, contract, freelance)
  - Status (draft, published, archived)
  - Published date and standard timestamps

### Related Models

- **Category**: Job categories for classification (e.g., Frontend Development, DevOps)
- **Location**: Geographical locations for job positions
- **Language**: Programming languages required for the job
- **Attribute**: Custom attributes for jobs using the EAV pattern

## Advanced Filtering System

The API includes a powerful filtering system that allows complex queries with:

### Field Type Filtering

- **Text/String fields**:
  - Equality: `=`, `!=`
  - Contains: `LIKE`
- **Numeric fields**:
  - Equality: `=`, `!=`
  - Comparison: `>`, `<`, `>=`, `<=`
- **Boolean fields**:
  - Equality: `=`, `!=`
- **Enum fields**:
  - Equality: `=`, `!=`
  - Multiple values: `IN`
- **Date fields**:
  - Equality: `=`, `!=`
  - Comparison: `>`, `<`, `>=`, `<=`

### Relationship Filtering

- **Operations supported**:
  - Equality (`=`): Exact match - all values must be present
  - Has any of (`HAS_ANY`): Job has any of the specified values
  - Is any of (`IS_ANY`): Relationship matches any of the values
  - Existence (`EXISTS`): Relationship exists

### EAV Filtering by Attribute Type

- Support for filtering by custom attributes based on their type (text, number, boolean, date, select)

### Logical Operators

- Support for `AND`/`OR` logical operators
- Support for grouping conditions with parentheses

## Query Language Reference

The Job Board API implements a powerful custom query language for filtering jobs. This query language provides a flexible way to search and filter jobs based on various criteria.

### Query Structure

Queries are passed as a URL parameter using the `filter` parameter:

```
GET /api/jobs?filter=expression
```

Where `expression` is a filtering expression that follows the syntax described below.

### Basic Syntax

The basic syntax for filtering is:

```
field operator value
```

- **field**: The name of a job field (e.g., `title`, `salary_min`, `is_remote`)
- **operator**: One of the supported operators (e.g., `=`, `>`, `LIKE`)
- **value**: The value to compare against

### Supported Operators by Field Type

#### Text Fields (title, description, company_name)
- `=`: Exact match (e.g., `title=Senior Developer`)
- `!=`: Not equal to (e.g., `title!=Junior Developer`)
- `LIKE`: Contains substring (e.g., `title LIKE Developer`)

#### Numeric Fields (salary_min, salary_max)
- `=`: Equal to (e.g., `salary_min=50000`)
- `!=`: Not equal to (e.g., `salary_min!=40000`)
- `>`: Greater than (e.g., `salary_min>60000`)
- `<`: Less than (e.g., `salary_max<100000`)
- `>=`: Greater than or equal to (e.g., `salary_min>=55000`)
- `<=`: Less than or equal to (e.g., `salary_max<=120000`)

#### Boolean Fields (is_remote)
- `=`: Equal to (e.g., `is_remote=true`)
- `!=`: Not equal to (e.g., `is_remote!=false`)

#### Enum Fields (job_type, status)
- `=`: Equal to (e.g., `job_type=full-time`)
- `!=`: Not equal to (e.g., `status!=archived`)
- `IN`: One of multiple values (e.g., `job_type IN (full-time,part-time)`)

#### Date Fields (published_at, created_at, updated_at)
- `=`: Equal to (e.g., `published_at=2023-01-01`)
- `!=`: Not equal to (e.g., `published_at!=2023-01-01`)
- `>`: After date (e.g., `published_at>2023-01-01`)
- `<`: Before date (e.g., `published_at<2023-01-01`)
- `>=`: On or after date (e.g., `published_at>=2023-01-01`)
- `<=`: On or before date (e.g., `published_at<=2023-01-01`)

### Relationship Filtering

To filter by relationships, use the following syntax:

```
relation operator (value1,value2,...)
```

- **relation**: One of the supported relationships (`categories`, `locations`, `languages`)
- **operator**: One of the supported relationship operators
- **values**: A comma-separated list of values enclosed in parentheses

#### Relationship Operators
- `=`: Exact match (job has ALL of the specified values)
- `HAS_ANY`: Job has at least one of the specified values
- `IS_ANY`: Job matches any of the values
- `EXISTS`: Relationship exists (no values needed)

Examples:
- `languages=(PHP,JavaScript)` - Jobs requiring both PHP AND JavaScript
- `languages HAS_ANY (PHP,JavaScript)` - Jobs requiring either PHP OR JavaScript
- `locations IS_ANY (New York,San Francisco)` - Jobs in New York OR San Francisco
- `categories EXISTS` - Jobs that have at least one category

### Custom Attribute Filtering

To filter by custom attributes (EAV pattern), use the following syntax:

```
attribute:name operator value
```

- **name**: The name of the attribute (e.g., `years_experience`, `education_level`)
- **operator**: Operator appropriate for the attribute type
- **value**: The value to compare against

Examples:
- `attribute:years_experience>=3` - Jobs requiring 3+ years of experience
- `attribute:education_level=Bachelor's` - Jobs requiring a Bachelor's degree
- `attribute:certification_required=true` - Jobs requiring certification

### Logical Operators and Grouping

You can combine multiple conditions using logical operators:

- `AND`: Both conditions must be true
- `OR`: At least one condition must be true

You can also use parentheses to group conditions and control precedence:

```
(condition1 AND condition2) OR condition3
```

Examples:
- `title LIKE Developer AND salary_min>=60000`
- `(job_type=full-time OR job_type=contract) AND is_remote=true`
- `locations IS_ANY (New York,San Francisco) OR is_remote=true`

### Value Formatting

- **Text values**: Can be unquoted if they contain no spaces or special characters, otherwise use quotes
- **Numbers**: Written as-is without quotes (e.g., `50000`)
- **Booleans**: Use `true` or `false` without quotes
- **Dates**: Use ISO format `YYYY-MM-DD` (e.g., `2023-01-01`)
- **Lists**: Comma-separated values within parentheses (e.g., `(PHP,JavaScript)`)

### Advanced Usage Tips

1. **Combining Field and Relationship Filters**:
   ```
   (job_type=full-time AND salary_min>=50000) AND languages HAS_ANY (PHP,JavaScript)
   ```

2. **Filtering by Multiple Attributes**:
   ```
   attribute:years_experience>=3 AND attribute:education_level=Master's
   ```

3. **Complex Location Requirements**:
   ```
   (locations IS_ANY (New York,San Francisco) OR is_remote=true) AND salary_min>=80000
   ```

4. **Filtering Recently Posted Jobs**:
   ```
   published_at>=2023-01-01 AND status=published
   ```

5. **Filtering by Salary Range and Job Type**:
   ```
   salary_min>=60000 AND salary_max<=100000 AND job_type IN (full-time,contract)
   ```

## Query Examples

```
# Find remote full-time jobs requiring PHP or JavaScript
filter=(job_type=full-time AND is_remote=true) AND (languages HAS_ANY (PHP,JavaScript))

# Find senior jobs with 3+ years experience in New York or allowing remote work
filter=(seniority_level=Senior OR attribute:years_experience>=3) AND (locations IS_ANY (New York,Remote))

# Find recent jobs in the technology industry with a specific salary range
filter=(published_at>=2023-01-01) AND (salary_min>=70000 AND salary_max<=120000) AND attribute:industry=Technology
```

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/Mina-Nabil/astudio-task.git
   cd astudio-task
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

4. Configure your database in the `.env` file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=job_board
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Run migrations and seed the database:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. Start the development server:
   ```bash
   php artisan serve
   ```

## API Documentation

Full API details can be found on postman collection (https://documenter.getpostman.com/view/38975133/2sAYkHodX7)

### Jobs Endpoints

- `GET /api/jobs?filter=expression` - List all jobs with pagination (Filter jobs with advanced query syntax using filter parameter)
- `GET /api/jobs/{id}` - Get a specific job
- `POST /api/jobs` - Create a new job
- `PUT /api/jobs/{id}` - Update a job
- `DELETE /api/jobs/{id}` - Delete a job

### Categories Endpoints

- `GET /api/categories` - List all job categories
- `GET /api/categories/{id}` - Get a specific category
- `GET /api/categories/{id}/jobs` - Get jobs for a specific category

### Locations Endpoints

- `GET /api/locations` - List all locations
- `GET /api/locations/{id}` - Get a specific location
- `GET /api/locations/{id}/jobs` - Get jobs for a specific location

### Languages Endpoints

- `GET /api/languages` - List all programming languages
- `GET /api/languages/{id}` - Get a specific language
- `GET /api/languages/{id}/jobs` - Get jobs requiring a specific language

### Attributes Endpoints

- `GET /api/attributes` - List all custom attributes
- `GET /api/attributes/{id}` - Get a specific attribute

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- Laravel team for the amazing framework