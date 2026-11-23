<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\MetaAdReport;
use App\Models\User;
use App\Support\EmployeeDailyExpenseCalculator;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MetaAdsReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.meta-ads-report';

    protected static ?string $title = 'Meta Ads Report';

    protected static ?string $navigationLabel = 'Meta Ads Report';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedChartBar;

    protected static ?int $navigationSort = 10;

    /*
    |--------------------------------------------------------------------------
    | Meta Ads Table
    |--------------------------------------------------------------------------
    */

    public function table(Table $table): Table
    {
        $campaignReports = MetaAdReport::query()
            ->selectRaw('
                MIN(id) as id,
                report_date,
                agent_id,
                campaign_id,
                campaign_name,
                MAX(budget) as budget,
                SUM(amount_spent) as amount_spent
            ')
            ->groupBy('report_date', 'agent_id', 'campaign_id', 'campaign_name');

        return $table
            ->query(
                MetaAdReport::query()
                    ->fromSub($campaignReports, 'meta_ad_reports')
                    ->selectRaw('
                        MIN(meta_ad_reports.id) as id,
                        meta_ad_reports.report_date,
                        meta_ad_reports.agent_id,
                        COUNT(*) as campaign_count,
                        SUM(meta_ad_reports.budget) as total_budget,
                        SUM(meta_ad_reports.amount_spent) as total_amount_spent
                    ')
                    ->selectSub(
                        Customer::query()
                            ->selectRaw('COALESCE(SUM(customers.price), 0)')
                            ->whereColumn('customers.agent_id', 'meta_ad_reports.agent_id')
                            ->whereColumn('customers.booking_date', 'meta_ad_reports.report_date'),
                        'total_revenue'
                    )
                    ->selectSub(
                        MetaAdReport::query()
                            ->from('meta_ad_reports as daily_reports')
                            ->selectRaw('COALESCE(SUM(daily_reports.amount_spent), 0)')
                            ->whereColumn('daily_reports.report_date', 'meta_ad_reports.report_date'),
                        'daily_total_spend'
                    )
                    ->with('agent')
                    ->groupBy('meta_ad_reports.report_date', 'meta_ad_reports.agent_id')
            )

            ->columns([
                TextColumn::make('row_number')
                    ->label('#')
                    ->rowIndex(),

                TextColumn::make('report_date')
                    ->label('Date')
                    ->date('d M Y'),

                TextColumn::make('agent.name')
                    ->label('Agent Name'),

                TextColumn::make('campaign_count')
                    ->label('Campaigns'),

                TextColumn::make('total_revenue')
                    ->label('Agent Revenue')
                    ->money('INR')
                    ->summarize(
                        Sum::make()
                            ->label('Total Agent Revenue')
                            ->money('INR')
                            ->extraAttributes(['style' => 'font-size: 1.125rem'])
                    ),

                TextColumn::make('total_budget')
                    ->label('Total Budget')
                    ->money('INR')
                    ->summarize(
                        Sum::make()
                            ->label('Total Budget')
                            ->money('INR')
                            ->extraAttributes(['style' => 'font-size: 1.125rem'])
                    ),

                TextColumn::make('total_amount_spent')
                    ->label('Total Amount Spent')
                    ->money('INR')
                    ->summarize(
                        Sum::make()
                            ->label('Total Amount Spent')
                            ->money('INR')
                            ->extraAttributes(['style' => 'font-size: 1.125rem'])
                    ),

                TextColumn::make('salary_per_day')
                    ->label('Salary / Day')
                    ->state(fn ($record): float => EmployeeDailyExpenseCalculator::dailySalary(
                        $record->agent?->salary,
                        Carbon::parse($record->report_date)
                    ))
                    ->money('INR')
                    ->summarize(
                        Summarizer::make()
                            ->label('Total Salary / Day')
                            ->using(function ($query): string {
                                $rows = $query->get(['agent_id', 'report_date']);
                                $salaries = User::query()
                                    ->whereIn('id', $rows->pluck('agent_id')->unique())
                                    ->pluck('salary', 'id');
                                $totalDailySalary = $rows->sum(
                                    fn ($row): float => EmployeeDailyExpenseCalculator::dailySalary(
                                        $salaries->get($row->agent_id),
                                        Carbon::parse($row->report_date)
                                    )
                                );

                                return '₹'.number_format($totalDailySalary, 2);
                            })
                            ->extraAttributes(['style' => 'font-size: 1.125rem'])
                    ),

                TextColumn::make('spend_share')
                    ->label('Spend Share')
                    ->state(fn ($record): string => (float) $record->daily_total_spend > 0
                        ? number_format(
                            (float) $record->total_amount_spent / (float) $record->daily_total_spend * 100,
                            2
                        ).'%'
                        : '0.00%')
                    ->summarize(
                        Summarizer::make()
                            ->label('Total Spend Share')
                            ->using(function ($query): string {
                                $totalSpendShare = $query
                                    ->get(['total_amount_spent', 'daily_total_spend'])
                                    ->sum(fn ($row): float => (float) $row->daily_total_spend > 0
                                        ? (float) $row->total_amount_spent / (float) $row->daily_total_spend * 100
                                        : 0);

                                return number_format($totalSpendShare, 2).'%';
                            })
                            ->extraAttributes(['style' => 'font-size: 1.125rem'])
                    ),

                TextColumn::make('roas')
                    ->label('ROAS')
                    ->state(fn ($record): string => (float) $record->total_amount_spent > 0
                        && (float) $record->total_revenue > 0
                        ? number_format(
                            (float) $record->total_revenue / (float) $record->total_amount_spent,
                            2
                        ).'x'
                        : '—'),

                TextColumn::make('marketing_percentage')
                    ->label('Marketing %')
                    ->state(fn ($record): string => (float) $record->total_amount_spent > 0
                        && (float) $record->total_revenue > 0
                        ? number_format(
                            (float) $record->total_amount_spent / (float) $record->total_revenue * 100,
                            2
                        ).'%'
                        : '—'),

                TextColumn::make('spend_vs_salary')
                    ->label('Spend - Daily Salary')
                    ->description(fn ($record): string => '₹'.
                        number_format((float) $record->total_amount_spent, 2).
                        ' ad spend - ₹'.
                        number_format(
                            EmployeeDailyExpenseCalculator::dailySalary(
                                $record->agent?->salary,
                                Carbon::parse($record->report_date)
                            ),
                            2
                        ).
                        ' salary/day')
                    ->state(function ($record): string {
                        $dailySalary = EmployeeDailyExpenseCalculator::dailySalary(
                            $record->agent?->salary,
                            Carbon::parse($record->report_date)
                        );
                        $difference = (float) $record->total_amount_spent - $dailySalary;

                        return ($difference < 0 ? '-₹' : '₹').number_format(abs($difference), 2);
                    })
                    ->summarize(
                        Summarizer::make()
                            ->label('Overall Spend - Daily Salary')
                            ->using(function ($query): string {
                                $rows = $query->get(['agent_id', 'report_date', 'total_amount_spent']);
                                $salaries = User::query()
                                    ->whereIn('id', $rows->pluck('agent_id')->unique())
                                    ->pluck('salary', 'id');
                                $totalDailySalary = $rows->sum(
                                    fn ($row): float => EmployeeDailyExpenseCalculator::dailySalary(
                                        $salaries->get($row->agent_id),
                                        Carbon::parse($row->report_date)
                                    )
                                );
                                $difference = (float) $rows->sum('total_amount_spent') - $totalDailySalary;

                                return ($difference < 0 ? '-₹' : '₹').number_format(abs($difference), 2);
                            })
                            ->extraAttributes(['style' => 'font-size: 1.125rem'])
                    ),
            ])

            ->recordActions([
                Action::make('viewCampaigns')
                    ->label('View Campaigns')
                    ->icon(Heroicon::OutlinedEye)
                    ->modalHeading(
                        fn ($record) => 'Campaigns — '.
                            $record->agent?->name.' — '.
                            Carbon::parse($record->report_date)->format('d M Y')
                    )
                    ->modalContent(function ($record) {
                        $campaigns = MetaAdReport::query()
                            ->selectRaw('
                                MIN(id) as id,
                                agent_id,
                                campaign_id,
                                campaign_name,
                                MAX(budget) as budget,
                                MAX(budget_type) as budget_type,
                                SUM(amount_spent) as amount_spent
                            ')
                            ->where('agent_id', $record->agent_id)
                            ->whereDate('report_date', $record->report_date)
                            ->groupBy('agent_id', 'campaign_id', 'campaign_name')
                            ->orderBy('campaign_name')
                            ->with('agent')
                            ->get();

                        $agentRevenue = Customer::query()
                            ->where('agent_id', $record->agent_id)
                            ->whereDate('booking_date', $record->report_date)
                            ->sum('price');

                        $reportDate = Carbon::parse($record->report_date);
                        $dailySalary = EmployeeDailyExpenseCalculator::dailySalary(
                            $record->agent?->salary,
                            $reportDate
                        );
                        $dailySpend = (float) $campaigns->sum('amount_spent');
                        $dailyTotalSpend = (float) MetaAdReport::query()
                            ->whereDate('report_date', $record->report_date)
                            ->sum('amount_spent');
                        $spendShare = $dailyTotalSpend > 0
                            ? $dailySpend / $dailyTotalSpend * 100
                            : 0;

                        return view(
                            'filament.pages.meta-ads-campaign-details',
                            [
                                'campaigns' => $campaigns,
                                'agentRevenue' => $agentRevenue,
                                'dailySalary' => $dailySalary,
                                'dailySpend' => $dailySpend,
                                'spendShare' => $spendShare,
                                'spendVsSalary' => $dailySalary > 0
                                    ? $dailySpend / $dailySalary
                                    : null,
                            ]
                        );
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])

            /*
                        |--------------------------------------------------------------------------
                        | Date Filter
                        |--------------------------------------------------------------------------
                        */

            ->filters([

                Filter::make('report_date')
                    ->label('Date Range')
                    ->schema([
                        Select::make('period')
                            ->label('Period')
                            ->placeholder('Select a period')
                            ->options([
                                'today' => 'Today',
                                'yesterday' => 'Yesterday',
                                'this_week' => 'This week',
                                'last_7_days' => 'Last 7 days',
                                'last_15_days' => 'Last 15 days',
                                'this_month' => 'This month',
                                'last_30_days' => 'Last 30 days',
                                'last_month' => 'Last month',
                                'this_quarter' => 'This quarter',
                                'this_year' => 'This year',
                            ])
                            ->live()
                            ->columnSpanFull()
                            ->afterStateUpdated(function (?string $state, callable $set): void {
                                [$startDate, $endDate] = self::getDateRangeForPeriod($state);

                                $set('start_date', $startDate?->toDateString());
                                $set('end_date', $endDate?->toDateString());
                            }),

                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->native(false)
                            ->maxDate(today())
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('period', null)),

                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->native(false)
                            ->minDate(fn (callable $get) => $get('start_date'))
                            ->maxDate(today())
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('period', null)),
                    ])
                    ->columns(2)
                    ->query(
                        function (Builder $query, array $data): Builder {
                            [$startDate, $endDate] = filled($data['period'] ?? null)
                                ? self::getDateRangeForPeriod($data['period'])
                                : [$data['start_date'] ?? null, $data['end_date'] ?? null];

                            return $query
                                ->when(
                                    $startDate,
                                    fn (Builder $query, $date): Builder => $query->whereDate('report_date', '>=', $date)
                                )
                                ->when(
                                    $endDate,
                                    fn (Builder $query, $date): Builder => $query->whereDate('report_date', '<=', $date)
                                );
                        }
                    ),

                SelectFilter::make('agent_id')
                    ->label('Agent')
                    ->relationship(
                        name: 'agent',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query
                            ->role('Agent')
                            ->where('is_active', true)
                            ->where('name', '!=', 'Amazon')
                            ->orderBy('name')
                    )
                    ->searchable()
                    ->preload()
                    ->placeholder('All agents'),

            ])

            /*
            |--------------------------------------------------------------------------
            | Sorting
            |--------------------------------------------------------------------------
            */

            ->defaultSort(
                'report_date',
                'desc'
            )

            /*
            |--------------------------------------------------------------------------
            | Pagination
            |--------------------------------------------------------------------------
            */

            ->paginated([
                25,
                50,
                100,
            ])

            ->defaultPaginationPageOption(25);
    }

    private static function getDateRangeForPeriod(?string $period): array
    {
        $today = Carbon::today();

        return match ($period) {
            'today' => [$today, $today],
            'yesterday' => [$today->copy()->subDay(), $today->copy()->subDay()],
            'this_week' => [$today->copy()->startOfWeek(Carbon::MONDAY), $today],
            'last_7_days' => [$today->copy()->subDays(6), $today],
            'last_15_days' => [$today->copy()->subDays(14), $today],
            'this_month' => [$today->copy()->startOfMonth(), $today],
            'last_30_days' => [$today->copy()->subDays(29), $today],
            'last_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->startOfMonth()->subDay()],
            'this_quarter' => [$today->copy()->firstOfQuarter(), $today],
            'this_year' => [$today->copy()->startOfYear(), $today],
            default => [null, null],
        };
    }
}
