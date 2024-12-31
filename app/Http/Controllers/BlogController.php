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
        ]);

        // Handle image uploads
        $feature =   $request->hasFile('feature') ? $request->file('feature')->store('blogs', 'public') : null;


        // Create the blog post
        $blog = Blog::create([
            'title' => $request->title,
            'description1' => $request->description1,
            'description2' => $request->description2,
            'feature' => $feature,
     
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
