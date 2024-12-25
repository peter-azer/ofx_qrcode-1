<?php


namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    // Store a new blog post
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description1' => 'required|string',
            'description2' => 'required|string',
            'feature' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image1' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image2' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle image uploads
        $feature =   $request->hasFile('image1') ? $request->file('image1')->store('blogs', 'public') : null;
        $image1Path = $request->hasFile('image1') ? $request->file('image1')->store('blogs', 'public') : null;
        $image2Path = $request->hasFile('image2') ? $request->file('image2')->store('blogs', 'public') : null;

        // Create the blog post
        $blog = Blog::create([
            'title' => $request->title,
            'description1' => $request->description1,
            'description2' => $request->description2,
            'feature' => $feature,
            'image1' => $image1Path,
            'image2' => $image2Path,
        ]);

        return response()->json(['blog' => $blog], 201);
    }

    // Get all blog posts
    public function index()
    {
        $blogs = Blog::all();
        return response()->json($blogs);
    }

    // Get a blog post by ID
    public function show($id)
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json(['message' => 'Blog not found'], 404);
        }

        return response()->json($blog);
    }
}
