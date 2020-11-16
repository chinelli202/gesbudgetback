<?php

namespace App\Http\Controllers;

use App\Http\Resources\MockBudgetFonctionnement;
use App\Services\MockBudgetFonctionnementService;
use Dotenv\Result\Success;
use Illuminate\Http\Request;

class BudgetFonctionnementController extends Controller
{
    private $sucess_status = 200;
    //
    public function index(MockBudgetFonctionnementService $service){
        $budgets = $service->getBudgets();
        return response()->json(["status" => $this->sucess_status, "success" => true, "data" => $budgets]);
        // return MockBudgetFonctionnement::collection($budgets);
    }
}
