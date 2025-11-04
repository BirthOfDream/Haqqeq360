<?php

namespace App\Http\Controllers\Api\CourseController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Actions\Courses\{
    IndexCourseAction,
    ShowCourseAction,
    SearchCourseAction,
    FilterCourseAction
};

class CourseController extends Controller
{
    public function index(Request $request, IndexCourseAction $action)
    {
        $limit = $request->input('limit', 10);
        $courses = $action->execute($limit);
        return response()->json(['success' => true, 'data' => $courses]);
    }

    public function show(Request $request, int $id, ShowCourseAction $action)
    {
        $course = $action->execute($id);
        if (!$course) {
            return response()->json(['success' => false, 'message' => 'Course not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $course]);
    }

    public function search(Request $request, SearchCourseAction $action)
    {
        $title = $request->input('title', '');
        $limit = $request->input('limit', 10);
        $courses = $action->execute($title, $limit);
        return response()->json(['success' => true, 'data' => $courses]);
    }

    public function filter(Request $request, FilterCourseAction $action)
    {
        $filters = $request->only(['level', 'mode']);
        $limit = $request->input('limit', 10);
        $courses = $action->execute($filters, $limit);
        return response()->json(['success' => true, 'data' => $courses]);
    }
}
