Project Structure 

app/
 ├── Actions/
 │    ├── Users/
 │    │     ├── CreateUserAction.php
 │    │     ├── ShowUserAction.php
 │    │     ├── UpdateUserAction.php
 │    │     └── DeleteUserAction.php
 │    ├── Courses/
 │    │     ├── CreateCourseAction.php
 │    │     ├── ShowCourseAction.php
 │    │     ├── UpdateCourseAction.php
 │    │     └── DeleteCourseAction.php
 │    ├── Bootcamps/
 │    ├── Enrollments/
 │    ├── Assignments/
 │    ├── Submissions/
 │    ├── Notifications/
 │    └── Reports/
 │
 ├── Repositories/
 │    ├── Interfaces/
 │    │     ├── UserRepositoryInterface.php
 │    │     ├── CourseRepositoryInterface.php
 │    │     ├── BootcampRepositoryInterface.php
 │    │     ├── EnrollmentRepositoryInterface.php
 │    │     ├── AssignmentRepositoryInterface.php
 │    │     ├── SubmissionRepositoryInterface.php
 │    │     ├── NotificationRepositoryInterface.php
 │    │     └── ReportRepositoryInterface.php
 │    │
 │    ├── User/
 │    │     └── UserRepository.php
 │    ├── Course/
 │    │     └── CourseRepository.php
 │    ├── Bootcamp/
 │    │     └── BootcampRepository.php
 │    ├── Enrollment/
 │    │     └── EnrollmentRepository.php
 │    ├── Assignment/
 │    │     └── AssignmentRepository.php
 │    ├── Submission/
 │    │     └── SubmissionRepository.php
 │    ├── Notification/
 │    │     └── NotificationRepository.php
 │    └── Report/
 │          └── ReportRepository.php
 │
 └── Http/
      └── Controllers/
           └── Api/
                ├── UserController/
                ├── CourseController/
                ├── BootcampController/
                ├── EnrollmentController/
                ├── AssignmentController/
                ├── SubmissionController/
                ├── NotificationController/
                └── ReportController/





Notifications : 
🧩 Logic Summary

Admin can:

Send a notification to all users or to specific users (via user_id array)

Instructor can:

Send to their own students (linked via enrollments in their courses or bootcamps)

Student can:

Only view, mark as read/unread, and delete their own notifications

All routes are protected by auth:sanctum.


Notes about CreateNotificationAction implementation:

For bulk operations we directly insert into DB with \DB::table('notifications')->insert(...) to avoid creating huge Eloquent objects. This preserves speed for "admin -> all" or large course audiences.

The action returns a lightweight summary (count / message) for bulk sends. If you want objects returned, we can change to inserting and then querying the created records (costly for large sets).