<?php

namespace App\Http\Controllers\Cashflow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CashflowCategory;

class CashflowCategoryController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $categories = CashflowCategory::with('parentCategory', 'childCategories')
            ->orderBy('categoryType')
            ->orderBy('categoryName')
            ->get()
            ->groupBy('categoryType');

        return view('cashflow.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        $parentCategories = CashflowCategory::where('isActive', true)
            ->orderBy('categoryName')
            ->get();

        return view('cashflow.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'categoryName' => 'required|string|max:100',
            'categoryType' => 'required|in:income,expense',
            'parentCategoryId' => 'nullable|exists:cashflow_categories,categoryId',
            'description' => 'nullable|string|max:500',
        ]);

        CashflowCategory::create([
            'categoryName' => $request->categoryName,
            'categoryType' => $request->categoryType,
            'parentCategoryId' => $request->parentCategoryId,
            'description' => $request->description,
            'isActive' => true,
        ]);

        return redirect()->route('cashflow.categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit($id)
    {
        $category = CashflowCategory::findOrFail($id);
        $parentCategories = CashflowCategory::where('isActive', true)
            ->where('categoryId', '!=', $id)
            ->orderBy('categoryName')
            ->get();

        return view('cashflow.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        $category = CashflowCategory::findOrFail($id);

        $request->validate([
            'categoryName' => 'required|string|max:100',
            'categoryType' => 'required|in:income,expense',
            'parentCategoryId' => 'nullable|exists:cashflow_categories,categoryId|different:categoryId',
            'description' => 'nullable|string|max:500',
            'isActive' => 'nullable|boolean',
        ]);

        $category->update([
            'categoryName' => $request->categoryName,
            'categoryType' => $request->categoryType,
            'parentCategoryId' => $request->parentCategoryId,
            'description' => $request->description,
            'isActive' => $request->has('isActive') ? $request->isActive : $category->isActive,
        ]);

        return redirect()->route('cashflow.categories.index')
            ->with('success', 'Catégorie modifiée avec succès.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy($id)
    {
        $category = CashflowCategory::findOrFail($id);

        // Vérifier si la catégorie est utilisée
        if ($category->transactions()->count() > 0) {
            return back()->withErrors(['error' => 'Cette catégorie ne peut pas être supprimée car elle est utilisée dans des transactions.']);
        }

        // Vérifier si elle a des sous-catégories
        if ($category->childCategories()->count() > 0) {
            return back()->withErrors(['error' => 'Cette catégorie ne peut pas être supprimée car elle contient des sous-catégories.']);
        }

        $category->delete();

        return redirect()->route('cashflow.categories.index')
            ->with('success', 'Catégorie supprimée avec succès.');
    }
}

