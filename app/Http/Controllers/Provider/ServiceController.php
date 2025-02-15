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
        $new_name = null;
        if ($request->hasFile('image')) {
            $image     = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $new_name  = time() . '.' . $extension;
            $image->move(public_path('uploads/service_images'), $new_name);
        }

        $services = Services::create([
            'provider_id'               => auth()->id(),
            'service_category_id'       => $request->service_category_id,
            'service_sub_categories_id' => $request->service_sub_categories_id,
            'title'                     => $request->title,
            'description'               => $request->description,
            'price'                     => $request->price,
            'service_type'              => $request->service_type,
            'image'                     => $new_name,
        ]);
        // return $services;

        $services->save();
        $services->load('provider:id,full_name', 'category:id,name,icon', 'subCategory:id,name,image');

        return response()->json([
            'status'  => true,
            'message' => 'Service added successfully',
            'data'    => $services,
        ], 201);
    }
}
