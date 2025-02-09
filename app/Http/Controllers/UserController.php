<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function show($id)
    {
        return User::findOrFail($id);
    }

    public function index()
    {
        return User::where('users.username', '!=', 'admin')
            ->join('departments', 'users.department_id', '=', 'departments.id')
            ->join('users_status', 'users.status_id', '=', 'users_status.id')
            ->select('users.*', 'departments.name as department', 'users_status.name as status')->paginate();
    }

    public function info_create()
    {
        return [
            'departments' => DB::table('departments')->select('id as value', 'name as label')->get(),
            'statuses' => DB::table('users_status')->select('id as value', 'name as label')->get(),
        ];
    }

    public function store(Request $request)
    {
        $validation = $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users',
            'department_id' => 'required|exists:departments,id',
            'status_id' => 'required|exists:users_status,id',
            'username' => 'required|unique:users',
            'password' => 'required',
        ], [
            "*.required" => "Yêu cầu nhập trường này",
            "*.unique" => "Giá trị này đã tồn tại",
        ]);

        User::create($validation);

        // return User::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $validation = $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email,' . $id,
            'department_id' => 'required|exists:departments,id',
            'status_id' => 'required|exists:users_status,id',
            'username' => 'required|unique:users,username,' . $id,
        ], [
            "*.required" => "Yêu cầu nhập trường này",
            "*.unique" => "Giá trị này đã tồn tại",
        ]);

        User::findOrFail($id)->update($validation);

        if ($request->password) {
            User::findOrFail($id)->update([
                'change_password_at' => now(),
            ]);
        }
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
    }
}
