<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Support\EmployeeDailyExpenseCalculator;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class EmployeeDailyExpenseReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.employee-daily-expense-report';

    protected static ?string $title = 'Agent Daily Expenses';

    protected static ?string $navigationLabel = 'Employee Daily Expenses';

    protected static UnitEnum|string|null $navigationGroup = 'Reports';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedCalculator;

    protected static ?int $navigationSort = 11;

    public string $selectedMonth;

    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m');
    }

    public function updatedSelectedMonth(): void
    {
        if (! preg_match('/^([0-9]{4})-(0[1-9]|1[0-2])$/D', $this->selectedMonth, $matches)
            || ! checkdate((int) $matches[2], 1, (int) $matches[1])) {
            $this->selectedMonth = now()->format('Y-m');
            $this->addError('selectedMonth', 'Select a valid reporting month.');
            $this->resetTable();

            return;
        }

        $this->resetErrorBag('selectedMonth');
        $this->resetTable();
    }

    /**
     * @return Collection<int, User>
     */
    public function getAgents(): Collection
    {
        return User::query()
            ->role('Agent')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getDailySalary(User $agent): ?string
    {
        if ($agent->salary === null) {
            return null;
        }

        $month = Carbon::createFromFormat('!Y-m', $this->selectedMonth);

        return '₹'.number_format(
            EmployeeDailyExpenseCalculator::dailySalary($agent->salary, $month),
            2,
            '.',
            ','
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): array => $this->getTableRows())
            ->columns([
                TextColumn::make('row_number')
                    ->label('#'),
                TextColumn::make('agent')
                    ->label('Agent')
                    ->weight(fn (array $record): ?string => $record['is_shared_expense'] ? 'bold' : null),
                TextColumn::make('salary_per_day')
                    ->label('Salary Per Day')
                    ->placeholder('—'),
                TextColumn::make('budget_spend')
                    ->label('Budget Spend')
                    ->placeholder('—')
                    ->weight(fn (array $record): ?string => $record['is_shared_expense'] ? 'bold' : null),
            ])
            ->paginated(false);
    }

    /**
     * @return list<array<string, int|string|bool|null>>
     */
    private function getTableRows(): array
    {
        $rows = [[
            '__key' => 'shared-daily-budget',
            'row_number' => '',
            'agent' => 'Daily Budget Spend (Business-wide)',
            'salary_per_day' => null,
            'budget_spend' => '₹'.number_format(
                EmployeeDailyExpenseCalculator::OTHER_DAILY_EXPENSE,
                2,
                '.',
                ','
            ),
            'is_shared_expense' => true,
        ]];

        foreach ($this->getAgents() as $index => $agent) {
            $rows[] = [
                '__key' => 'agent-'.$agent->getKey(),
                'row_number' => $index + 1,
                'agent' => $agent->name,
                'salary_per_day' => $this->getDailySalary($agent),
                'budget_spend' => null,
                'is_shared_expense' => false,
            ];
        }

        $rows[] = [
            '__key' => 'shared-daily-budget',
            'row_number' => '',
            'agent' => 'Daily Budget Spend (Business-wide)',
            'salary_per_day' => null,
            'budget_spend' => '₹'.number_format(
                EmployeeDailyExpenseCalculator::OTHER_DAILY_EXPENSE,
                2,
                '.',
                ','
            ),
            'is_shared_expense' => true,
        ];

        return $rows;
    }
}
