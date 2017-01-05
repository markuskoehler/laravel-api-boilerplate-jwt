<?php

namespace App\Api\V1\Controllers;

use Config;
use App\User;
use Exception;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\SignUpRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SignUpController extends Controller
{
    public function signUp(SignUpRequest $request, JWTAuth $JWTAuth)
    {
        $user = new User($request->all());
        if(!$user->save()) {
            throw new HttpException(500);
        }

        /*try {
            $user = new User($request->all());
            $user->save();
        } catch(Exception $ex) {
            throw new HttpException(500, $ex->getMessage());
        }*/

        if(!Config::get('boilerplate.sign_up.release_token')) {
            return response()->json([
                'status' => 'ok'
            ], 201);
        }

        $token = $JWTAuth->fromUser($user, ["username" => $user->name]);
        return response()->json([
            'status' => 'ok',
            'token' => $token
        ], 201);
    }
}
