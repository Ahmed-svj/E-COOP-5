<?php
/** @var Router $router */

// Public
$router->get('/', 'HomeController@index');
$router->get('/about', 'HomeController@about');
$router->get('/contact', 'HomeController@contact');
$router->get('/faq', 'HomeController@faq');
$router->get('/privacy', 'HomeController@privacy');
$router->post('/contact', 'HomeController@sendContact');
$router->get('/lang/{code}', 'HomeController@lang');
$router->post('/theme', 'HomeController@theme');

// Auth
$router->get('/login', 'AuthController@loginForm', ['guest']);
$router->post('/login', 'AuthController@login', ['guest']);
$router->post('/logout', 'AuthController@logout', ['auth']);
$router->get('/register/student', 'AuthController@studentForm', ['guest']);
$router->post('/register/student', 'AuthController@registerStudent', ['guest']);
$router->get('/register/company', 'AuthController@companyForm', ['guest']);
$router->get('/register/supervisor', 'AuthController@supervisorForm', ['guest']);
$router->post('/register/supervisor', 'AuthController@registerSupervisor', ['guest']);
$router->post('/register/company', 'AuthController@registerCompany', ['guest']);
$router->get('/forgot-password', 'AuthController@forgotForm', ['guest']);
$router->post('/forgot-password', 'AuthController@forgot', ['guest']);
$router->get('/reset-password', 'AuthController@resetForm', ['guest']);
$router->post('/reset-password', 'AuthController@reset', ['guest']);

// Shared (all roles)
$router->group('', ['auth'], function (Router $r) {
    $r->get('/dashboard', 'HomeController@dashboard');
    $r->get('/notifications', 'NotificationController@index');
    $r->get('/notifications/{id}/open', 'NotificationController@open');
    $r->post('/notifications/read-all', 'NotificationController@readAll');
    $r->get('/api/notifications/unread', 'NotificationController@unread');
    $r->get('/messages', 'MessageController@index');
    $r->post('/messages/start', 'MessageController@start');
    $r->get('/messages/{id}', 'MessageController@show');
    $r->post('/messages/{id}', 'MessageController@send');
    $r->get('/api/messages/{id}', 'MessageController@poll');
    $r->get('/settings', 'ProfileController@settings');
    $r->post('/settings', 'ProfileController@saveSettings');
    $r->post('/settings/password', 'ProfileController@changePassword');
    $r->post('/settings/email', 'ProfileController@changeEmail');
    $r->post('/settings/profile', 'ProfileController@saveProfile');
    $r->post('/settings/notifications', 'ProfileController@saveNotifications');
    $r->post('/settings/cv/remove', 'ProfileController@removeCv');
    $r->post('/settings/avatar', 'ProfileController@avatar');
    $r->get('/avatar/{id}', 'ProfileController@showAvatar');
    $r->get('/files/cv/{id}', 'ProfileController@downloadCv');
    $r->get('/files/report/{id}', 'ProfileController@downloadReport');
});

// Student
$router->group('/student', ['role:Student'], function (Router $r) {
    $r->get('/dashboard', 'Student\DashboardController@index');
    $r->get('/profile', 'Student\ProfileController@edit');
    $r->post('/profile', 'Student\ProfileController@update');
    $r->get('/opportunities', 'Student\OpportunityController@index');
    $r->get('/opportunities/{id}', 'Student\OpportunityController@show');
    $r->post('/opportunities/{id}/apply', 'Student\OpportunityController@apply');
    $r->get('/applications', 'Student\ApplicationController@index');
    $r->post('/applications/{id}/withdraw', 'Student\ApplicationController@withdraw');
    $r->get('/attendance', 'Student\AttendanceController@index');
    $r->post('/attendance', 'Student\AttendanceController@checkIn');
    $r->get('/report', 'Student\ReportController@index');
    $r->post('/report', 'Student\ReportController@upload');
    $r->get('/evaluations', 'Student\EvaluationController@index');
});

// Company
$router->group('/company', ['role:Company'], function (Router $r) {
    $r->get('/dashboard', 'Company\DashboardController@index');
    $r->get('/profile', 'Company\ProfileController@edit');
    $r->post('/profile', 'Company\ProfileController@update');
    $r->get('/opportunities', 'Company\OpportunityController@index');
    $r->get('/opportunities/create', 'Company\OpportunityController@create');
    $r->post('/opportunities', 'Company\OpportunityController@store');
    $r->get('/opportunities/{id}/edit', 'Company\OpportunityController@edit');
    $r->post('/opportunities/{id}', 'Company\OpportunityController@update');
    $r->post('/opportunities/{id}/close', 'Company\OpportunityController@close');
    $r->get('/applicants', 'Company\ApplicationController@index');
    $r->get('/applicants/{id}', 'Company\ApplicationController@show');
    $r->post('/applications/{id}/accept', 'Company\ApplicationController@accept');
    $r->post('/applications/{id}/reject', 'Company\ApplicationController@reject');
    $r->get('/daily-code', 'Company\DailyCodeController@index');
    $r->post('/daily-code', 'Company\DailyCodeController@generate');
    $r->get('/trainees', 'Company\EvaluationController@trainees');
    $r->get('/evaluate/{id}', 'Company\EvaluationController@form');
    $r->post('/evaluate/{id}', 'Company\EvaluationController@store');
});

// Academic Supervisor
$router->group('/supervisor', ['role:Supervisor'], function (Router $r) {
    $r->get('/dashboard', 'Supervisor\DashboardController@index');
    $r->get('/students', 'Supervisor\DashboardController@students');
    $r->get('/students/{id}', 'Supervisor\DashboardController@student');
    $r->get('/attendance', 'Supervisor\AttendanceController@index');
    $r->post('/attendance/{id}/approve', 'Supervisor\AttendanceController@approve');
    $r->post('/attendance/{id}/reject', 'Supervisor\AttendanceController@reject');
    $r->post('/attendance/approve-all', 'Supervisor\AttendanceController@approveAll');
    $r->post('/attendance/{id}/status', 'Supervisor\AttendanceController@change');
    $r->get('/reports', 'Supervisor\ReportController@index');
    $r->post('/reports/{id}/review', 'Supervisor\ReportController@review');
    $r->get('/evaluate/{id}', 'Supervisor\EvaluationController@form');
    $r->post('/evaluate/{id}', 'Supervisor\EvaluationController@store');
    $r->get('/export', 'Supervisor\ExportController@index');
    $r->get('/export/csv', 'Supervisor\ExportController@csv');
});

// Admin
$router->group('/admin', ['role:Admin'], function (Router $r) {
    $r->get('/dashboard', 'Admin\DashboardController@index');
    $r->get('/users', 'Admin\UserController@index');
    $r->post('/users/supervisor', 'Admin\UserController@createSupervisor');
    $r->post('/supervisors/{id}/approve', 'Admin\UserController@approveSupervisor');
    $r->post('/supervisors/{id}/reject', 'Admin\UserController@rejectSupervisor');
    $r->post('/users/{id}/toggle', 'Admin\UserController@toggle');
    $r->get('/users/{id}', 'Admin\UserController@show');
    $r->get('/placements', 'Admin\AssignmentController@placements');
    $r->get('/companies', 'Admin\CompanyController@index');
    $r->post('/companies/{id}/approve', 'Admin\CompanyController@approve');
    $r->post('/companies/{id}/reject', 'Admin\CompanyController@reject');
    $r->get('/opportunities', 'Admin\OpportunityController@index');
    $r->post('/opportunities/{id}/approve', 'Admin\OpportunityController@approve');
    $r->post('/opportunities/{id}/reject', 'Admin\OpportunityController@reject');
    $r->get('/assign', 'Admin\AssignmentController@index');
    $r->post('/assign', 'Admin\AssignmentController@assign');
    $r->post('/placements/{id}/complete', 'Admin\AssignmentController@complete');
    $r->get('/analytics', 'Admin\AnalyticsController@index');
    $r->get('/report', 'Admin\SystemReportController@index');
    $r->get('/report/csv', 'Admin\SystemReportController@csv');
    $r->get('/audit-logs', 'Admin\AuditLogController@index');
    $r->get('/contact-messages', 'Admin\ContactController@index');
});
