<?php

namespace App\Http\Controllers;


use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\VerifyNumberRequest;
use App\Models\User;
use App\Models\Attempt;
use App\Models\Message;
use Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->user = new User();
        $this->attempt = new Attempt();
        $this->message = new Message();
        $this->allCodes = $this->user->select('code')->get();
    }

    public function index() {
        return view('register');
    }

    public function register(CreateUserRequest $request) {
        $data = $request->except('_token');
        $phone = preg_replace("/[^0-9]/", "", $data['phone']);
        try {
            $code = $this->generateCode();
            $user = $this->user->create([
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'phone' => $phone,
                'code' => $code,
                'attempt' => 0,
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $this->message->insert([
                'user_id' => $user->id,
                'content' => "Your verification code is: ${code}",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        } catch(\Exception $e) {
            Log::info($e->getMessage());
            return $this->badRequest();
        }

        return response([
            'status' => 'success',
            'data' => [
                'user_id' => $user->id,
                'start_counter' => (strtotime($user->created_at) + 60) > time() ? true : false,
                'message' => $user->messages->last(),
            ]
        ], 200);
    }

    public function sendNewCode(Request $request) {
        $data = $request->except('_token');
        if (!isset($data['user_id']) || !$this->authorizeToSendCode($data['user_id'])) {
            return $this->badRequest();
        }
        $user = $this->user->where('id', $data['user_id'])->first();

        if (!$user) {
            return $this->badRequest();
        }
        try {
            $code = $this->generateCode();
            $this->user->where('id', $data['user_id'])->update([
                'code' => $code,
                'attempt' => 0,
                'updated_at' => Carbon::now(),
            ]);
            $this->message->insert([
                'user_id' => $user->id,
                'content' => "Your verification code is: ${code}",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        } catch(\Exception $e) {
            Log::info($e->getMessage());
            return $this->badRequest();
        }

        return response([
            'status' => 'success',
            'data' => [
                'start_counter' => true,
                'message' => $user->messages->last(),
            ]
        ], 200);
    }

    public function verifyPhoneNumber(VerifyNumberRequest $request) {
        $data = $request->except('_token');
        $errors = [];
        $user = $this->user->where('id', $data['user_id'])->first();
        if ($user->attempt >= 3) {
            return $this->badRequest();
        }

        if ($user->status) {
            return response([
                'status' => 'success',
                'message' => [
                    'content' => 'Your account is already activated.',
                    'type' => 'success'
                ],
            ], 200);
        }
        try {
            $this->attempt->insert([
                'user_id' => $user->id,
                'code' => $data['code'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            if ((int) $user->code === (int) $data['code']) {
                $this->user->where('id', $user->id)->update([
                    'attempt' => 0,
                    'status' => 1,
                    'updated_at' => Carbon::now()
                ]);
            } else {
                $this->user->where('id', $user->id)->update([
                    'attempt' => DB::raw('attempt + 1'),
                    'updated_at' => Carbon::now(),
                ]);
                $errors['code'] = "Your code is inccorect";
            }
        } catch(\Exception $e) {
            Log::info($e->getMessage());
            return $this->badRequest();
        }

        $user = $this->user->where('id', $user->id)->first();
        $message = [];
        $blockForm = false;
        if ($user->status) {
            $message = [
                'content' => 'Welcome to SMSBump!',
                'type' => 'success',
            ];
            $this->message->insert([
                'user_id' => $user->id,
                'content' => 'Welcome to SMSBump!',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } else if ($user->attempt >=3) {
            $message = [
                'content' => 'You have been blocked for too many attempts. Try again after a minute',
                'type' => 'danger',
            ];
            $blockForm = true;
        }

        return response([
            'status' => 'success',
            'message' => $message,
            'errors' => $errors,
            'block_form' => $blockForm
        ], 200);
    }

    public function setAttemptsToZero(Request $request) {
        $data = $request->except('_token');

        if (!isset($data['user_id'])) {
            return $this->badRequest();
        }

        try {
            $this->user->where('id', $data['user_id'])->update([
                'attempt' => 0,
                'updated_at' => Carbon::now()
            ]);
        } catch(\Exception $e) {
            Log::info($e->getMessage());
            return $this->badRequest();
        }

        return response([
            'status' => 'success',
        ], 200);
    }

    private function generateCode() {
        $newCode = rand(100000, 999999);
        foreach($this->allCodes as $item) {
            if ($newCode === (int) $item->code) {
                $this->generateCode();
                break;
            }
        }

        return $newCode;

    }

    private function badRequest() {
        return response([
            'status' => 'failed',
            'message' => 'Something went wrong. Please contact with us',
        ], 400);
    }

    private function authorizeToSendCode($userId) {
        $user = $this->user->where('id', $userId)->first();

        if (!$user) {
            return false;
        }
        $lastUpdatedDate = Carbon::parse($user->updated_at)->timestamp;
        if (($lastUpdatedDate + 60) > Carbon::now()->timestamp) {

            return false;
        }
        return true;
    }
}
