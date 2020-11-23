<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileuploadController extends Controller
{
    private function uploadfile(Request $request) {
        $imageName = time().'.'.$request->image->getClientOriginalExtension();
        $request->image->move(public_path('images'), $imageName);   

    	return response()->json(['success'=>'You have successfully upload image.']);
    }
}
