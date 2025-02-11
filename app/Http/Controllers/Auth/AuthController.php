<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerifyOTP;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    //get user profile
    public function ownProfile()
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'User Not Found'], 422);
        }

        return response()->json(['status' => true, 'data' => $user]);
    }
    //signup or registration
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name'            => 'required|string|max:255',
            'email'                => 'required|string|email|unique:users,email',
            'provider_description' => 'required|string',
            'password'             => 'required|string|min:6',
            'address'              => 'nullable|string|max:255',
            'contact'              => 'nullable|string|max:15',
            'role'                 => 'nullable|string|in:super_admin,provider,user',
            'image'                => 'nullable|image',
            'document'             => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 400);
        }

        $new_name = null;
        if ($request->hasFile('image')) {
            $image     = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $new_name  = time() . '.' . $extension;
            $image->move(public_path('uploads/profile_images'), $new_name);
        }

        $otp            = rand(100000, 999999);
        $otp_expires_at = now()->addMinutes(10);
        $role           = $request->role ?? 'user';

        $user = User::create([
            'name'                 => $request->name,
            'email'                => $request->email,
            'provider_description' => $request->provider_description,
            'address'              => $request->address,
            'contact'              => $request->contact,
            'password'             => Hash::make($request->password),
            'role'                 => $role,
            'image'                => $new_name,
            'otp'                  => $otp,
            'otp_expires_at'       => $otp_expires_at,
            'status'               => 'inactive',
        ]);

        try {
            Mail::to($user->email)->send(new VerifyOTP($otp));
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status'       => true,
            'message'      => 'Registration successful. Please verify your email!',
            'access_token' => $token,
            'token_type'   => 'bearer',
        ], 200);
    }

    // verify email
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 400);
        }
        $user = User::where('otp', $request->otp)->first();

        if ($user) {
            $user->otp               = null;
            $user->email_verified_at = now();
            $user->status            = 'active';
            $user->save();

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'status'  => true,
                'message' => 'OTP verified successfully.',
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'error'  => 'Invalid OTP.'], 400);
    }
    //login
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'Email not found.'], 422);
        }

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid password.'], 401);
        }

        return response()->json([
            'status'           => true,
            'access_token'     => $token,
            'token_type'       => 'bearer',
            'user_information' => [
                'name'              => $user->name,
                'email'             => $user->email,
                'role'              => $user->role,
                'email_verified_at' => $user->email_verified_at,
                'image'             => $user->image,
            ],
        ], 200);

    }

    public function guard()
    {
        return Auth::guard('api');
    }
    // update profile
    public function updateProfile(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'User not authenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'full_name'            => 'nullable|string|max:255',
            'provider_description' => 'nullable|string',
            'cover_letter'         => 'nullable|string',
            'address'              => 'nullable|string|max:255',
            'contact'              => 'nullable|string|max:16',
            'password'             => 'nullable|string|min:6|confirmed',
            'image'                => 'nullable|file',
            'documents.*'          => 'nullable|file',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        $user->full_name            = $validatedData['full_name'] ?? $user->full_name;
        $user->address              = $validatedData['address'] ?? $user->address;
        $user->contact              = $validatedData['contact'] ?? $user->contact;
        $user->provider_description = $validatedData['provider_description'] ?? $user->provider_description;
        $user->cover_letter         = $validatedData['cover_letter'] ?? $user->cover_letter;

        if (! empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        if ($request->hasFile('image')) {
            $existingImage = $user->image;

            if ($existingImage) {
                $oldImage = parse_url($existingImage);
                $filePath = ltrim($oldImage['path'], '/');
                if (file_exists($filePath)) {
                    unlink($filePath); // Delete the existing image
                }
            }

            // Upload new image
            $image     = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $newName   = time() . '.' . $extension;
            $image->move(public_path('uploads/profile_images'), $newName);

            $user->image = $newName;
        }
        //delete old document
        if ($request->hasFile('documents')) {
            $existingDocuments = $user->document;

            if (is_array($existingDocuments)) {
                foreach ($existingDocuments as $document) {
                    $relativePath = parse_url($document, PHP_URL_PATH);
                    $relativePath = ltrim($relativePath, '/');
                    unlink(public_path($relativePath));
                }
            }

            // Upload new documents
            $newDocuments = [];
            foreach ($request->file('documents') as $document) {
                $documentName = time() . uniqid() . $document->getClientOriginalName();
                $document->move(public_path('uploads/documents'), $documentName);

                $newDocuments[] = $documentName;
            }

            // Save the new documents as a JSON-encoded array
            $user->document = json_encode($newDocuments);
        }
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Profile updated successfully.',
            'data'    => [
                'id'        => $user->id,
                'full_name' => $user->full_name,
                'email'     => $user->email,
                'address'   => $user->address,
                'contact'   => $user->phone,
                'image'     => $user->image,
                'documents' => $user->document, // decoded JSON
                'role'      => $user->role,
            ],
        ], 200);

    }

    //change password
    public function changePassword(Request $request)
    {

        $request->validate([
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Password changed successfully']);
    }
    // forgote password
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['error' => 'Email not registered.'], 404);
        }
        $otp = rand(100000, 999999);

        DB::table('users')->updateOrInsert(
            ['email' => $request->email],
            ['otp' => $otp, 'created_at' => now()]
        );

        try {
            Mail::to($request->email)->send(new VerifyOTP($otp));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Failed to send OTP.'], 500);
        }

        return response()->json([
            'status'  => true,
            'message' => 'OTP sent to your email.'], 200);
    }

    // reset password
    public function resetPassword(Request $request)
    {
        // return $request;
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Password reset successful.'], 200);
    }

    //resend otp
    public function resendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['error' => 'Email not registered.'], 404);
        }

        $otp = rand(100000, 999999);

        DB::table('users')->updateOrInsert(
            ['email' => $request->email],
            ['otp' => $otp, 'created_at' => now()]
        );

        try {
            Mail::to($request->email)->send(new VerifyOTP($otp));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Failed to resend OTP.'], 500);
        }

        return response()->json([
            'status'  => true,
            'message' => 'OTP resent to your email.'], 200);
    }
    //logout
    public function logout()
    {
        if (! auth('api')->check()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User is not authenticated.',
            ], 401);
        }

        auth('api')->logout();

        return response()->json([
            'status'  => true,
            'message' => 'Successfully logged out.',
        ]);
    }

}
