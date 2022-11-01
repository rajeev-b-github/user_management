<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiResponseController;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;


class UserController extends Controller
{
    /**
     * Register the specified resource to database.
     *
     * @param  int  $id and other registration fields
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterRequest $req)
    {
        try {
            $input = $req->all();
            $input['password'] = bcrypt($input['password']);

            $user = User::create($input);

            $responseArray = [
                'Id' => $user->id,
                'name' => $user->name,
                'user_type' => $user->user_type,
                'email' => $user->email,
                'token' => $user->createToken('register-token')->accessToken,
                'message' => 'User Created Successfully',
            ];
            return response()->json($responseArray, 200);
        } catch (\Exception $ex) {

            return response()->json($ex->getMessage(),);
        }
    }
    /**
     * Login the specified resource.
     *
     * @param  email and password
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $req)
    {

        try {
            if (
                !Auth::attempt([
                    'email' => $req->email,
                    'password' => $req->password,
                ])
            ) {
                return
                    ApiResponseController::responseFailed('Username or Password incorrect');
            }
            if (auth()->user()->is_approved == 1) {
                $responseArray = [
                    'Id' => auth()->user()->id,
                    'user_type' => auth()->user()->user_type,
                    'name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'token' => auth()
                        ->user()
                        ->createToken('login-token')->accessToken,
                ];
                $response = ApiResponseController::responseSuccess('User Logged in Successfully', $responseArray);
            } else {
                $response = ApiResponseController::responseFailed('Login Failed :: Your not able to login until your profile is approved, please contact admin!!');
            }
        } catch (\Throwable $th) {
            $response = ApiResponseController::responseServerError($th->getMessage());
        }
        return $response;
    }

    /**
     * Logout the given resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        try {
            $response[] = "";
            auth()->user()->token()->revoke();
            $response = ApiResponseController::responseSuccess('User Logged out Successfully');
        } catch (\Throwable $th) {
            $response = ApiResponseController::responseServerError($th->getMessage());
        }
        return $response;
    }
}
