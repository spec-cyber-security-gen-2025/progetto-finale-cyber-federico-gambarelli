<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function profileView(){
        $user = Auth::user();
        return view('profile', compact('user'));
    }

    public function profileEdit(Request $request){
        // dd('ciao');
        // $user = Auth::user();
        $validatedData = $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:50',
        ]);
        $user = Auth::user();

        $user->update($validatedData);




        return redirect()->route('homepage')->with('message', 'Profile updated');
    }
}
