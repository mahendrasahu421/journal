<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Event\TestRunner\ExecutionAborted;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use App\Library\sHelper;


class ReviewerController extends Controller
{
    public function index()
    {
        $data['title'] = 'Reviewer | Create';
        
        return view('admin.reviewer.create')->with($data);
    }

    public function store(Request $request)
    {

        $request->validate(
            [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'mobile' => 'required|digits:10|unique:users',
                'email' => 'required|string|email|max:255|unique:users',
                'degree' => 'required|string',
                'institution' => 'required|string',
                'position' => 'required|string',
                'department' => 'required|string',
                'reason' => 'required|string',
                'password' => 'required',
                'user_status' => 'required|string',
            ],
            [
                // Custom error messages
                'first_name.required' => 'First name is required !!!',
                'last_name.required' => 'Last name is required !!!',
                'mobile.required' => 'Mobile number is required !!!',
                'mobile.digits' => 'Mobile number must be 10 digits !!!',
                'mobile.unique' => 'This mobile number is already registered !!!',
                'email.required' => 'Email field is required !!!',
                'email.email' => 'Please enter a valid email address !!!',
                'email.unique' => 'This email is already registered !!!',
                'degree.required' => 'User Degree field is required !!!',
                'institution.required' => 'Institution field is required !!!',
                'position.required' => 'Position field is required !!!',
                'department.required' => 'Department field is required !!!',
                'reason.required' => 'Reason field is required !!!',
                'user_type.required' => 'User type is required !!!',
                'password.required' => 'Password is required !!!',
                'password.min' => 'Password must be at least 8 characters !!!',
                'user_status.required' => 'User status is required !!!',
            ]
        );

        try {
            $userId = sHelper::fetchNewUserId();
          

            $user = new User([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'degree' => $request->degree,
                'institution' => $request->institution,
                'position' => $request->position,
                'department' => $request->department,
                'reason' => $request->reason,
                'user_type' => 'reviewer',
                'password' => Hash::make($request->password),
                'user_status' => $request->user_status,
                'term_and_condition' => 1,
                'userId' => $userId,
            ]);

            if ($user->save()) {

                //assigned roles
                $roles = Role::where('slug','reviewer')->get();
                $user->assignRole($roles);
                
                return redirect('/admin/reviewers')->with(["msg"=>"<div class='callout callout-success'><strong>Success </strong> Reviewer addedd Successfully !!! </div>" ]);  
            }


        } catch (\Exception $e) {
            // Log the exception
            $e->getMessage();

            // Redirect back with an error message
            return redirect()->back()->with(["msg"=>"<div class='callout callout-danger'><strong>Wrong </strong> Something went wrong, please try again!!! </div>" ]);  
        }
    }



    public function show()
    {
        $data['title'] = 'Reviewer | List';
        return view('admin.reviewer.list')->with($data);
    }

    public function edit(Request $request, $id)
    {
        $data['title'] = 'Reviewer | Edit';
        $data['reviwer'] = User::with('roles')->find($id);
        $data['roles'] = Role::all();
      
        return view('admin.reviewer.edit')->with($data);
    }

    public function update(Request $request, $id)
    {
        $data['title'] = 'Reviewer | update';
        $user = User::find($id);
        if($user != NULL) {
            $user->first_name = $request->first_name ?? '';
            $user->last_name = $request->last_name ?? '';
            $user->email = $request->email ?? '';
            $user->mobile = $request->mobile ?? '';
            $user->user_status = $request->user_status ?? '';
            $user->degree = $request->degree ?? '';
            $user->institution = $request->institution ?? '';
            $user->position = $request->position ?? '';
            $user->department = $request->department ?? '';
            $user->reason = $request->reason ?? '';

            $user->save(); 

            $roleIdArr = $request->roles;
    
            //unassigned, assigned roles
            $assignedRoles = $user->roles;
            if($assignedRoles->count() > 0) {
                foreach($assignedRoles as $assignedRole) {
                    $user->removeRole($assignedRole);
                }
            }

            //unassigned, assigned roles
            if($user != NULL) {
                if(count($roleIdArr) > 0) {
                    //assigned, assigned roles
                    $roles = Role::whereIn('id', $roleIdArr)->get();
                    $user->assignRole($roles);
                }
            }
    
            return redirect()->back()->with(["msg"=>"<div class='callout callout-success'><strong>Success </strong>  Record update Successfully  !!! </div>"]); 
        } else{
            return redirect()->back()->with(["msg"=>"<div class='callout callout-danger'><strong>Info </strong>  Something went wrong, please try again.  !!! </div>"]); 
        }
    }

    public function list(Request $request)
    {
        $limit = request()->input('length');
        $start = request()->input('start');
        $totalRecord = User::count();

        $usersQuery = User::query();
        $users = $usersQuery->skip($start)->take($limit)->get();

        $row = [];
        if ($users->count() > 0) {
            $i = 1;
            foreach ($users as $user) {
                $change_credential = NULL;
                $edit_btn = '<a href="' . url("admin/reviewers/edit/" . $user->id) . '" data-toggle="tooltip" title="Edit Record" class="btn btn-primary" style="margin-right: 5px;">
						<i class="fas fa-edit"></i> 
					  </a>';

                //if(Auth::user()->isAbleTo('change-user-credential')){
                $change_credential = '<a href="' . url("admin/edit_credential/" . $user->id) . '" data-toggle="tooltip" title="Edit Record" class="btn btn-success" style="margin-right: 5px;">
						<i class="fas fa-key"></i> 
					  </a>';
                //}
                $row = [];
                $row['sn'] = '<a href="' . url("admin/reviewers/edit/$user->id") . '">' . $user->userId . '</a>';;

                $row['name'] = $user->first_name;
                $row['email'] = $user->email;
                $row['mobile'] = $user->mobile;
                $row['user_type'] = $user->user_type;

                $row['action'] = $edit_btn . " " . $change_credential;

                $rows[] = $row;
            }
        }

        $json_data = array(
            "draw"            => intval(request()->input('draw')),
            "recordsTotal"    => intval($totalRecord),
            "recordsFiltered" => intval($totalRecord),
            "data"            => $rows
        );
        // echo "<pre>";
        // print_r($json_data);exit;
        return json_encode($json_data);
        exit;
    }
}
