<?php

namespace App\Console\Commands;

use App\Models\Flashcard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
            if($action === "Exit") {
                $this->info("Exiting Flashcard Interactive");
                break;
            }
            //Handle different actions
            $method = 'handle' . Str::studly($action);
            if(method_exists($this, $method)) {
                $this->$method();
            } else{
               $this->line("Invalid action selected");
            }
          }
    }

    private function handleCreate()
    {
        $flashcardData = $this->getFlashcardData();
    
        if(!$flashcardData) {
            return;
        }

        try {
            Flashcard::create($flashcardData);
            $this->info("Flashcard created successfully.");
        } catch(\Exception $e) {
            $this->error("Failed to create the flashcard due to a database error");
        }
    }
    
    private function getFlashcardData()
    {
        $question = $this->ask("Enter the flashcard question");
        $answer = $this->ask("Enter the flashcard answer");

        $validator = $this->validateFlashcardData($question, $answer);

        if($validator->fails()) {
            $this->error("Validation failed:");
            foreach($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return;
        }

        return [
            'question' => $question,
            'answer' => $answer
        ];
    }
    
    private function validateFlashcardData($question, $answer)
    {
        return Validator::make([
            "question" => $question,
            "answer" => $answer
        ],
        [
            "question" => ["required", "string", "max:255", "min:3"], // Example: Minimum length of 5 characters 
            "answer" => ["required", "string", "max:255", "min:1"], // Example: Minimum length of 2 characters
        ]
        );
    }

    private function handleList() 
    {
        $flashcards = $this->getFlashcards(); 
        
        $tableData = $flashcards->map(function ($flashcard) { 
            return [ 
                'ID' => $flashcard->id,
                'Question' => $flashcard->question, 
                'Answer' => $flashcard->answer, 
                'Created At' => $flashcard->created_at->format('Y-m-d H:i:s'),
                'Updated At' => $flashcard->updated_at->format('Y-m-d H:i:s'), ];
             }); 
        
        $this->table(["ID", "Question", "Answer", "Created At", "Updated At"], $tableData);
    }

    private function handlePractice() 
    {
        $flashcards = $this->getFlashcards();
        $totalFlashCards = count($flashcards);

        $correctlyAnswered = 0;

        foreach($flashcards as $flashcard) {
           $this->practiceSingleFlashcard($flashcard, $correctlyAnswered);
        }

        $completionPercentage = ($totalFlashCards > 0) ? round(($correctlyAnswered / $totalFlashCards) * 100, 2) : 0;
         $this->info("Practice session complete. Completion: {$completionPercentage}%");
    }

    private function practiceSingleFlashcard($flashcard, &$correctlyAnswered) 
    { 
        if ($flashcard->user_answer === $flashcard->answer) { 
            $this->error("You have already answered this question correctly"); 
            return; 
        } 
        $userAnswer = $this->ask("Q: {$flashcard->question}"); 
        
        if (empty(trim($userAnswer))) 
        { 
            $this->error("Answer cannot be empty."); 
            return; 
        }
        
        if (strtolower($userAnswer) === strtolower($flashcard->answer)) { 
            $this->info("Correct!"); $correctlyAnswered++; 
            
            $this->updateFlashcardUserAnswer($flashcard, $userAnswer); 
        } else { 
            $this->error("Incorrect!"); 
        } 
    } 
    
    private function updateFlashcardUserAnswer($flashcard, $userAnswer) { 
        $flashcard->update(["user_answer" => $userAnswer]);
     }

    private function handleStats()
    {
        $totalFlashcards = count($this->getFlashcards());
        $answeredFlashcards = $this->getCompletedFlashcardsCount();
        $correctlyAnsweredFlashcards = $this->getCorrectlyAnsweredFlashcardsCount();
        
        $answeredPercentage = $totalFlashcards > 0 ? round(($answeredFlashcards / $totalFlashcards) * 100, 2) : 0; 
        $correctPercentage = $answeredFlashcards > 0 ? round(($correctlyAnsweredFlashcards / $answeredFlashcards) * 100, 2) : 0; 
        
        $this->info("Total flashcards: {$totalFlashcards}"); 
        $this->info("Answered flashcards: {$answeredFlashcards} ({$answeredPercentage}%)"); 
        $this->info("Correctly answered flashcards: {$correctlyAnsweredFlashcards} ({$correctPercentage}%)");
    }

    private function handleReset()
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

    private function getFlashcards() 
    { 
        return Flashcard::all(); 
    } 
    
    private function getCompletedFlashcardsCount() 
    { 
        return Flashcard::whereNotNull('user_answer')->count(); 
    } 
    
    private function getCorrectlyAnsweredFlashcardsCount() 
    {
         return Flashcard::where('user_answer', Flashcard::raw('answer'))->count(); 
    }
}
