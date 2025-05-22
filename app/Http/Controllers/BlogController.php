<?php


namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class BlogController extends Controller
{
    // Store a new blog post
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:blogs,slug',
            'description1' => 'required|string',
            'description2' => 'required|string',
            'feature' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // image validation
        ]);
    
      
        if($request->hasFile('feature')){
            $featurePath = $request->file('feature')->store('blogs', 'public');
            $feature = URL::to(Storage::url($featurePath));
        }
    
        // Create the blog post
        $blog = Blog::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'description1' => $request->description1,
            'description2' => $request->description2,
            'feature' => $feature, // Save image path
        ]);
    
        // Return the created blog as JSON response
        return response()->json(['blog' => $blog], 201);
    }
    
    // Get all blog posts
    public function index()
    {
        $blogs = Blog::all();
        return response()->json($blogs);
    }

    // Get a blog post by ID
    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)->first();

        if (!$blog) {
            return response()->json(['message' => 'Blog not found'], 404);
        }
        return response()->json($blog);
    }


    public function delete($id)
    {
        $blog = Blog::find($id);
  

        if (!$blog) {
            return response()->json(['message' => 'Blog not found'], 404);
        }

        $blog->delete();

             return response()->json(['message' => 'blog deleted successfully']);
    }
}
