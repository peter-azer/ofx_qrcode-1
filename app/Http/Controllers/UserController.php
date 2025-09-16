<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use App\Models\Profile;

class UserController extends Controller
{
    public function update(Request $request){
        $user = auth()->user();
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'address' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validatedData['avatar'] = $avatarPath;
        }

        $user->update($validatedData);

        return response()->json(['message' => 'User updated successfully']);
    }

        public function deleteUser($id){
        try {
            $id = intval($id);
            dd($id);
            if (!auth()->user()->role == 'admin') {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            $user = User::findOrFail($id);

            $profile = Profile::where('user', $user->id)->first();
            if ($profile) {
                // Delete associated links
                $profile->links()->delete();
                
                // Delete associated images and their files
                foreach ($profile->images as $image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
                
                // Delete associated pdfs and their files
                foreach ($profile->pdfs as $pdf) {
                    Storage::disk('public')->delete($pdf->pdf_path);
                    $pdf->delete();
                }
                
                // Delete associated events
                $profile->events()->delete();
                
                // Delete associated branches
                $profile->branches()->delete();
                
                // Delete associated records and their files
                foreach ($profile->records as $record) {
                    Storage::disk('public')->delete($record->mp3_path);
                    $record->delete();
                }
                
                // Finally, delete the profile itself and the user
                $profile->delete();
            }                   
            $user->delete();

            return response()->json(['message' => 'Profile and all associated data deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
