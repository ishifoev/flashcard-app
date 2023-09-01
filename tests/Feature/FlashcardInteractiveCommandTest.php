<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Flashcard;
 
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

      
}