<?php

namespace App\Http\Controllers;

use App\Http\Resources\MockBudgetFonctionnement;
use App\Services\MockBudgetFonctionnementService;
use Illuminate\Http\Request;

class BudgetFonctionnementController extends Controller
{
    //
    public function index(MockBudgetFonctionnementService $service){
        $budgets = $service->getBudgets();
        return MockBudgetFonctionnement::collection($budgets);
    }
}
