<?php

namespace App\Controllers;

use App\Models\MLogin;
use App\Models\MMember;
use App\Models\MProduk;
use Config\Database;

class ProdukController extends RestfulController 
{ 
    # Membuat fungsi create produk
    public function create() {
        if ($response = $this->wajibPengelola()) {
            return $response;
        }

        $data = [
            'kode_produk' => $this->request->getVar('kode_produk'),
            'nama_produk' => $this->request->getVar('nama_produk'),
            'harga' => $this->request->getVar('harga'),
            'gambar' => $this->request->getVar('gambar'),
            'persediaan' => $this->request->getVar('persediaan') ?? 0
        ];

        $model = new MProduk();
        $model->insert($data);
        $produk = $model->find($model->getInsertID());
        return $this->responseHasil(200, true, $produk);
    }

    # Membuat fungsi list produk
    public function list() { 
        $model = new MProduk();
        $produk = $model->findAll();
        return $this->responseHasil(200, true, $produk);
    }

    # Mmembuat fungsi detail produk
    public function detail($id) {
        $model = new MProduk();
        $produk = $model->find($id);
        return $this->responseHasil(200, true, $produk);
    }

    # Membuat fungsi ubah produk
    public function ubah($id) { 
        if ($response = $this->wajibPengelola()) {
            return $response;
        }

        $data = [ 
            'kode_produk' => $this->request->getVar('kode_produk'),
            'nama_produk' => $this->request->getVar('nama_produk'),
            'harga' => $this->request->getVar('harga'),
            'gambar' => $this->request->getVar('gambar'),
            'persediaan' => $this->request->getVar('persediaan') ?? 0
        ];

        $model = new MProduk();
        $model->update($id, $data);
        $produk = $model->find($id);

        return $this->responseHasil(200, true, $produk);
    }

    #Membuat fungsi hapus produk
    public function hapus($id) { 
        if ($response = $this->wajibPengelola()) {
            return $response;
        }

        $model = new MProduk();
        $produk = $model->delete($id);

        return $this->responseHasil(200, true, $produk);
    }

    # Membuat fungsi beli produk
    public function beli($id)
    {
        if ($response = $this->wajibPembeli()) {
            return $response;
        }

        $db = Database::connect();
        $db->transBegin();

        $produk = $db->query(
            'SELECT * FROM produk WHERE id = ? FOR UPDATE',
            [$id]
        )->getRowArray();

        if (!$produk) {
            $db->transRollback();
            return $this->responseHasil(404, false, 'Produk tidak ditemukan');
        }

        $persediaan = (int) ($produk['persediaan'] ?? 0);
        if ($persediaan <= 0) {
            $db->transRollback();
            return $this->responseHasil(400, false, 'Stok produk habis');
        }

        $persediaanBaru = $persediaan - 1;
        $db->table('produk')
            ->where('id', $id)
            ->update(['persediaan' => $persediaanBaru]);

        if ($db->transStatus() === false) {
            $db->transRollback();
            return $this->responseHasil(500, false, 'Pembelian gagal diproses');
        }

        $db->transCommit();

        $model = new MProduk();
        $produk = $model->find($id);

        return $this->responseHasil(200, true, $produk);
    }

    private function wajibPengelola()
    {
        return $this->wajibRole('pengelola', 'Akses hanya untuk pengelola');
    }

    private function wajibPembeli()
    {
        return $this->wajibRole('pembeli', 'Pembelian hanya bisa dilakukan pembeli');
    }

    private function wajibRole($role, $pesan)
    {
        $member = $this->ambilMemberLogin();
        if (!$member) {
            return $this->responseHasil(401, false, 'Token tidak valid');
        }

        if (($member['role'] ?? 'pembeli') !== $role) {
            return $this->responseHasil(403, false, $pesan);
        }

        return null;
    }

    private function ambilMemberLogin()
    {
        $authorization = $this->request->getHeaderLine('Authorization');
        $token = null;

        if (preg_match('/Bearer\s+(.+)/', $authorization, $matches)) {
            $token = trim($matches[1]);
        }

        if (!$token) {
            return null;
        }

        $login = (new MLogin())->where('auth_key', $token)->first();
        if (!$login) {
            return null;
        }

        return (new MMember())->find($login['member_id']);
    }
}
