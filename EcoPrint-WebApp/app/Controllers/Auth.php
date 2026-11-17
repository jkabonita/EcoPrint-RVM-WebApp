<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RfidConModel;
use CodeIgniter\Controller;
use League\OAuth2\Client\Provider\Google;

class Auth extends BaseController
{
    protected $format = 'json';
    public $userModel;
    public $rfidConModel;
    public $rfidTag;
    public $session;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->rfidConModel = new RfidConModel();
        $this->session = session();
    }

    public function loginForm()
    {
        $session = session();

        $user = $session->get('role');
        if ($user === 'admin') return redirect()->to('admin');
        if ($user === 'print') return redirect()->to('print');
        if ($user === 'user') return redirect()->to('dashboard');

        return view('auth/login');
    }

    public function login()
    {
        $recaptcha = config('Recaptcha');

        if ($recaptcha->enabled) {
            $token = $this->request->getPost('g-recaptcha-response');
            
            if (!$token) {
                return redirect()->back()->with('error', 'reCAPTCHA verification required.');
            }

            if (!$this->verifyRecaptcha($token, $recaptcha->secretKey)) {
                return redirect()->back()->with('error', 'reCAPTCHA verification failed.');
            }
        }
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $username = trim($username);
        $password = trim($password);

        $user = $this->userModel->where('username', $username)->first();
        $user = $this->userModel->where('username', $username)->first();
        
        $dummyHash = '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
        if(!$user){
            password_verify($password, $dummyHash);
            return redirect()->back()->with('error', 'Email or Username not found');
        }

        if(!$user['password'] || $user['password'] == null){
            password_verify($password, $dummyHash);
            return redirect()->back()->with('error', 'Email found but no password was set, please try signing in with Google.');
        }

        if (!password_verify($password, $user['password'])) {
            return redirect()->back()->with('error', 'Incorrect password.');
        }

        session()->set('isLoggedIn', true);
        session()->set($user);

        if(($user['role'] == 'admin') || ($user['role'] == 'print') || ($user['role'] == 'user')){
            return redirect()->to('/');
        }
        else{
            return redirect()->back()->with('error', 'Unauthorized access');
        }
        
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }

    public function registerForm($rfid)
    {
        $data['rfid'] = $rfid;

        if(!isset($rfid) || empty($rfid)){
            return redirect()->to('/login')->with('error', 'RFID is required for registration.');
        }

        $this->session->set('rfid_tag', $rfid);

        return view('auth/register', $data);
    }

    public function googleLogin()
    {
        $provider = new Google([
            'clientId'     => env('GOOGLE_CLIENT_ID'),
            'clientSecret' => env('GOOGLE_CLIENT_SECRET'),
            'redirectUri'  => env('GOOGLE_REDIRECT_URI'),
        ]);

        $authUrl = $provider->getAuthorizationUrl();
        session()->set('oauth2state', $provider->getState());
        return redirect()->to($authUrl);
    }

    public function googleCallback()
    {
        $provider = new Google([
            'clientId'     => env('GOOGLE_CLIENT_ID'),
            'clientSecret' => env('GOOGLE_CLIENT_SECRET'),
            'redirectUri'  => env('GOOGLE_REDIRECT_URI'),
        ]);

        if ($this->request->getGet('state') !== session()->get('oauth2state')) {
            return redirect()->to('/login')->with('error', 'Invalid OAuth state.');
        }

        $token = $provider->getAccessToken('authorization_code', [
            'code' => $this->request->getGet('code')
        ]);

        $googleUser = $provider->getResourceOwner($token);
        $email = $googleUser->getEmail();
        $firstName = $googleUser->getFirstName();
        $lastName = $googleUser->getLastName();
        $rfidTag = $this->session->get('rfid_tag');
        
        if(!$this->isOrgEmail($email)){
           return redirect()->back()->with('error', 'Please use your CSPC Email.');
        }
        $user = $this->userModel->where('email', $email)->first();

        if(!$user && isset($rfidTag)){
            $newUser = [
                'rfid_tag' => $rfidTag,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ];

            $createUser = $this->userModel->insert($newUser);

            if(!$createUser){
                return redirect()->to('/login')->with('error', 'Failed to create user account.');
            }

            $user = $this->userModel->where('email', $email)->first();
        } else if ($user && isset($rfidTag)) {
            if($user['rfid_tag'] != $rfidTag){
                $this->userModel->update($user['user_id'], ['rfid_tag' => $rfidTag]);
                $user = $this->userModel->where('email', $email)->first();
            }
        } else if($user) {
            $user = $this->userModel->where('email', $email)->first();
        }else {
            return redirect()->to('/login')->with('error', 'No account associated with this Google email, please register first.');
        }
        $this->session->remove('rfid_tag');
        $this->session->set('isLoggedIn', true);
        $this->session->set($user);

        return redirect()->to('/dashboard');
    }

    private function verifyRecaptcha(string $token, string $secret): bool
    {
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $response = file_get_contents($url . '?secret=' . urlencode($secret) . '&response=' . urlencode($token));
        $result = json_decode($response, true);

        return isset($result['success']) && $result['success'] === true;
    }
    
    public function isOrgEmail($email, $allowedDomains = ['my.cspc.edu.ph', 'cspc.edu.ph'])
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
    
        $domain = strtolower(substr(strrchr($email, "@"), 1));
        return in_array($domain, array_map('strtolower', $allowedDomains));
    }

}

