<?php

namespace Tests\Feature;

use App\Enums\BenefitType;
use App\Enums\FundingType;
use App\Enums\ProgramStatus;
use App\Livewire\Distribution\Workspace;
use App\Models\AyudaProgram;
use App\Models\FundingSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DistributionProjectPickerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected FundingSource $funding;

    protected AyudaProgram $program1;

    protected AyudaProgram $program2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'Admin']);

        $this->funding = FundingSource::create([
            'funding_type' => FundingType::Government,
            'title' => 'Municipal Calamity Fund',
            'source_code' => 'MCF-2026-001',
            'allocated_amount' => 1000000.00,
            'spent_amount' => 0.00,
            'remaining_balance' => 1000000.00,
        ]);

        $this->program1 = AyudaProgram::create([
            'funding_source_id' => $this->funding->id,
            'program_code' => 'AMS-PD-000101',
            'title' => 'Emergency Rice Distribution',
            'benefit_type' => BenefitType::Goods,
            'budget_cap' => 200000.00,
            'item_name' => 'Sack of Rice (25kg)',
            'item_unit' => 'Sack',
            'item_quantity_per_beneficiary' => 1,
            'target_beneficiaries' => 100,
            'status' => ProgramStatus::Active,
        ]);

        $this->program2 = AyudaProgram::create([
            'funding_source_id' => $this->funding->id,
            'program_code' => 'AMS-PD-000202',
            'title' => 'Senior Citizen Medical Aid',
            'benefit_type' => BenefitType::Cash,
            'budget_cap' => 150000.00,
            'unit_amount' => 3000.00,
            'target_beneficiaries' => 50,
            'status' => ProgramStatus::Active,
        ]);
    }

    /**
     * Requirement 2 & 13: Initial project-first workflow without automatic first-project selection.
     */
    public function test_no_automatic_first_project_selection_on_mount(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->assertSet('selectedProjectId', null)
            ->assertSet('showProjectPickerModal', true)
            ->assertSee('No Active Project Selected');
    }

    /**
     * Requirement 1 & 13: Automatic picker opening only when no project is selected.
     */
    public function test_automatic_picker_opening_only_when_no_project_is_selected(): void
    {
        // When no project is selected, modal opens automatically on mount
        $test = Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->assertSet('showProjectPickerModal', true);

        // Selecting a project closes the picker modal
        $test->call('selectProject', $this->program1->id)
            ->assertSet('selectedProjectId', $this->program1->id)
            ->assertSet('showProjectPickerModal', false);

        // Explicitly calling openProjectPicker reopens the modal and resets search
        $test->set('projectPickerSearch', 'Some query')
            ->call('openProjectPicker')
            ->assertSet('showProjectPickerModal', true)
            ->assertSet('projectPickerSearch', '')
            ->assertSet('projectPickerHighlightedId', $this->program1->id);
    }

    /**
     * Requirement 4, 5 & 13: Picker bindings and case-insensitive search by name and code.
     */
    public function test_picker_bindings_and_case_insensitive_search(): void
    {
        $test = Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->assertViewHas('pickerPrograms', function ($programs) {
                return $programs->count() === 2;
            });

        // Search by title case-insensitively (lowercase "rice")
        $test->set('projectPickerSearch', 'rice')
            ->assertViewHas('pickerPrograms', function ($programs) {
                return $programs->count() === 1
                    && $programs->first()->program_code === 'AMS-PD-000101';
            });

        // Search by title case-insensitively (uppercase "MEDICAL")
        $test->set('projectPickerSearch', 'MEDICAL')
            ->assertViewHas('pickerPrograms', function ($programs) {
                return $programs->count() === 1
                    && $programs->first()->program_code === 'AMS-PD-000202';
            });

        // Search by program code case-insensitively (lowercase "000101")
        $test->set('projectPickerSearch', '000101')
            ->assertViewHas('pickerPrograms', function ($programs) {
                return $programs->count() === 1
                    && $programs->first()->title === 'Emergency Rice Distribution';
            });

        // Search with non-matching term returns empty list
        $test->set('projectPickerSearch', 'NON_EXISTENT_PROGRAM')
            ->assertViewHas('pickerPrograms', function ($programs) {
                return $programs->isEmpty();
            });
    }

    /**
     * Requirement 3 & 13: Selected project is preserved during refreshes.
     */
    public function test_selection_persistence_after_refresh(): void
    {
        $test = Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->call('selectProject', $this->program2->id)
            ->assertSet('selectedProjectId', $this->program2->id);

        // Trigger a Livewire re-render / filter refresh
        $test->set('pendingSearch', 'Dela Cruz')
            ->assertSet('selectedProjectId', $this->program2->id)
            ->assertViewHas('currentProject', function ($current) {
                return $current !== null && $current->id === $this->program2->id;
            });
    }

    /**
     * Requirement 6 & 13: Disabled selection when no row is highlighted/selected.
     */
    public function test_disabled_selection_with_no_selected_row(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->assertSet('selectedProjectId', null)
            ->assertSet('projectPickerHighlightedId', null)
            // Attempt confirming with null highlighted ID
            ->call('confirmProjectSelection')
            ->assertSet('selectedProjectId', null)
            ->assertSet('showProjectPickerModal', true);
    }

    /**
     * Requirement 7 & 13: Selecting by highlight followed by confirm, and by direct select.
     */
    public function test_selecting_by_highlight_and_confirm_or_direct_selection(): void
    {
        // 1. Single click to highlight, then confirm
        Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->call('highlightProject', $this->program1->id)
            ->assertSet('projectPickerHighlightedId', $this->program1->id)
            ->call('confirmProjectSelection')
            ->assertSet('selectedProjectId', $this->program1->id)
            ->assertSet('showProjectPickerModal', false);

        // 2. Direct selection (double click behavior)
        Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->call('selectProject', $this->program2->id)
            ->assertSet('selectedProjectId', $this->program2->id)
            ->assertSet('showProjectPickerModal', false);
    }

    /**
     * Requirement 8 & 13: Cancel preserving the existing selected project.
     */
    public function test_cancel_preserving_existing_project(): void
    {
        $test = Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->call('selectProject', $this->program1->id)
            ->assertSet('selectedProjectId', $this->program1->id);

        // Open picker to switch project, then cancel
        $test->call('openProjectPicker')
            ->assertSet('showProjectPickerModal', true)
            ->call('highlightProject', $this->program2->id)
            ->call('closeProjectPicker')
            ->assertSet('showProjectPickerModal', false)
            ->assertSet('selectedProjectId', $this->program1->id); // Remains program1
    }
}
