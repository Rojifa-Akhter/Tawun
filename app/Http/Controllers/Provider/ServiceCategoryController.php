<?php
namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Models\ServiceSubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceCategoryController extends Controller
{
    // Store a new category with its subcategory
    public function storeCategoryWithSubcategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_name'       => 'required|string',
            'sub_categories_name' => 'nullable|string',
            'icon'                => 'nullable',
            'image'               => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        // Check if category exists
        $category = ServiceCategory::where('name', $request->category_name)->first();

        if (! $category) {
            $icon_name = null;
            if ($request->hasFile('icon')) {
                $icon      = $request->file('icon');
                $extension = $icon->getClientOriginalExtension();
                $icon_name = time() . '.' . $extension;
                $icon->move(public_path('uploads/category_icons'), $icon_name);
            }
            $category = ServiceCategory::create([
                'name' => $request->category_name,
                'icon' => $icon_name,
            ]);
        }

        // Create subcategory
        $new_name = null;
        if ($request->hasFile('image')) {
            $image     = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $new_name  = time() . '.' . $extension;
            $image->move(public_path('uploads/sub_category_images'), $new_name);
        }
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
    public function storeSubcategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:service_categories,id',
        ], [
            'category_id.exists' => 'The selected category does not exist.', // Custom error message
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $subcategory = ServiceSubCategory::create([
            'name'                => $request->name,
            'service_category_id' => $request->category_id,
        ]);
        $subcategory->load('category:id,name,icon');

        return response()->json([
            'status'      => true,
            'message'     => 'Subcategory added successfully',
            'subcategory' => $subcategory,
        ], 201);
    }

    // Update a subcategory and change its category if needed
    public function updateSubcategory(Request $request, $id)
    {
        try {
            $sub_category = ServiceSubCategory::with('category:id,name,icon')->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Sub Category Not Found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'                => 'nullable|string|max:255',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'image'               => 'nullable|image',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        // Handle Image Upload
        if ($request->hasFile('image')) {
            $existingImage = $sub_category->image;

            // Delete old image if it exists
            if ($existingImage) {
                $relativePath = parse_url($existingImage, PHP_URL_PATH);
                $relativePath = ltrim($relativePath, '/');
                $fullPath     = public_path($relativePath);

                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }

            // Upload new image
            $image     = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $new_name  = time() . '.' . $extension;
            $image->move(public_path('uploads/sub_category_images'), $new_name);

            $validatedData['image'] = $new_name;
        }

        // Update the subcategory
        $sub_category->update($validatedData);

        $sub_category->refresh()->load('category:id,name,icon');

        return response()->json([
            'status'      => true,
            'message'     => 'Subcategory updated successfully',
            'subcategory' => $sub_category,
        ]);
    }

    // Delete a subcategory
    public function deleteSubcategory($id)
    {
        $sub_category = ServiceSubCategory::find($id);

        if (! $sub_category) {
            return response()->json(['status' => false, 'message' => 'SubCategory Not Found'], 401);
        }

        $sub_category->delete();

        return response()->json(['message' => 'Subcategory deleted successfully']);
    }
    //delete category
    public function deleteServiceCategory($id)
    {
        $category = ServiceCategory::find($id);

        if (! $category) {
            return response()->json(['status' => false, 'message' => 'Category Not Found'], 404);
        }

        // First, delete all associated subcategories
        ServiceSubCategory::where('service_category_id', $category->id)->delete();

        // Then, delete the category itself
        $category->delete();

        return response()->json(['status' => true, 'message' => 'Category and all its subcategories deleted successfully']);
    }

    //category list with subcategory
    public function getCategory(Request $request)
    {
        $search = $request->input('search');

        $category_list = ServiceCategory::with('subcategories');

        // Apply search filter if provided
        if ($search) {
            $category_list = $category_list->where('ticket_type', $search);
        }

        // Get paginated result
        $category_list = $category_list->paginate();

        if ($category_list->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'There is no data in the category list'], 401);
        }
        return response()->json(['status' => true, 'data' => $category_list], 200);
    }

}
