<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Token;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', function (Request $request) {
    try {
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password'))
        ]);

        $token = JWTAuth::fromUser($user);

        // $user->save();

        return response()->json(['user' => $user, 'token' => $token], 201);
    } catch (\Exception $e) {
        return response()->json(['message' => 'An error occured'], 500);
    }
});

Route::post('/login', function(Request $request) {
    try {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'user' => $user,
                'token' => $token,
            ], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    } catch (\Exception $e) {
        return response()->json(['message' => 'An error occured'], 500);
    }
});

Route::get('/products', function(Request $request) {
    try {
        $products = Product::with('category')->get();
        return response()->json($products, 200);

    } catch (\Exception $e) {
        return response()->json(['message' => 'An error occured'], 500);
    }
});

Route::get('/orders', function(Request $request) {
    try {
        // $tokenString = $request->header('Authorization');
        // $tokenString = Str::substr($tokenString, 7);

        // $token = new Token($tokenString);
        // $decodedToken = JWTAuth::decode($token);
        // $userId = $decodedToken['sub'];

        $orders = Order::where('user_id', 1)->with('product')->get();
        $orderList = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'transaction_reference' => $order->transaction_reference,
                'created_at' => $order->created_at,
                'product_name' => $order->product->name,
                'amount_paid' => $order->product->amount,
            ];
        });

        return response()->json($orderList);
    } catch (\Exception $e) {
        return response()->json(['message' => 'An error occured'], 500);
    }
});

// public function decodeToken()
// {
//     $token = JWTAuth::getToken();
//     $decodedToken = JWTAuth::decode($token);

//     // You can now access the decoded token data
//     // For example, to get the token payload:
//     $payload = $decodedToken->getPayload();

//     // Alternatively, you can get a specific claim value:
//     $userId = $decodedToken->getClaim('sub');
// }
