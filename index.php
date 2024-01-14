<?php

// 个税税率表
const TAX_RATES = [
    36000       => [0.03, 0, 0],
    144000      => [0.1, 2520, 36000],
    300000      => [0.2, 16920, 144000],
    420000      => [0.25, 31920, 300000],
    660000      => [0.3, 52920, 420000],
    960000      => [0.35, 85920, 660000],
    PHP_INT_MAX => [0.45, 181920, 960000], // 适用于超过960000的部分
];

/**
 * 计算月度个人所得税。
 *
 * @param float $taxableIncome 累积应纳税所得额
 * @param float $taxTotal      已缴税总额
 *
 * @return float 应缴税额
 */
function calculateMonthlyTax(float $taxableIncome, float $taxTotal): float
{
    // 初始化税额
    $tax = 0;

    // 遍历税率和速算扣除数来计算税额
    foreach (TAX_RATES as $threshold => $rateInfo) {
        [$rate, $quickDeduction, $lowerLimit] = $rateInfo;
        if ($taxableIncome > $lowerLimit && $taxableIncome <= $threshold) {
            $tax = $taxableIncome * $rate - $quickDeduction;
            break;
        }
    }

    // 返回计算结果，减去已缴税总额
    return round($tax, 2) - $taxTotal;
}

/**
 * 调用固定工资税后收入计算
 *
 * @param float $income  月收入
 * @param float $insure  社保+公积金等扣除
 * @param float $special 专项扣除
 *
 * @return string
 */
function calculateFixedIncomeTax(float $income, float $insure, float $special): string
{
    // 累计交税额
    $taxTotal = 0;
    // 个税起征点
    $taxFreeThreshold = 5000;
    // 输出内容
    $output = '';
    for ($monthly = 1; $monthly <= 12; ++$monthly) {
        $cumulativeIncome  = $income                               * $monthly;
        $cumulativeInsure  = $insure                               * $monthly;
        $cumulativeSpecial = $special                              * $monthly;
        $taxableIncome     = $cumulativeIncome - $taxFreeThreshold * $monthly - $cumulativeInsure - $cumulativeSpecial;

        $tax = calculateMonthlyTax($taxableIncome, $taxTotal);
        $taxTotal += $tax;
        $output .= "月份：{$monthly}，税额：{$tax}</br>";
    }

    $output .= '12月份累计专项扣除：' . ($special * 12) . '</br>';
    $output .= "12月份累计交税额：{$taxTotal}</br>";
    $output .= '12月份累计收入：' . ($income * 12) . '</br>';

    return $output;
}

/**
 * 调用固定工资税后收入计算
 *
 * @param array $data
 *
 * @return string
 */
function calculateFlexibleIncomeTax(array $data): string
{
    // 累计交税额
    $taxTotal = 0;
    // 个税起征点
    $taxFreeThreshold = 5000;
    // 输出内容
    $output = '';
    // 12月份累计专项扣除
    $cumulativeSpecialTotal = 0;
    // 12月份累计收入
    $cumulativeIncomeTotal = 0;
    foreach ($data as $index => $month) {
        $monthly           = $index + 1;
        $cumulativeIncome  = array_sum(array_column(array_slice($data, 0, $monthly), 'income'));
        $cumulativeInsure  = array_sum(array_column(array_slice($data, 0, $monthly), 'insure'));
        $cumulativeSpecial = array_sum(array_column(array_slice($data, 0, $monthly), 'special'));

        $taxableIncome = $cumulativeIncome - $taxFreeThreshold * $monthly - $cumulativeInsure - $cumulativeSpecial;

        $tax = calculateMonthlyTax($taxableIncome, $taxTotal);
        $taxTotal += $tax;
        $output .= "月份：{$monthly}，税额：{$tax}</br>";

        if (12 === $monthly) {
            $cumulativeSpecialTotal = $cumulativeSpecial;
            $cumulativeIncomeTotal  = $cumulativeIncome;
        }
    }

    $output .= "12月份累计专项扣除：{$cumulativeSpecialTotal}</br>";
    $output .= "12月份累计交税额：{$taxTotal}</br>";
    $output .= "12月份累计收入：{$cumulativeIncomeTotal}</br>";

    return $output;
}

// 调用固定工资税后收入计算
echo calculateFixedIncomeTax(10000, 351, 400);

$data = [
    [
        'income'  => 10000,
        'insure'  => 351,
        'special' => 400,
    ],
    [
        'income'  => 8000,
        'insure'  => 351,
        'special' => 400,
    ],
    [
        'income'  => 9000,
        'insure'  => 351,
        'special' => 400,
    ],
    [
        'income'  => 6000,
        'insure'  => 351,
        'special' => 400,
    ],
    [
        'income'  => 7000,
        'insure'  => 351,
        'special' => 400,
    ],
    [
        'income'  => 8000,
        'insure'  => 351,
        'special' => 1900,
    ],
    [
        'income'  => 10000,
        'insure'  => 351,
        'special' => 1900,
    ],
    [
        'income'  => 10000,
        'insure'  => 351,
        'special' => 1900,
    ],
    [
        'income'  => 10000,
        'insure'  => 351,
        'special' => 1900,
    ],
    [
        'income'  => 10000,
        'insure'  => 351,
        'special' => 1900,
    ],
    [
        'income'  => 10000,
        'insure'  => 351,
        'special' => 1900,
    ],
    [
        'income'  => 10000,
        'insure'  => 351,
        'special' => 1900,
    ],
];

// 调用灵活工资税后收入计算
echo calculateFlexibleIncomeTax($data);
