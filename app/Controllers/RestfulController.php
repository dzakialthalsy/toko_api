<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class RestfulController extends ResourceController
{ 

    protected $format = 'json';

    protected function responseHasil($code, $status, $data)
    { 
        // Tambahkan header Access-Control agar API bisa diakses oleh Flutter
        return $this->response->setStatusCode($code)
                              ->setHeader('Access-Control-Allow-Origin', '*')
                              ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                              ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
                              ->setJSON([ 
                                  'code' => $code,
                                  'status' => $status,
                                  'data' => $data
                              ]);
    }
}