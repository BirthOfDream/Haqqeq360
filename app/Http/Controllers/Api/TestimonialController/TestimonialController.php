<?php

namespace App\Http\Controllers\Api\TestimonialController;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TestimonialController extends Controller
{
    public function index()
    {
        return response()->json(Testimonial::where('status', 'published')->get());
    }

    public function show($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        return response()->json($testimonial);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:100',
            'position'=> 'nullable|string|max:150',
            'review'  => 'required',
            'status'  => 'required|in:draft,published',
            'image'   => 'nullable|image|max:2048'
        ]);

        if ($validator->fails())
            return response()->json($validator->errors(), 422);

        $data = $request->all();

        if($request->hasFile('image')){
            $data['image_path'] = $request->file('image')->store('testimonials', 'public');
        }

        $testimonial = Testimonial::create($data);

        return response()->json($testimonial, 201);
    }
}
