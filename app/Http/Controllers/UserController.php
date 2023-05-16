<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\Api as ApiHelper;
use App\Traits\ApiController;
use App\Http\Resources\Data;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use JWTAuth;

class UserController extends Controller
{
    use ApiController;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $resource = ApiHelper::resource();

        try{

            $users = new User;
           
            if ($request->filter_like) {
            
                $filter_like = $request->filter_like;
            
                $users = $users->where(function($query)use($filter_like){
                    $query->orWhere('first_name','LIKE','%'.$filter_like.'%')
                        ->orWhere('last_name','LIKE','%'.$filter_like.'%')
                        ->orWhere('email','LIKE','%'.$filter_like.'%')
                        ->orWhere('user_name','LIKE','%'.$filter_like.'%');
                });
            }
            
            $users = $users->paginate(5);

            $data  =  new Data($users);
            $resource = array_merge($resource, $data->toArray($request));
          ApiHelper::success($resource);
        }catch(\Exception $e){
          ApiHelper::setException($resource, $e);
        }

        return $this->sendResponse($resource);
    }


    public function getDrivers(Request $request)
    {
        $resource = ApiHelper::resource();

        try{

            $users = User::where('is_admin',false)->get();
            
            $data  =  new Data($users);
            $resource = array_merge($resource, $data->toArray($request));
          ApiHelper::success($resource);
        }catch(\Exception $e){
          ApiHelper::setException($resource, $e);
        }

        return $this->sendResponse($resource);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $resource = ApiHelper::resource();

        try{

            $validator= \Validator::make($request->all(),[
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                //'user_name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|confirmed',
            ]);

            if($validator->fails()){
                ApiHelper::setError($resource, 0, 422, $validator->errors());
                return $this->sendResponse($resource);
            }

            $user = new User;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->user_name = $request->user_name;
            $user->email = $request->email;
            $user->is_admin = 0;
            $user->password = Hash::make($request->password);
            $user->save();
            
            if ($request->login) {
              
              $credentials = [
                'email' => $user->email,
                'password' => $request->password
              ];
              
              $token = JWTAuth::attempt($credentials);
              
              return $this->respondWithToken($token,$user);
            }

            $data  =  new Data($user);
            $resource = array_merge($resource, $data->toArray($request));
          ApiHelper::success($resource);
        }catch(\Exception $e){
          ApiHelper::setException($resource, $e);
        }

        return $this->sendResponse($resource);
    }

     protected function respondWithToken(string $token,$user)
    {
        return response()->json([
            'access_token' => $token,
            'user' => $user,
            'token_type' => 'bearer',
            //'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
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
        $resource = ApiHelper::resource();

        try{

            $validator= \Validator::make($request->all(),[
                'first_name' => 'nullable|string|min:3|max:255',
                'last_name' => 'nullable|string|min:3|max:255',
                //'user_name' => 'required|string|max:255',
                'email' => 'nullable|email|min:8|max:255|unique:users,email,'.$id,
                'password' => 'nullable|confirmed',
            ]);

            if($validator->fails()){
                ApiHelper::setError($resource, 0, 422, $validator->errors());
                return $this->sendResponse($resource);
            }

            $user = User::where('id',$id)->first();
            $user->first_name = $request->first_name ?? $user->first_name;
            $user->last_name = $request->last_name ?? $user->last_name;
            $user->user_name = $request->user_name ?? $user->user_name;
            $user->email = $request->email ?? $user->email;
            $user->password = !is_null($request->password) ? Hash::make($request->password) : $user->password;
            $user->save();

            $data  =  new Data($user);
            $resource = array_merge($resource, $data->toArray($request));
          ApiHelper::success($resource);
        }catch(\Exception $e){
          ApiHelper::setException($resource, $e);
        }

        return $this->sendResponse($resource);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $resource = ApiHelper::resource();

        try{

            $user = User::where('id',$id)->update(['is_active' => false]);

            $resource = array_merge($resource, ['data' => []]);
          ApiHelper::success($resource);
        }catch(\Exception $e){
          ApiHelper::setException($resource, $e);
        }

        return $this->sendResponse($resource);
    }

    public function active($id)
    {
        $resource = ApiHelper::resource();

        try{

            $user = User::where('id',$id)->update(['is_active' => true]);

            $resource = array_merge($resource, ['data' => []]);
          ApiHelper::success($resource);
        }catch(\Exception $e){
          ApiHelper::setException($resource, $e);
        }

        return $this->sendResponse($resource);
    }
}
