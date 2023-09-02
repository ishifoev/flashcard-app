<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Flashcard;
use Illuminate\Support\Facades\DB;

class FlashcardInteractiveCommandTest extends TestCase
{
    use RefreshDatabase;
 
    /** @test */
    public function it_can_create_a_flashcard()
    {
        // Simulate user input and command execution
        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Select an option:', 'Create')
            ->expectsQuestion('Enter the flashcard question', 'What is 2 + 2?')
            ->expectsQuestion('Enter the flashcard answer', '4')
            ->expectsOutput('Flashcard created successfully.')
            ->expectsQuestion('Select an option:', 'Exit') // To exit the loop
            ->assertExitCode(0);
 
        // Assert that a flashcard was created and exists in the database
        $this->assertCount(1, Flashcard::all());
        $this->assertDatabaseHas('flashcards', [
            'question' => 'What is 2 + 2?',
            'answer' => '4',
        ]);
    }

    public function it_handles_database_error_during_flashcard_creation()
    {
        // Mock a database error during flashcard creation
        DB::shouldReceive('table')->once()->andReturnUsing(function () {
            throw new \Exception('Database error');
        });
 
        // Simulate user input and command execution
        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Select an option:', 'Create')
            ->expectsQuestion('Enter the flashcard question', 'What is 2 + 2?')
            ->expectsQuestion('Enter the flashcard answer', '4')
            ->expectsOutput('Failed to create the flashcard due to a database error.')
            ->expectsQuestion('Select an option:', 'Exit') // To exit the loop
            ->assertExitCode(0);
 
        // Ensure that no flashcard was created
        $this->assertCount(0, Flashcard::all());
    }

      /** @test */
    public function it_cannot_create_a_flashcard_with_invalid_data()
    {
          // Simulate user input with invalid data
        $this->artisan('flashcard:interactive')
              ->expectsQuestion('Select an option:', 'Create')
              ->expectsQuestion('Enter the flashcard question', '') // Invalid: Empty question
              ->expectsQuestion('Enter the flashcard answer', '')
              ->expectsOutput('Validation failed:')
              ->expectsQuestion('Select an option:', 'Exit') // To exit the loop
              ->assertExitCode(0);
   
          // Assert that no flashcard was created
          $this->assertCount(0, Flashcard::all());
    }

    /** @test */
    public function it_can_reset_progress()
    {
        // Create a few flashcards with user answers in the database
        Flashcard::factory()->count(3)->create(['user_answer' => 'Some answer']);
    
        // Simulate user input and command execution
        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Select an option:', 'Reset')
            ->expectsQuestion('Are you sure you want to reset all progress? This action cannot be undone.', 'yes')
            ->expectsOutput('Practice progress has been reset for all flashcards.')
            ->expectsQuestion('Select an option:', 'Exit') // To exit the loop
            ->assertExitCode(0);
    
        // Assert that user answers have been reset for all flashcards
        $this->assertEquals(0, Flashcard::whereNotNull('user_answer')->count());
    }

      /** @test */
      public function it_can_handle_practice_session_with_correct_answers()
      {
          // Create three flashcards in the database with different questions and answers
          $flashcards = Flashcard::factory()->count(3)->create();
   
          // Simulate user input to practice flashcards with correct answers
          $this->artisan('flashcard:interactive')
              ->expectsQuestion('Select an option:', 'Practice')
              ->expectsQuestion('Q: ' . $flashcards[0]->question, $flashcards[0]->answer)
              ->expectsQuestion('Q: ' . $flashcards[1]->question, $flashcards[1]->answer)
              ->expectsQuestion('Q: ' . $flashcards[2]->question, $flashcards[2]->answer)
              ->expectsQuestion('Select an option:', 'Exit') // To exit the loop
              ->assertExitCode(0);
   
          // Assert that practice session completion is 100% since all answers are correct
          $this->assertDatabaseHas('flashcards', ['user_answer' => $flashcards[0]->answer]);
          $this->assertDatabaseHas('flashcards', ['user_answer' => $flashcards[1]->answer]);
          $this->assertDatabaseHas('flashcards', ['user_answer' => $flashcards[2]->answer]);
      }
   
      /** @test */
    public function it_can_handle_practice_session_with_incorrect_answers()
    {
          // Create three flashcards in the database with different questions and answers
        $flashcards = Flashcard::factory()->count(3)->create();
   
          // Simulate user input to practice flashcards with incorrect answers
        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Select an option:', 'Practice')
            ->expectsQuestion('Q: ' . $flashcards[0]->question, 'incorrect1')
            ->expectsQuestion('Q: ' . $flashcards[1]->question, $flashcards[1]->answer)
            ->expectsQuestion('Q: ' . $flashcards[2]->question, 'incorrect2')
            ->expectsQuestion('Select an option:', 'Exit') // To exit the loop
            ->assertExitCode(0);
   
          // Assert that practice session completion is less than 100%
        $this->assertDatabaseMissing('flashcards', ['user_answer' => $flashcards[0]->answer]);
        $this->assertDatabaseHas('flashcards', ['user_answer' => $flashcards[1]->answer]);
        $this->assertDatabaseMissing('flashcards', ['user_answer' => $flashcards[2]->answer]);
  }

  /** @test */
  public function it_displays_error_message_for_empty_question_and_answer()
  {
      // Simulate user input with empty question and answer
      $this->artisan('flashcard:interactive')
          ->expectsQuestion('Select an option:', 'Create')
          ->expectsQuestion('Enter the flashcard question', '') // Empty question
          ->expectsQuestion('Enter the flashcard answer', '')   // Empty answer
          ->expectsOutput('Validation failed:')
          ->expectsOutput('The question field is required.')
          ->expectsOutput('The answer field is required.')
          ->expectsQuestion('Select an option:', 'Exit') // To exit the loop
          ->assertExitCode(0);

      // Assert that no flashcard was created
      $this->assertCount(0, Flashcard::all());
  }

  /** @test */
  public function it_displays_error_message_for_empty_user_answer_during_practice()
  {
      // Create a flashcard in the database
      $flashcard = Flashcard::factory()->create();

      // Simulate user input with an empty user answer during practice
      $this->artisan('flashcard:interactive')
          ->expectsQuestion('Select an option:', 'Practice')
          ->expectsQuestion('Q: ' . $flashcard->question, '') // Empty user answer
          ->expectsOutput('Answer cannot be empty.')
          ->expectsQuestion('Select an option:', 'Exit') // To exit the loop
          ->assertExitCode(0);

      // Assert that the flashcard's user answer was not updated
      $this->assertDatabaseHas('flashcards', [
          'id' => $flashcard->id,
          'user_answer' => null,
      ]);
  }
     /** @test */
    public function it_can_list_flashcards()
    {
        // Create a few flashcards in the database
        Flashcard::factory()->count(1)->create();
        $flashcards = Flashcard::all();

        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Select an option:', 'List')
            ->expectsTable([
                'ID',
                'Question',
                "Answer",
                "Created At",
                "Updated At"
            ], [
                [$flashcards[0]->id, $flashcards[0]->question, $flashcards[0]->answer, $flashcards[0]->created_at->format('Y-m-d H:i:s'),
                 $flashcards[0]->updated_at->format('Y-m-d H:i:s')],
            ])
            ->expectsQuestion('Select an option:', 'Exit')
            ->assertExitCode(0);
            ;
    }

     /** @test */
     public function it_can_display_stats()
     {
         // Create a few flashcards in the database
         $totalFlashcards = 10;
         Flashcard::factory()->count($totalFlashcards)->create();
  
         // Set user answers for some of the flashcards
         $answeredFlashcards = 7;
         Flashcard::inRandomOrder()->limit($answeredFlashcards)->update(['user_answer' => 'Some answer']);
  
         // Calculate expected percentages
         $answeredPercentage = ($answeredFlashcards / $totalFlashcards) * 100;
  
         // Count correctly answered flashcards
         $correctlyAnsweredFlashcards = Flashcard::where('user_answer', Flashcard::raw('answer'))->count();
         $correctPercentage = ($correctlyAnsweredFlashcards / $answeredFlashcards) * 100;
  
         // Simulate user input and command execution
         $this->artisan('flashcard:interactive')
             ->expectsQuestion('Select an option:', 'Stats')
             ->expectsOutput("Total flashcards: $totalFlashcards")
             ->expectsOutput("Answered flashcards: $answeredFlashcards ($answeredPercentage%)")
             ->expectsOutput("Correctly answered flashcards: $correctlyAnsweredFlashcards ($correctPercentage%)")
             ->expectsQuestion('Select an option:', 'Exit') // To exit the loop
             ->assertExitCode(0);
     }

    /** @test */
    public function it_handles_invalid_menu_choice()
    {
        $this->artisan('flashcard:interactive')
            ->expectsOutput('Welcome to Flashcard Interactive!')
            ->expectsQuestion('Select an option:', 'InvalidChoice')
            ->expectsOutput('Invalid action selected')
            ->expectsQuestion('Select an option:', 'Exit')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_can_exit_the_program()
    {
        $this->artisan('flashcard:interactive')
            ->expectsOutput('Welcome to Flashcard Interactive!')
            ->expectsQuestion('Select an option:', 'Exit')
            ->expectsOutput('Exiting Flashcard Interactive')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_handles_unexpected_input_during_practice()
    {
        // Create a flashcard in the database
        $flashcard = Flashcard::factory()->create();
    
        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Select an option:', 'Practice')
            ->expectsQuestion('Q: ' . $flashcard->question, 'UnexpectedInput')
            ->expectsOutput('Incorrect!')
            ->expectsQuestion('Select an option:', 'Exit')
            ->assertExitCode(0);
    }
}