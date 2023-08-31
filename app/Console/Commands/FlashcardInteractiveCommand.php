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
        $flashcards = Flashcard::all();
        $totalFlashCards = count($flashcards);

        $correctlyAnswered = 0;

        foreach($flashcards as $flashcard) {
            $userAnswer = $this->ask("Q: {$flashcard->question}");

            if(strtolower($userAnswer) === strtolower($flashcard->answer)) {
                $this->info("Correct!");
                $correctlyAnswered++;
            } else {
                $this->error("Incorrect!");
            }
            //if(strtolow)
        }

        $completionPercentage = ($correctlyAnswered / $totalFlashCards) * 100;
        $this->info("Practice session complete. Completion {$completionPercentage}%");
    }

    private function displayStats()
    {
        $totalFlashCards = Flashcard::count();
        $answeredFlashcards = Flashcard::whereNotNull("user_answer")->count();
        $correctlyAnsweredFlashcards = Flashcard::whereColumn("user_answer", "answer")->count();

        $answerPercentage = ($answeredFlashcards / $totalFlashCards) * 100;
        $correctPercentage = ($correctlyAnsweredFlashcards / $answeredFlashcards) * 100;

        $this->info("Total flashcards: {$totalFlashCards}");
        $this->info("Answered flashcards: {$answeredFlashcards} {$answerPercentage}$");
        $this->info("Correctly answered flashcards: {$correctlyAnsweredFlashcards} {$answeredPercentage}")
    }

    private function resetProgress()
    {
        //Implement flashcard reset logic
    }
}
