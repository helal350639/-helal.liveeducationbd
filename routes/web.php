<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\StudentLogin;
use App\Http\Controllers\Students\StudentHomeCtrl;
use App\Http\Controllers\Homepage\CategoryCtrl;
use App\Http\Controllers\Homepage\JobPreparationCtrl;
use App\Http\Controllers\Homepage\HomePageCtrl;
use App\Http\Controllers\Homepage\HomepageSettingCtrl;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Student;
use App;
use URL;
use Illuminate\Support\Facades\Log;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/auth/github', function () {
	return Socialite::driver('github')->redirect();
});

Route::get('/auth/google', function () {
	return Socialite::driver('google')->redirect();
});

// force https on production server
if (App::environment('production'))
{
	URL::forceScheme('https');
}


Route::controller(HomePageCtrl::class)->group(function()
{
	Route::get('/', 'index')->name('homepage');
	Route::get('home/course', 'course')->name('home.course');
	Route::get('home/course/{id}', 'courseShow')->name('home.course.show');
	Route::get('home/pages/{slug}', 'page')->name('home.page');
  Route::get('home/coming-soon', 'comingSoon')->name('home.coming-soon');
  Route::get('home/circular/{id?}', 'circular')->name('home.circular');
  Route::get('home/circular-details/{id}', 'circularShow')->name('home.circular.show');
  Route::get('home/results/{paper}', 'resultList')->name('home.results');
  Route::get('home/routine-filter/{id}/{department?}', 'routineFilter')->name('home.routine.filter');
});

Route::controller(CategoryCtrl::class)->group(function()
{
  Route::prefix('category')->group(function()
  {
    Route::get('courses/{type?}', 'courses')->name('category.courses');
    Route::get('batch/{type?}/{course}', 'batch')->name('category.batch');

    Route::get('departments/{type}', 'departments')->name('category.departments');

    Route::get('batches/{type?}/{dashboard?}', 'batches')->name('category.batches');
    Route::get('department/{type}/{batch?}/{dashboard?}', 'department')->name('category.department');
    Route::get('paper/{type}/{batch}/{department?}', 'paper')->name('category.paper');
    Route::get('result-option', 'resultOption')->name('category.result.option');
    Route::get('check-method/{type}/{batch}/{department?}', 'checkMethod')->name('category.check-method');
  });
});

Route::controller(JobPreparationCtrl::class)->group(function()
{
  Route::get('homepage/courses', 'courses')->name('homepage.courses');
  Route::get('homepage/batch/{course_id}', 'batches')->name('homepage.batches');
});

Route::get('/login', function()
{
	return view('auth.login');
})->name('login');

Route::post('/login', [LoginController::class, 'loginPost'])->name('login.post');
Route::controller(RegisterController::class)->group(function()
{
	Route::get('/signup', 'signup')->name('register');
	Route::post('/signup', 'signupPost')->name('register.post');
	Route::get('/account_verify/{code}', 'verify')->name('email.verify');
});

Route::controller(StudentLogin::class)->group(function()
{
	Route::get('students/login', 'login')->name('students.login');
	Route::post('students/login', 'loginPost')->name('students.login.post');
	Route::post('students/logout', 'logout')->name('students.logout');
	Route::get('/auth/github/callback', 'oAuthGithub');
	Route::get('/auth/google/callback', 'oAuthGoogle');
});

Route::controller(ForgotPasswordController::class)->group(function()
{
	Route::get('students/forgot-password', function() {
		return view('auth.passwords.email');
	})->middleware('guest')->name('password.request');
	
	Route::post('students/email-password', 'emailPassword')->name('password.email');

	Route::get('/reset-password/{token}', 'passwordReset')->middleware('guest')->name('password.reset');

	Route::post('password.update', 'updatePassword')->name('password.update');
});

Route::controller(SslCommerzPaymentController::class)->group(function()
{
	// SSLCOMMERZ Start
	Route::get('/example1', 'exampleEasyCheckout');
	Route::get('/payment-proceed', 'exampleHostedCheckout');

	Route::post('/students/pay', 'index')->name('payment.proceed');
	Route::post('/pay-via-ajax', 'payViaAjax');

	Route::post('/students/success', 'success');
	Route::post('/students/fail', 'fail');
	Route::post('/students/cancel', 'cancel');

	Route::post('/students/ipn', 'ipn');
	//SSLCOMMERZ END
});

Route::middleware('auth:student')->group(function()
{
	Route::controller(StudentHomeCtrl::class)->group(function()
	{
    Route::prefix('students')->group(function()
    {
      Route::get('/', 'dashboard')->name('students.dashboard');
      Route::get('home', 'home')->name('students.home');
      Route::get('exam', 'exam')->name('students.exam');
      Route::get('free-exam', 'freeExam')->name('students.free-exam');
      Route::get('batch-exam/{batch}', 'batchExam')->name('students.batch-exam');
      Route::get('check/{paper}', 'check')->name('students.check');
      Route::get('instruction/{paper}', 'instruction')->name('students.instruction');
      Route::get('exam/{id}', 'examShow')->name('students.exam.show');
      Route::get('questions/{paper_id}', 'getQuestions')->name('students.exam.questions');
      Route::get('batch-result', 'batchResult')->name('students.batch-result');
      Route::get('batch-routine', 'batchRoutine')->name('students.batch-routine');
      Route::get('result/{id}/{after?}', 'result')->name('students.result');
      Route::get('exam-paper/{id}', 'examPaper')->name('students.exam.paper');
      Route::get('solution/{id}', 'solution')->name('students.solution');
      Route::get('course', 'course')->name('students.course');
      Route::get('course/{id}', 'courseShow')->name('students.course.show');
      Route::post('course-apply', 'applyCourse')->name('students.course.apply');
      Route::get('checkout/{id}', 'checkout')->name('students.course.checkout');
      Route::get('confirm', 'confirm')->name('students.course.confirm');
      Route::get('my-course', 'myCourse')->name('students.my-course');
      Route::get('my-syllabus', 'mySyllabus')->name('students.my-syllabus');
      Route::post('my-syllabus', 'mySyllabusPost')->name('students.my-syllabus.post');
      Route::post('student/exam_add', 'examAdd')->name('student.exam.add');
      Route::get('syllabus/{id}', 'syllabus')->name('student.syllabus');
      Route::get('syllabus/{id}/pdf', 'generatePDF')->name('students.syllabus.pdf');
      Route::get('complain', 'complain')->name('students.complain');
      Route::post('complain', 'complainStore')->name('students.complain.store');
      Route::get('update-paper/{id}', 'updatePaperAjax')->name('paper.update.ajax');
      Route::get('profile', 'profile')->name('students.profile');
    });
	});

	Route::controller(VerificationController::class)->group(function()
	{
		Route::post('contact-check', 'contactCheck')->name('contact-check');
		Route::post('otp-check', 'otpCheck')->name('otp-check');
	});
});

Route::middleware(['auth'])->group(function()
{
	Route::get('/home', 'HomeCtrl@index')->name('home');
	Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

	// profile
	Route::get('profile', 'UserCtrl@profile')->name('profile');
	Route::get('profile/edit', 'UserCtrl@profileEdit')->name('profile.edit');
	Route::put('profile/update', 'UserCtrl@profileUpdate')->name('profile.update');

	//password change
	Route::get('/change_password', 'UserCtrl@changePassword')->name('user.password.change');
	Route::put('/change_password', 'UserCtrl@updatePassword')->name('user.change.password');
	
	//user routes
	Route::resource('/user', 'UserCtrl');

	/** -------------------- Resource Routes --------------- */
	Route::resource('filter', 'FilterCtrl');
	Route::resource('sub-filter', 'SubFilterCtrl');
	Route::resource('course', 'CourseCtrl');
	Route::resource('department', 'DepartmentCtrl');
	Route::resource('semester', 'SemesterCtrl');
	Route::resource('subject', 'SubjectCtrl');
	Route::resource('chapter', 'ChapterCtrl');
	Route::resource('question', 'QuestionCtrl');
	Route::resource('answer-file', 'AnswerFileCtrl');
	Route::resource('label', 'LabelCtrl');
	Route::resource('paper', 'PaperCtrl');
	Route::resource('batch', 'BatchCtrl');
	Route::resource('group', 'GroupCtrl');
	Route::resource('student', 'StudentCtrl');
	Route::resource('syllabus', 'SyllabusCtrl');
	Route::resource('teacher', 'TeacherCtrl');
	Route::resource('complain', 'ComplainCtrl');
	Route::resource('order', 'OrderCtrl');
	Route::resource('exam', 'ExamCtrl');
	Route::resource('page', 'PageCtrl');
	Route::resource('routine', 'RoutineCtrl');
	Route::resource('sms', 'SmsCtrl');
	Route::resource('email', 'EmailCtrl');
	Route::resource('circular', 'CircularCtrl');

	/** -------------------- Custom Routes ------------------ */
	Route::controller(DepartmentCtrl::class)->group(function()
	{
		Route::get('get_departments/{course_id}', 'getDepartments');
		Route::get('get_departments_by_batch/{batch_id}', 'getDepartmentsByBatch');
	});

	Route::controller(SemesterCtrl::class)->group(function()
	{
		Route::get('get_semesters/{department_id}', 'getSemesters');
	});

	Route::controller(SubjectCtrl::class)->group(function()
	{
		Route::get('get_sems_subjects/{semester_id}', 'getSemsSubjects');
		Route::get('get_subjects/{department_id}', 'getSubjects');
    Route::get('search-subject', 'search')->name('subject.search');
	});

	Route::controller(QuestionCtrl::class)->group(function()
	{
		Route::get('view_question', 'viewQuestion')->name('question.view');
		Route::post('question_view', 'viewQuestionPost')->name('question.view.post');
		Route::post('check_question_exist', 'Questions')->name('questions.title');
		Route::get('question_search', 'QuestionSearch')->name('question.search');
	});

	Route::controller(ChapterCtrl::class)->group(function()
	{
		Route::get('get_chapters/{subject_id}', 'getChapters');
    Route::get('search-chapter/{data?}', 'search')->name('chapter.search');
	});

	Route::controller(FilterCtrl::class)->group(function()
	{
		Route::get('get_filters/{course_id}', 'getFilters');
	});

	Route::controller(PaperCtrl::class)->group(function()
	{
    Route::prefix('paper')->group(function()
    {
      Route::get('add-question/{paper_id}', 'addQuestion')->name('paper.add.question');
      Route::post('add_to_paper', 'addToPaper')->name('paper.addtopaper');
      Route::get('view/{id}', 'view')->name('paper.view');
      Route::get('exam/{id}', 'exam')->name('paper.exam');
      Route::get('solution/{id}', 'solution')->name('paper.solution');
      Route::get('solution-ajax/{id}', 'solutionAjax')->name('paper.solution.ajax');
      Route::get('result/{id}', 'result')->name('paper.result');
      Route::get('result/csv/{id}', 'resultCsv')->name('paper.result.csv');
      Route::get('copy/{id}', 'copy')->name('paper.copy');
      Route::post('copy-store', 'copyStore')->name('paper.copy.store');
      Route::get('recheck_result/{id}', 'reCheckResult')->name('paper.recheck.result');
      Route::get('detach/{paperId}/{questionId}', 'detachQuestion')->name('paper.detach.question');
    });
    Route::get('paper-search', 'search')->name('paper.search');
	});

	Route::controller(BatchCtrl::class)->group(function()
	{
		Route::get('get_batches/{batch_id}', 'getBatches');
		Route::get('batch_send_sms/{id}', 'sendSMS')->name('batch.sms');
	});

	Route::controller(StudentCtrl::class)->group(function()
	{
		Route::get('add-students/{id}/{name}', 'addStudent')->name('students.add');
		Route::post('add-student-object', 'addStudentObject')->name('students.add.object');
		Route::get('add-student-complete', 'addStudentComplete')->name('students.add.complete');
		Route::get('student/view/{id}/{name}', 'view')->name('student.view');
		Route::get('student/remove/{id}/{name}/{obj}', 'remove')->name('student.remove');
		Route::get('student/send_verify/{id}', 'sendVerifyEmail')->name('send.verify.email');

		Route::post('student.search', 'search')->name('student.search');
		Route::get('student/login-by-id/{id}', 'loginById')->name('student.login-by-id');
	});

	Route::controller(SyllabusCtrl::class)->group(function()
	{
		Route::get('add-question/{syllabus}/{paper_id}', 'addQuestion')->name('syllabus.add.question');
		Route::post('syllabus/add_to_paper', 'addToPaper')->name('syllabus.addtopaper');
		Route::get('syllabus/view/{id}', 'view')->name('syllabus.view');
		Route::get('syllabus/pdf/{id}', 'pdf')->name('syllabus.pdf');
    Route::get('syllbus/copy/{id}', 'copy')->name('syllabus.copy');
    Route::post('syllbus/copy-store', 'copyStore')->name('syllabus.copy.store');
	});

	Route::controller(ExamCtrl::class)->group(function()
	{
		Route::get('/exam-live', 'live')->name('exam.live');
		Route::post('/exam/view', 'view')->name('exam.view');
		Route::get('/exam/paper/{exam_id}', 'paper')->name('exam.paper');
	});

	Route::controller(ComplainCtrl::class)->group(function()
	{
		Route::post('/complain-reply', 'reply')->name('complain.reply');
	});

	Route::controller(SmsCtrl::class)->group(function()
	{
		Route::post('sms/confirm', 'confirm')->name('sms.confirm');
		//this route for ajax call
		Route::get('sms/batch_student/{batch_ids}', 'getBatchStudents')->name('sms.batch.student');
	});

	Route::controller(EmailCtrl::class)->group(function()
	{
		Route::post('email/confirm', 'confirm')->name('email.confirm');
		//this route for ajax call
		Route::get('email/batch_student/{batch_ids}', 'getBatchStudents')->name('email.batch.student');
	});

	Route::controller(OrderCtrl::class)->group(function()
	{
		Route::get('order/add_to_batch/{id}', 'addToBatch')->name('student.addto.batch');
	});

  Route::controller(RoutineCtrl::class)->group(function()
  {
    //
  });

  Route::controller(RoutineItemCtrl::class)->group(function()
  {
    Route::get('routine/add-item/{id}', 'addItem')->name('routine.add-item');
    Route::post('routine/add-item-store', 'addItemPost')->name('routine.add-item.store');
    Route::get('routine/delete-item/{item_id}', 'deleteItem')->name('routine.item.delete');

    Route::get('routine/edit-item/{id}', 'editItem')->name('routine.item.edit');    
    Route::put('routine/item/update/{id}', 'update')->name('routine.item-update');
  });

  Route::controller(HomePageSettingCtrl::class)->group(function()
  {
    Route::get('about-us', 'aboutUs')->name('settings.about-us');
    Route::post('about-us/update', 'aboutUsUpdate')->name('about.update');
  });

  // database backup
  Route::get('/download-database', [BackupController::class, 'downloadDatabase'])->name('database.download');
	
});

// Route::controller(ScheduleCtrl::class)->group(function()
// {
//   Route::prefix('/schedule')->group(function()
//   {
//     Route::get('send-sms', 'sendSms');
//     Route::get('send-email', 'sendEmail');
//   });
// });

Route::get('create-log', function(){
  Log::info('test log');
  return 'working...';
});

Route::get('schedule-test', [HomePageCtrl::class, 'scheduleTest']);

// cache clear
Route::get('reboot', function ()
{
  Artisan::call('cache:clear');
  Artisan::call('view:clear');
  Artisan::call('route:clear');
  Artisan::call('config:cache');
  Artisan::call('view:cache');
  dd('System Successfully Rebooted');
});