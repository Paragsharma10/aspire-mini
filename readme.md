**Aspire Mini Application API Documentation**

This API documentation provides information on how to interact with the Loan Application API built using Laravel. The API allows users to apply for loans and retrieve loan application data.

Installation
To run the Loan Application API locally, follow these steps:

Clone the repository:
git clone https://github.com/Paragsharma10/aspire-mini.git

Navigate to the project directory:
cd aspire-mini

Install dependencies using Composer:
composer install

Create a copy of the .env.example file and rename it to .env. Update the .env file with your database configuration details:
cp .env.example .env

Generate an application key:
php artisan key:generate

Run database migrations:
php artisan migrate --seed

Start the development server:
php artisan serve

Clear the chache and config:
php artisan optimize

The API will now be accessible at http://localhost:8000.

Base URL
The base URL for all API endpoints is: http://localhost:8000/api/

Authentication
API authentication is required for certain endpoints. To authenticate, include the Authorization header in your requests with a valid API token.


Import Postman collection
https://www.postman.com/Parag10/workspace/aspire-mini/collection/7693276-e8c8b6a8-360c-43cf-92bc-036435196bc9?action=share&creator=7693276

For Authorization:
php artisan passport:install
php artisan migrate

Authorization: Bearer {API_TOKEN}
To obtain an API token, please contact the API administrator.


Change the passport client and id in postman collection



