<?php

namespace App\Console\Commands;

use App\Models\Flashcard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

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

        $validator = Validator::make([
            "question" => $question,
            "answer" => $answer
        ],
        [
            "question"=> "required|string|max:255",
            "answer" =>"required|string|max:255",
        ]
        );
        if($validator->fails()) {
            $this->error("Validation failed:");
            foreach($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return;
        }

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
                $flashcard->update(["user_answer" => $userAnswer]);
            } else {
                $this->error("Incorrect!");
            }
            //if(strtolow)
        }

        $completionPercentage = ($totalFlashCards > 0) ? round(($correctlyAnswered / $totalFlashCards) * 100, 2) : 0;
         $this->info("Practice session complete. Completion: {$completionPercentage}%");
    }

    private function displayStats()
    {
        $totalFlashcards = Flashcard::count(); 
        $answeredFlashcards = Flashcard::whereNotNull('user_answer')->count(); 
        $correctlyAnsweredFlashcards = Flashcard::where('user_answer', Flashcard::raw('answer'))->count(); 
        
        $answeredPercentage = $totalFlashcards > 0 ? round(($answeredFlashcards / $totalFlashcards) * 100, 2) : 0; 
        $correctPercentage = $answeredFlashcards > 0 ? round(($correctlyAnsweredFlashcards / $answeredFlashcards) * 100, 2) : 0; 
        $this->info("Total flashcards: {$totalFlashcards}"); $this->info("Answered flashcards: {$answeredFlashcards} ({$answeredPercentage}%)"); 
        $this->info("Correctly answered flashcards: {$correctlyAnsweredFlashcards} ({$correctPercentage}%)");
    }

    private function resetProgress()
    {
        $confirmed = $this->confirm('Are you sure you want to reset all progress? This action cannot be undone.'); 
        
        if (!$confirmed) 
        { 
            $this->info('Reset canceled.'); 
            return; 
        }

        Flashcard::query()->update(["user_answer" => null]);
        $this->info("Practice progress has been reset for all flashcards.");
    }
}
