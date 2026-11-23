<?php

namespace App\Support;

use Carbon\CarbonInterface;

final class EmployeeDailyExpenseCalculator
{
    public const OTHER_DAILY_EXPENSE = 600.0;

    public static function dailySalary(
        float|string|null $monthlySalary,
        CarbonInterface $month
    ): float {
        if (! is_numeric($monthlySalary)) {
            return 0.0;
        }

        return round((float) $monthlySalary / $month->daysInMonth, 2);
    }

    public static function dailyRevenueTarget(
        float|string|null $monthlySalary,
        CarbonInterface $date,
        float|string|null $dailyAdSpend
    ): float {
        $adSpend = is_numeric($dailyAdSpend) ? (float) $dailyAdSpend : 0.0;

        return round(self::dailySalary($monthlySalary, $date) + $adSpend, 2);
    }

    public static function totalDailySalaryExpense(
        iterable $monthlySalaries,
        CarbonInterface $month
    ): float {
        $totalMonthlySalary = 0.0;

        foreach ($monthlySalaries as $monthlySalary) {
            if (is_numeric($monthlySalary)) {
                $totalMonthlySalary += (float) $monthlySalary;
            }
        }

        return round($totalMonthlySalary / $month->daysInMonth, 2);
    }

    public static function totalCombinedDailyExpense(
        iterable $monthlySalaries,
        CarbonInterface $month
    ): float {
        return round(
            self::totalDailySalaryExpense($monthlySalaries, $month)
                + self::OTHER_DAILY_EXPENSE,
            2
        );
    }

    public static function remainingBudget(
        float|string|null $budget,
        float|string|null $amountSpent
    ): float {
        $totalBudget = is_numeric($budget) ? (float) $budget : 0.0;
        $totalSpent = is_numeric($amountSpent) ? (float) $amountSpent : 0.0;

        return round($totalBudget - $totalSpent, 2);
    }
}
