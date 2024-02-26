<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Exceptions\UrlSignatureException;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

use App\Mail\VerifyCodeAdmin;
use App\Jobs\MailJob;
use Inertia\Inertia;

class AuthVerifyUserController extends Controller
{
    /**
     * Ruta Firmada que realiza la
     * Activacion de la cuenta de un nuevo usuario
     */
    public function activeAccount (string $id)
    {
        $user = User::find($id);
        if($user){
            $user->status = true;
            $user->save();
            return response()->json([
                'message' => 'Cuenta activada correctamente',
                'status' => 200,
            ]);
        }else{
            return response()->json([
                'messageError' => 'Error de activacion de cuenta, usuario no encontrado',
                'status' => 403,
            ]);
        }
    }

    /**
     * Realiza el envio del codigo de verificacion del 2FA 
     */
    public function sendVerifyCode (Request $request, string $id)
    {
        try{
            $user = User::find($id);
            $urlSigned = URL::temporarySignedRoute(
                'verify.code',
                now()->addMinutes(30),
                ['id' => $user->id]
            );
            if($user && $user->code == null){
                $user->code = strval(rand(1000, 9999));
                $user->save();
                MailJob::dispatch(new VerifyCodeAdmin($user),$user)->onQueue('high');
            }
            return Inertia::render('Code',[
                'singurl' => $urlSigned,
                'msg'=>'Ingrese el codigo enviado al correo'
            ]);
        }
        catch (QueryException $e) {
            Log::channel('slack')->error($e->getMessage());
            return redirect()->back()->withErrors([
                'messageError' => 'Error interno del servidor. Por favor, inténtelo de nuevo más tarde.',
                'status'=> 500
                ])->withInput();
        } catch (PDOException $e) {
            Log::error('Error de PDO: ' . $e->getMessage());
            return Redirect::back()->withErrors(['messageError' => 'Error interno del servidor. Por favor, inténtelo de nuevo más tarde.']);
        }
    }
    
    /**
     * Verificacion del codigo del 2FA
     */
    public function verifyCode (Request $request, string $id)
    {
        try{
            $validate = $request->validate(
                ['code' => 'required|string|min:4|max:4'],
                [
                    'required' => 'El campo :attribute es requerido.',
                    'max' => [
                        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
                    ],
                    'min' => [
                        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
                    ],
                ]
            );
            $user = User::find($id);
            if(!$user) 
                return response()->json(['message' => 'Usuario no encontrado','status' => 401,], 401);
            if(!Hash::check($request->code, $user->code))
                return response()->json(['message' => 'Codigo incorrecto','status' => 401,], 401);
            Log::channel('slack')->info('Inicio sesion ' . $user->email);
            $user->code = null;
            $user->save();
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
            ], 500);
        } catch (PDOException $e) {
            Log::error('Error de PDO: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error interno del servidor. Por favor, inténtelo de nuevo más tarde.',
                'status'=> 500
            ], 500);
        }catch(ValidationException $e){
            return response()->json([
                'messageError' => 'Error de validacion',
                'status' => 401,
                'errors' => $e->errors()
            ], 401);
        }
        catch (\Exception $e) {
            Log::channel('slack')->error($e->getMessage());
            return response()->json([
                'message' => 'Error interno del servidor. Por favor, inténtelo de nuevo más tarde.',
                'status'=> 500,
                'error' => $e
            ], 500);
        }
    }
}
