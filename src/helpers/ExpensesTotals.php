<?php
namespace App\helpers;

class ExpensesTotals {
  public static function getTotals($data, $categories) {
    $expenses = [];
    foreach ($data as $expense) {
        $categoryName = $categories[$expense['category']] ?? 'Unknown';
        if (!isset($expenses[$categoryName])) {
            $expenses[$categoryName] = [
                'total' => 0,
                'items' => []
            ];
        }
        $expenses[$categoryName]['items'][] = $expense;
        $expenses[$categoryName]['total'] += $expense['value'];
    }

    // Remove empty keys and format totals
    $expenses = array_filter($expenses);
    foreach ($expenses as $_ => &$categoryData) {
        $categoryData['total'] = number_format(ceil($categoryData['total'] * 100) / 100, 2, '.', '');
    }

    return $expenses;
  }
}