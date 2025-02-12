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
            'category_name'       => 'required|string',
            'sub_categories_name' => 'required|string',
            'icon'                => 'nullable',
            'image'               => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        //icon for category
        $icon_name = null;
        if ($request->hasFile('icon')) {
            $icon      = $request->file('icon');
            $extension = $icon->getClientOriginalExtension();
            $icon_name = time() . '.' . $extension;
            $icon->move(public_path('uploads/category_icons'), $icon_name);
        }
        //    return  $icon_name;
        //image for subcategory
        $new_name = null;
        if ($request->hasFile('image')) {
            $image     = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $new_name  = time() . '.' . $extension;
            $image->move(public_path('uploads/sub_category_images'), $new_name);
        }
        // return $new_name;

        // Check if category exists
        $category = ServiceCategory::where('name', $request->category_name)->first();

        if (! $category) {
            // If category does not exist, create a new one
            $category = ServiceCategory::create([
                'name' => $request->category_name,
                'icon' => $icon_name,
            ]);
        }

        // Create subcategory
        $subcategory = ServiceSubCategory::create([
            'name'                => $request->sub_categories_name,
            'image'               => $new_name,
            'service_category_id' => $category->id,
        ]);

        return response()->json([
            'status'      => true,
            'message'     => 'Category and subcategory added successfully',
            'category'    => $category,
            'subcategory' => $subcategory,
        ], 201);
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
