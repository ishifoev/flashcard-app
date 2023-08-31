<?php

namespace App\Console\Commands;

use App\Models\Flashcard;
use Illuminate\Console\Command;

class FlashcardInteractiveCommand extends Command
{
    
    protected $signature = 'flashcard:interactive';

    protected $description = 'Interact with flashcards';

    public function handle()
    {
        $this->info("Welcome to Flashcard Interactive!");

        

        while(true) {
            //Implement main menu and user interactions
            $action = $this->choice("Select an option:", ["Create", "List", "Practice","Stats", "Reset", "Exit"]);

            //Handle different actions
            switch($action) {
                case "Create":
                    //Implement flashcard creation logic
                    $this->createFlashCard();
                    break;
                case "List":
                    //Implement flashcard listing logic
                    $this->listFlashcards();
                    break;
                case "Practice":
                    //Implement flashcard practice logic
                    $this->practiceFlashcards();
                    break;
                case "Stats":
                    //Implement flashcard stats logic
                    $this->displayStats();
                    break;
                case "Reset":
                    $this->resetProgress();
                    //Implement flashcard reset logic
                    break;
                case "Exit":
                    $this->info("Exiting Flashcard Interactive");
                    return;
                default:
                $this->error("Invalid action selected");
                break;
            }
        }
    }

    private function createFlashCard()
    {
        $question = $this->ask("Enter the flashcard question:");
        $answer = $this->ask("Enter the flashcard answer:");

        Flashcard::create([
            "question" => $question,
            "answer" => $answer
        ]);
        
        $this->info("Flashcard created successfully.");
    }

    private function listFlashcards() 
    {
        $flashcards = Flashcard::all();

        $this->table(["ID", "Question", "Answer"], $flashcards);
    }

    private function practiceFlashcards() 
    {
        //Implement flashcard practicing logic
    }

    private function displayStats()
    {
        //Implement flashcard statistics logic
    }

    private function resetProgress()
    {
        //Implement flashcard reset logic
    }
}