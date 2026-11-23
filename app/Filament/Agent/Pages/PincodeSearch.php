<?php

namespace App\Filament\Agent\Pages;

use App\Models\Customer;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Livewire\Attributes\Locked;
use Livewire\WithPagination;

class PincodeSearch extends Page
{
    use WithPagination;

    protected static ?string $navigationLabel = 'Pincode Search';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?int $navigationSort = 999;

    protected string $view = 'filament.agent.pages.pincode-search';

    protected Width|string|null $maxContentWidth = Width::Full;

    public string $pincode = '';

    #[Locked]
    public ?string $searchedPincode = null;

    public ?string $searchError = null;

    public function search(): void
    {
        $validated = $this->validate([
            'pincode' => ['required', 'string', 'regex:/\A[0-9]{6}\z/'],
        ], [
            'pincode.required' => 'Enter a six-digit pincode.',
            'pincode.regex' => 'The pincode must contain exactly six digits.',
        ]);

        $this->searchedPincode = $validated['pincode'];
        $this->searchError = null;
        $this->resetPage();
    }

    public function getRecords(): ?LengthAwarePaginator
    {
        if ($this->searchedPincode === null) {
            return null;
        }

        try {
            return Customer::query()
                ->withoutGlobalScope('activeAgent')
                ->where('pincode', $this->searchedPincode)
                ->with([
                    'agent' => fn (BelongsTo $query) => $query->withTrashed(),
                ])
                ->orderByRaw('COALESCE(booking_date, created_at) DESC')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(15);
        } catch (QueryException $exception) {
            report($exception);
            $this->searchError = 'We could not load booking history. Please try again.';

            return null;
        }
    }

    public function getViewData(): array
    {
        return [
            'records' => $this->getRecords(),
        ];
    }
}
