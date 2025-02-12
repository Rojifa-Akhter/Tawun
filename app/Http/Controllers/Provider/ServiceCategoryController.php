<?php
namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Models\ServiceSubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceCategoryController extends Controller
{
    //category list with subcategory
    public function getCategory()
    {
        $categories = ServiceCategory::with('subcategories')->get();
        return response()->json($categories);
    }
    // Store a new category with its subcategory
    public function storeCategoryWithSubcategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_name'       => 'required|string|unique:service_categories,name',
            'sub_categories_name' => 'required|string',
            'icon'                => 'nullable',
            'image'               => 'nullable',
        ]);

        if (! $validator) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $category = ServiceCategory::create([
            'name' => $request->name,
        ]);

        $subcategory = ServiceSubCategory::create([
            'name'                => $request->name,
            'service_category_id' => $category->id,
        ]);

        return response()->json([
            'message'     => 'Category and subcategory added successfully',
            'category'    => $category,
            'subcategory' => $subcategory,
        ]);
    }
    // Store a new subcategory under a category
    public function storeSubcategory(Request $request, ServiceCategory $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:service_sub_categories,name',

        ]);
        if (! $validator) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $subcategory = ServiceSubCategory::create([
            'name'                => $request->name,
            'service_category_id' => $category->id,
        ]);

        return response()->json(['message' => 'Subcategory added successfully', 'subcategory' => $subcategory]);
    }

    // Update a subcategory and change its category if needed
    public function updateSubcategory(Request $request, ServiceSubCategory $subcategory)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'nullable|string|unique:service_sub_categories,name,' . $subcategory->id,
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $subcategory->update([
            'name'        => $request->name,
            'category_id' => $request->category_id,
        ]);

        return response()->json(['message' => 'Subcategory updated successfully', 'subcategory' => $subcategory]);
    }

    // Delete a subcategory
    public function deleteSubcategory(ServiceSubCategory $subcategory)
    {
        $subcategory->delete();

        return response()->json(['message' => 'Subcategory deleted successfully']);
    }
}
