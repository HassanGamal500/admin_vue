<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
Use Image;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return User::latest()->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:80',
            'email' => 'required|email|string|max:200|unique:users',
            'password' => 'required|string|min:6'
        ]);



        return User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
    }

    public function profile()
    {
        return auth('api')->user();
    }

    public function updateProfile(Request $request) 
    {
        $user = auth('api')->user();

        $this->validate($request, [
            'name' => 'required|string|max:80',
            'email' => 'required|email|string|max:200|unique:users,email,'.$user->id,
            'password' => 'sometimes|min:6',
            'photo' => 'required'
        ]);

        $curentPhoto = $user->photo;
        
        if($request->photo != $curentPhoto){
            $name = time(). '.' .explode('/', explode(':', substr($request->photo, 0, strpos($request->photo, ';')))[1])[1];
            Image::make($request->photo)->save(public_path('img/profile/').$name);
            // $request->merge(['photo' => $name]);
            $user->update(['photo' => $name]);
            $userPhoto = public_path('img/profile/').$curentPhoto;
            if(file_exists($userPhoto)){
                @unlink($userPhoto);
            }
        }

        // dd($name);

        if($request->password){
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
        } else {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);
        }
        // $user->update($request->all());
        return $user;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $this->validate($request, [
            'name' => 'required|string|max:80',
            'email' => 'required|email|string|max:200|unique:users,email,'.$user->id,
            'password' => 'sometimes|min:6'
        ]);

        if($request->password){
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);
        } else {
            $user->update([
                'name' => $request->name,
                'email' => $request->email
            ]);
        }
        
        

        return ['message' => 'Updated The User Info'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $user->delete();
    }

    public function search()
    {
        if($search = \Request::get('q')) {
            $user = User::where(function($query) use ($search){
                $query->where('name', 'LIKE', "%$search%")->orWhere('email', 'LIKE', "%$search%");
            })->get();
        } else {
            $user = User::all();
        }
        return $user;
    }
}
