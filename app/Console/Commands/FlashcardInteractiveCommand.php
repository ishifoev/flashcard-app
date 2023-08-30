<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FlashcardInteractiveCommand extends Command
{
    
    protected $signature = 'flashcard:interactive';

    protected $description = 'Interact with flashcards';

    public function handle()
    {
        $this->info("Welcome to Flashcard Interactive!");

        //Implement main menu and user interactions

        $action = $this->choice("Select an option:", ["Create", "List", "Practice","Stats", "Reset", "Exit"]);

        //Handle different actions
        switch($action) {
            case "Create":
                //Implement flashcard creation logic
                break;
            case "List":
                //Implement flashcard listing logic
                break;
            case "Practice":
                //Implement flashcard practice logic
                break;
            case "Stats":
                //Implement flashcard stats logic
                break;
            case "Reset":
                //Implement flashcard reset logic
                break;
            case "Exit":
                $this->info("Exiting Flashcard Interactive");
                return;
            default:
               $this->error("Invalid action selected");
               break;
        }
        
        $this->line("Thank you for using Flashcard interactive!");
    }
}
