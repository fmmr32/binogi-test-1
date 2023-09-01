# Author: Mushfiqur Rahman

The Laravel Code Challange for Binogi

### STEPS For Core changes

1. create a migration file for nicknames in database which includes up and down logic (database->migrations-> fileName)
2. make changes in http->controller->userController to include nicknames
3. include nicknames rule in support->userStoreRequest and support->userUpdateRequest
4. include nickname in userMapper (mappers->userMapper)
5. include nickname in Schema (models->user->user)
6. make appropriate changes for nicknames in api calls (storage->api-docs->api-docs.json)
7. double check to make sure you adhere to SOLID principle

### STEPS For Testing

1. include nicknames in all acceptance and integration files where it is concerned.
2. create new tests that determines the integrity of the new addition of nicknames in DB and code base (eg. duplication, exceeding characters etc)

### Notes for new Testings

-   I included 6 more tests whose purpose is to fail.
    list of the new tests:
    1. Repository Test: Create fails without sufficient/ required data
    2. Integration Test: update to existing nickname
    3. Integration Test: create request fails with duplicate nickname
    4. Integration Test: case insensitive unique nickname
    5. Integration Test: update request fails with long nickname
    6. Integration Test: create request fails with invalid nickname length

### Difficulties faced

1. VS code's default terminal is powershell and powershell does not recognize the command 'CREATE'. so the given
   code for test DB docker "" exec -it mysql bash -c "mysql -u root -ppassword -e \"DROP DATABASE IF EXISTS testing; CREATE DATABASE testing\"" (creates the test DB) ""
   throws error.

    - it can be fixed by changing the syntax that follows powershell.
    - easiest way to implement is changing the terminal to CMD in vs code

# Laravel Code Challenge

This code test involves performing work on an existing Laravel project.
The task is split into several sub-categories and shouldn't take longer than 2-4 hours of your time.

### Restrictions and Requirements

1. This challenge requires Docker to be installed on your system. The easiest way to accomplish this is to [install Docker Desktop](https://www.docker.com/).
2. You should focus on code quality and structure.
3. Wherever possible, try to follow the [SOLID principles](https://en.wikipedia.org/wiki/SOLID).

### Setup

This repository has been set up for you to start right away. We are using Docker to ensure that
this code challenge can be run locally on your machine, regardless of your installed system environment.

-   The project can be brought up by running the following commands from the root directory of the project:
    -   `docker-compose up --remove-orphans`
    -   `docker-compose run --rm php composer install`
    -   `docker-compose run --rm php /var/www/artisan migrate:fresh --seed`
    -   `docker-compose run --rm php /var/www/artisan l5-swagger:generate`

### The Challenge

You have been given access to a list of users.
The assignment is to add a column named `nickname` (via a migration) to the database as well as updating the related endpoints.

1. The GET request needs to include the new column.
2. The POST request and the PUT request need to be able to change the value of the column asserting the following validation rules:
    - A valid `nickname` must be <ins> unique </ins> among users.
    - A valid `nickname` must be <ins> shorter </ins> than 30 characters.
3. Documentation should be updated so Swagger can be generated and used to smoke test.
    - We are using the open-source package [L5-Swagger](https://github.com/DarkaOnLine/L5-Swagger) to generate OpenAPI Swagger.
4. Tests
    - Integration and Acceptance tests should be updated to reflect your changes
    - Tests should be added to assert that your changes function as expected
    - Tests should be added to assert that given "bad" cases will fail (assert failures)

#### Additional Instructions

-   Please carefully review the existing codebase and find/fix(handle) any errors that you come across. For instance, make sure to handle 404 errors for all requests appropriately. Thoroughly test your fixes to ensure that the application behaves as expected under various scenarios.
-   Feel free to add relevant comments and documentation in the code to explain any changes you make or any assumptions you may have made during the process.

### Submitting Your Work

1. When you are ready to submit your work: do not open a PR.
2. Instead, push your changes to a public repository on GitHub and email a link to [cto@binogi.com](cto@binogi.com).
3. In the email please specify your name in the subject field.

### Hints

-   If you are developing on a Windows machine or an Intel Mac machine, you may need to remove `platform: linux/x86_64` from the `docker-compose.yml` file (under mysql)
-   The OpenAPI Swagger documentation can be generated on demand by running `docker-compose run --rm php /var/www/artisan l5-swagger:generate` in the root directory of the project.
    -   This documentation can be viewed by navigating to [http://localhost:7777/api/documentation](http://localhost:7777/api/documentation).
-   Don't worry about authentication.
-   Tests can be run by executing the following commands:
    -   `docker exec -it mysql bash -c "mysql -u root -ppassword -e \"DROP DATABASE IF EXISTS testing; CREATE DATABASE testing\""` (creates the test DB)
    -   `docker-compose run --rm php php /var/www/artisan test` (runs the tests)
