<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;

Route::view('/', 'welcome');

Route::get('/dashboard',function(){
    $user_id = auth()->user()->id;
    $users = User::where('id', '!=', $user_id)->get();

    return view('dashboard',['users'=> $users]);
    
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/chat/{id}',function($id){
    return view('chat',['id'=> $id]);
    
})->middleware(['auth', 'verified'])->name('chat');

// Route::view('dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
