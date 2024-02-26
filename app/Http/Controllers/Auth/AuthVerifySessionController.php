<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

use App\Http\Requests\UserRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

use App\Mail\VerifyAccount;
use App\Mail\VerifyCodeAdmin;
use App\Mail\TimOutCodeVerify;
use App\Jobs\MailJob;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;

class AuthVerifySessionController extends Controller
{
    /**
     * Mostrar la vista del Registro
     */
    public function registerView() {
        if(!auth()->user()){
            return Inertia::render('Auth/Register');
        }
        return redirect('/dashboard');
    }
    
    /**
     * Crea un nuevo registro de usuario
     */
    public function register(UserRequest $request) {

        try {
            $data = $request->json()->all();
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);
            MailJob::dispatch(new VerifyAccount($user),$user)->onQueue('default');
            Log::channel('slak')->info('Se registro ' . $user->email);

            return response()->json([
                'message' => 'Se ha enviado un correo de verificación a su cuenta.',
                'status' => 200,
            ]);
            
        } catch (QueryException $e) {
            Log::channel('slack')->error($e->getMessage());
            return response()->json([
                'message' => 'Error interno del servidor. Por favor, inténtelo de nuevo más tarde.',
                'status'=> 500
            ]);
        } catch (PDOException $e) {
            Log::error('Error de PDO: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error interno del servidor. Por favor, inténtelo de nuevo más tarde.',
                'status'=> 500
            ]);
        }
    }

    /**
     * Mostrar la vista del Inicio de Sesion
     */
    public function loginView() {
        if(!auth()->user()){
            return Inertia::render('Auth/Login', [
                'status' => session('status'),
            ]);
        }
        return redirect('/dashboard');
    }

    /**
     * Verifica y crea una nueva sesion autenticada del usuario
     */
    public function login(LoginRequest $request){
        
        try{
            $user = User::where('email', $request->email)->first();
            if(!$user || Hash::check($request->password, $user->password) === false){
                Log::channel('slack')->warning('Se intento inciar sesion con la cuenta de ' . $request->email. '');
                return response()->json([
                    'message' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
                    'status'=> 401
                ], 401);
            }
            if($user->status === 0) return response()->json([
                    'message' => 'Su cuenta no ha sido verificada. Por favor, revise su correo electrónico.',
                    'status'=> 401
                ], 401);
            if($user->rol === 1){
                $timeout = now()->addMinutes(3);
                MailJob::dispatch(new VerifyCodeAdmin($user),$user)->onQueue('default');
                $urlSigned = URL::temporarySignedRoute(
                    'verify.code',
                    $timeout,
                    ['id' => $user->id]
                );
                // TimOutCodeVerify::dispatch($user)->delay($timeout)->onQueue('default');
                Log::channel('slack')->warning('Se intento inciar sesion con con la cuenta de Administrador de ' . $user->email. '');
                return response()->json([
                    'message' => 'Se envio un codigo de verificacion al correo '.$user->email,
                    'singurl' => parse_url($urlSigned, PHP_URL_QUERY),
                    'user_id' => $user->id,
                    'status'=> 403
                ], 403);
            }
            Log::channel('slack')->info('Inicio sesion ' . $user->email);
            return response()->json([
                'message' => 'Inicio de sesión exitoso.',
                'data' => [
                    'token' => $user->createToken('token')->plainTextToken,
                ],
                'status' => 200,
            ], 200);
        } catch (QueryException $e) {
            Log::channel('slack')->error($e->getMessage());
            return response()->json([
                'message' => 'Error interno del servidor. Por favor, inténtelo de nuevo más tarde.',
                'status'=> 500
            ],500);
        } catch (PDOException $e) {
            Log::error('Error de PDO: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error interno del servidor. Por favor, inténtelo de nuevo más tarde.',
                'status'=> 500
            ],500);
        }
    }


    /**
     * Destruye la sesion autenticada actual del usuario.
     */
    public function lgout(Request $request)
    {
        try{
            $user = $request->user();
            $user && $user->tokens()->delete();
            return [
                'message' => 'Sesión cerrada correctamente.',
                'status' => 200,
                'data' => $user
            ];
        }catch (QueryException $e) {
            Log::channel('slack')->error($e->getMessage());
            return response()->json([
                'message' => 'Error interno del servidor. Por favor, inténtelo de nuevo más tarde.',
                'status'=> 500
            ], 500);
        } catch (PDOException $e) {
            Log::error('Error de PDO: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error interno del servidor. Por favor, inténtelo de nuevo más tarde.',
                'status'=> 500
            ],  500);
        }catch (\Exception $e) {
            Log::error('Error de PDO: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error interno del servidor. Por favor, inténtelo de nuevo más tarde.',
                'error' => $e,
                'status'=> 500
            ], 500);
        }
    }
}
