<?php
namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function createServices(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_category_id'       => 'required|string|exists:service_categories,id',
            'service_sub_categories_id' => 'required|string|exists:service_sub_categories,id',
            'title'                     => 'required|string|max:255',
            'description'               => 'required|string',
            'price'                     => 'required|string',
            'service_type'              => 'required|in:virtual,in-person',
            'image'                     => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }
        if ($request->hasFile('image')) {
            $image     = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $new_name  = time() . '.' . $extension;
            $image->move(public_path('uploads/sub_category_images'), $new_name);
        }
        $services = Services::create([
            'service_category_id'       => $request->service_category_id,
            'service_sub_categories_id' => $request->service_sub_categories_id,
            'title'                     => $request->title,
            'description'               => $request->description,
            'price'                     => $request->price,
            'service_type'              => $request->service_type,
            'image'                     => $request->image,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Service added successfully',

        ], 201);
    }
}
