<?php

namespace App\Livewire\Masterlist;

use App\Models\Beneficiary;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Masterlist - eKalinga+')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $selectedBarangay = '';

    public ?string $connectionError = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedBarangay(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->connectionError = null;
        $beneficiaries = collect();
        $barangays = [];

        try {
            $query = Beneficiary::query();

            // Filter out soft-deleted records if column exists
            try {
                $query->where(function ($q) {
                    $q->where('IsDeleted', 0)->orWhereNull('IsDeleted');
                });
            } catch (\Throwable) {
            }

            if ($this->search) {
                $term = trim($this->search);
                $query->where(function ($q) use ($term) {
                    $q->where('full_name', 'like', "%{$term}%")
                        ->orWhere('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('beneficiary_id', 'like', "%{$term}%")
                        ->orWhere('civilregistry_id', 'like', "%{$term}%")
                        ->orWhere('address', 'like', "%{$term}%");
                });
            }

            if ($this->selectedBarangay) {
                $query->where('address', 'like', "%{$this->selectedBarangay}%");
            }

            $beneficiaries = $query->paginate(25);

            // Fetch barangays from CRS database
            try {
                $barangays = DB::connection('crs')
                    ->table('barangays')
                    ->orderBy('name')
                    ->pluck('name')
                    ->toArray();
            } catch (\Throwable) {
                $barangays = [
                    'Balasinon', 'Buguis', 'Carre', 'Clib', 'Harada Yano', 'Ibo', 'Inayagan',
                    'Kiblagon', 'Labon', 'Lapediche', 'Luparan', 'Mckinley', 'New Cebu',
                    'Osmeña', 'Palili', 'Parame', 'Poblacion', 'Roxas', 'Solongvale',
                    'Tagolilong', 'Tala-o', 'Talas', 'Tanwalang', 'Waterfall',
                ];
            }
        } catch (\Throwable $e) {
            $this->connectionError = 'Unable to connect to live CRS database ('.config('database.connections.crs.host').':3306). Reason: '.$e->getMessage();
            $beneficiaries = new LengthAwarePaginator([], 0, 25);
        }

        return view('livewire.masterlist.index', [
            'beneficiaries' => $beneficiaries,
            'barangays' => $barangays,
            'connectionError' => $this->connectionError,
        ]);
    }
}
