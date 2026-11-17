<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $session = session();
        $user = $session->get('user_id');
        $role = $session->get('role');
        if(!$user) {
            return redirect()->to('/login');
        }
        if($role === 'user') {
            return redirect()->to('/dashboard');
        }
        if($role === 'print') {
            return redirect()->to('/print');
        }
        if($role === 'admin') {
            return redirect()->to('/admin');
        }
    }
    
    public function phpInfo()
    {
        return view('info');
    }

    public function termsOfService()
    {
        return view('terms/termsOfService');
    }

    public function privacyPolicy()
    {
        return view('terms/privacyPolicy');
    }
}
