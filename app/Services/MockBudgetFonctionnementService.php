<?php

namespace App\Services;

use App\Mocks\MockBudgetFonctionnement;

class MockBudgetFonctionnementService {
    public function getBudgets(){
        $budgets = MockBudgetFonctionnement::all();
        return $budgets;
    }
}