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
use App\Actions\Lessons\{
    IndexLessonAction,
    ShowLessonAction
};

class CourseController extends Controller
{
    public function index(Request $request, IndexCourseAction $action)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $courses = $action->execute($limit, $page);
        
        return response()->json([
            'success' => true,
            'data' => $courses->items(),
            'pagination' => [
                'current_page' => $courses->currentPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
                'last_page' => $courses->lastPage(),
                'from' => $courses->firstItem(),
                'to' => $courses->lastItem(),
            ]
        ]);
    }

    public function show(Request $request, int $id, ShowCourseAction $action)
    {
        $course = $action->execute($id);
        
        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $course
        ]);
    }

    public function search(Request $request, SearchCourseAction $action)
    {
        $title = $request->input('title', '');
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        
        $courses = $action->execute($title, $limit, $page);
        
        return response()->json([
            'success' => true,
            'data' => $courses->items(),
            'pagination' => [
                'current_page' => $courses->currentPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
                'last_page' => $courses->lastPage(),
                'from' => $courses->firstItem(),
                'to' => $courses->lastItem(),
            ]
        ]);
    }

    public function filter(Request $request, FilterCourseAction $action)
    {
        $filters = $request->only(['level', 'mode', 'has_seats']);
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        
        $courses = $action->execute($filters, $limit, $page);
        
        return response()->json([
            'success' => true,
            'data' => $courses->items(),
            'pagination' => [
                'current_page' => $courses->currentPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
                'last_page' => $courses->lastPage(),
                'from' => $courses->firstItem(),
                'to' => $courses->lastItem(),
            ]
        ]);
    }

    public function lessons(Request $request, int $courseId, IndexLessonAction $action)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $result = $action->execute($courseId, $limit, $page);
        
        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $result->items(),
            'pagination' => [
                'current_page' => $result->currentPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
                'last_page' => $result->lastPage(),
                'from' => $result->firstItem(),
                'to' => $result->lastItem(),
            ]
        ]);
    }

    // public function showLesson(Request $request, int $courseId, int $lessonId, ShowLessonAction $action)
    // {
    //     $lesson = $action->execute($courseId, $lessonId);
        
    //     if (!$lesson) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Lesson not found or does not belong to this course'
    //         ], 404);
    //     }
        
    //     return response()->json([
    //         'success' => true,
    //         'data' => $lesson
    //     ]);
    // }

    public function downloadCourseBooklet(Request $request, int $courseId)
    {
        $course = \App\Models\Course::find($courseId);
        
        if (!$course || !$course->course_booklet) {
            return response()->json([
                'success' => false,
                'message' => 'Course or booklet not found'
            ], 404);
        }
        
        $filePath = storage_path('app/public/' . $course->course_booklet);
        
        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Booklet file not found'
            ], 404);
        }
        
        return response()->download($filePath, basename($filePath));
    }

public function showUnits(Request $request, int $courseId)
{
    $units = \App\Models\Unit::with([
        'lessons' => function ($query) {
            $query->orderBy('order');
        }
    ])
        ->withCount('lessons')
        ->where('course_id', $courseId)
        ->orderBy('order')
        ->get();
    
    if ($units->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No units found for this course'
        ], 404);
    }
    
    return response()->json([
        'success' => true,
        'data' => $units
    ]);
}

   public function showunitlessons(Request $request, int $courseId, int $unitId)
{
    $unit = \App\Models\Unit::with([
        'lessons' => function ($query) {
            $query->orderBy('order');
        }
    ])
        ->where('course_id', $courseId)
        ->find($unitId);
    
    if (!$unit) {
        return response()->json([
            'success' => false,
            'message' => 'Unit not found'
        ], 404);
    }
    
    return response()->json([
        'success' => true,
        'data' => [
            'unit' => [
                'id' => $unit->id,
                'title' => $unit->title,
                'order' => $unit->order
            ],
            'lessons' => $unit->lessons
        ]
    ]);
}

public function showLesson(Request $request, int $courseId, int $unitId, int $lessonId)
{
    $lesson = \App\Models\Lesson::with([
        'unit:id,title,order,course_id',
        'unit.course:id,title'
    ])
        ->whereHas('unit', function ($query) use ($courseId, $unitId) {
            $query->where('id', $unitId)
                  ->where('course_id', $courseId);
        })
        ->find($lessonId);
    
    if (!$lesson) {
        return response()->json([
            'success' => false,
            'message' => 'Lesson not found'
        ], 404);
    }
    
    return response()->json([
        'success' => true,
        'data' => $lesson
    ]);
}
}