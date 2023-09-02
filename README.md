# Flashcard CLI Application

An interactive CLI program for practicing flashcards.

## Getting Started

1. Clone this repository to your local machine:

   ```bash
   git clone https://github.com/ishifoev/flashcard-app

2. Navigate to the project directory:
 
   ```bash
   cd flashcard-app

3. Copy `.env.example` file and configure your database connection

    ```bash 
    cp .env.example .env

    Update the `.env` file with your database credentials. 

4. Start the Laravel Sail enviroment

    ```bash
    ./vendor/bin/sail up -d

5. Generate an application key

    ```bash
    ./vendor/bin/sail artisan key:generate

6. Run the database migrations
   
   ```bash
   ./vendor/bin/sail artisan migrate

## Usage

To start the Flashcard CLI application, run the following command

    ```bash
    ./vendor/bin/sail artisan flashcard:interactive
    ```

## Menu 

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
