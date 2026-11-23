<?php

namespace Tests\Unit;

use App\Support\EmployeeDailyExpenseCalculator;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class EmployeeDailyExpenseCalculatorTest extends TestCase
{
    public function test_it_calculates_salary_expense_for_the_selected_month_length(): void
    {
        $salary = 15000;

        $this->assertSame(
            500.0,
            EmployeeDailyExpenseCalculator::dailySalary($salary, Carbon::create(2026, 4, 1))
        );
        $this->assertSame(
            483.87,
            EmployeeDailyExpenseCalculator::dailySalary($salary, Carbon::create(2026, 1, 1))
        );
        $this->assertSame(
            535.71,
            EmployeeDailyExpenseCalculator::dailySalary($salary, Carbon::create(2026, 2, 1))
        );
        $this->assertSame(
            517.24,
            EmployeeDailyExpenseCalculator::dailySalary($salary, Carbon::create(2024, 2, 1))
        );
    }

    public function test_daily_revenue_target_includes_salary_cost_and_daily_ad_spend(): void
    {
        $this->assertSame(
            1322.58,
            EmployeeDailyExpenseCalculator::dailyRevenueTarget(
                10000,
                Carbon::create(2026, 10, 30),
                1000
            )
        );
    }

    public function test_combined_daily_expense_counts_the_shared_expense_once(): void
    {
        $month = Carbon::create(2026, 4, 1);

        $this->assertSame(
            1100.0,
            EmployeeDailyExpenseCalculator::totalCombinedDailyExpense([15000], $month)
        );
        $this->assertSame(
            1600.0,
            EmployeeDailyExpenseCalculator::totalCombinedDailyExpense([15000, 15000], $month)
        );
    }

    public function test_missing_salary_is_treated_as_zero_without_affecting_shared_expense(): void
    {
        $month = Carbon::create(2026, 4, 1);

        $this->assertSame(0.0, EmployeeDailyExpenseCalculator::dailySalary(null, $month));
        $this->assertSame(
            600.0,
            EmployeeDailyExpenseCalculator::totalCombinedDailyExpense([null], $month)
        );
    }

    public function test_remaining_meta_ads_budget_excludes_salary_expenses(): void
    {
        $this->assertSame(
            8000.0,
            EmployeeDailyExpenseCalculator::remainingBudget(20000, 12000)
        );
    }
}
