<?php
namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Services;
use App\Models\ServiceSubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    //create services
    public function createServices(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'service_category_id'       => 'required|string|exists:service_categories,id',
            'service_sub_categories_id' => 'required|string|exists:service_sub_categories,id',
            'title'                     => 'required|string|max:255',
            'description'               => 'required|string',
            'price'                     => 'required|string',
            'service_type'              => 'required|in:virtual,in-person',
            'image'                     => 'nullable|image',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        // Check if the sub-category belongs to the given category
        $subCategoryExists = ServiceSubCategory::where('id', $request->service_sub_categories_id)
            ->where('service_category_id', $request->service_category_id)
            ->exists();

        if (! $subCategoryExists) {
            return response()->json([
                'status'  => false,
                'message' => 'The selected sub-category does not belong to the given category.',
            ], 422);
        }

        // image upload
        $service_image = null;
        if ($request->hasFile('image')) {
            $image         = $request->file('image');
            $extension     = $image->getClientOriginalExtension();
            $service_image = time() . '.' . $extension;
            $image->move(public_path('uploads/service_images'), $service_image);
        }

        $services = Services::create([
            'provider_id'               => auth()->id(),
            'service_category_id'       => $request->service_category_id,
            'service_sub_categories_id' => $request->service_sub_categories_id,
            'title'                     => $request->title,
            'description'               => $request->description,
            'price'                     => $request->price,
            'service_type'              => $request->service_type,
            'image'                     => $service_image,
        ]);
        // return $services;

        $services->save();
        $services->load('provider:id,full_name,image', 'category:id,name,icon', 'subCategory:id,name,image');

        return response()->json([
            'status'  => true,
            'message' => 'Service added successfully',
            'data'    => $services,
        ], 201);
    }
    public function updateServices(Request $request, $id)
    {
        $services = Services::with('provider:id,full_name,image', 'category:id,name,icon', 'subCategory:id,name,image')->findOrFail($id);

        if (! $services) {
            return response()->json(['status' => false, 'message' => 'Service Not Found'], 422);
        }
        $validator = Validator::make($request->all(), [
            'service_category_id'       => 'nullable|string|exists:service_categories,id',
            'service_sub_categories_id' => 'nullable|string|exists:service_sub_categories,id',
            'title'                     => 'nullable|string|max:255',
            'description'               => 'nullable|string',
            'price'                     => 'nullable|string',
            'service_type'              => 'nullable|in:virtual,in-person',
            'image'                     => 'nullable|image',
        ]);
        if (! $validator) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();

        // Handle Image Upload
        if ($request->hasFile('image')) {
            $existingImage = $services->image;

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
            $image->move(public_path('uploads/service_images'), $new_name);

            $validatedData['image'] = $new_name;
        }

        // Update the subcategory
        $services->update($validatedData);
        $services->refresh()->load('provider:id,full_name,image', 'category:id,name,icon', 'subCategory:id,name,image');

        return response()->json([
            'status'      => true,
            'message'     => 'Subcategory updated successfully',
            'subcategory' => $services,
        ]);
    }
    //delete service
    public function deleteService($id)
    {
        $service = Services::find($id);

        if (! $service) {
            return response()->json(['status' => false, 'message' => 'Service Not Found'], 401);
        }

        $service->delete();

        return response()->json(['message' => 'Service deleted successfully']);
    }
    public function getService(Request $request)
    {
        $search = $request->input('search');

        $service_list = Services::with('subCategory');

        if ($search) {
            $service_list = $service_list->where('title', 'like', "%$search%");
        }

        $service_list = $service_list->paginate();

        if ($service_list->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'There is no data in the service list'], 401);
        }
        return response()->json(['status' => true, 'data' => $service_list], 200);
    }
    public function servicesDetails($id)
    {
        $service = Services::with([
            'provider:id,full_name,image',
            'category:id,name,icon',
            'subCategory:id,name,image',
        ])->find($id);

        if (!$service) {
            return response()->json(['status' => false, 'message' => 'Service Not Found'], 401);
        }

        return response()->json(['status' => true, 'data' => $service], 200);
    }

}
