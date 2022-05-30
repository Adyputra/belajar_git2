<?php
defined('BASEPATH') or exit('No direct script access allowed');
include APPPATH . 'third_party/Midtrans/Midtrans.php';

class Payment extends CI_Controller
{

    private function _initMidtrans()
    {
        // Set your Merchant Server Key
        \Midtrans\Config::$serverKey = 'SB-Mid-server-t-6CLEHQuJuLc5J-7l6aASBa';
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        \Midtrans\Config::$isProduction = false;
        // Set sanitization on (default)
        \Midtrans\Config::$isSanitized = true;
        // Set 3DS transaction for credit card to true
        \Midtrans\Config::$is3ds = true;
    }

    public function checkout()
    {

        $this->_initMidtrans();
        $name = $this->input->post('nama');
        $email = $this->input->post('email');
        $phone = $this->input->post('phone');
        $alamat = $this->input->post('alamat');
        $kabkota = $this->input->post('kabkota');
        $provinsi = $this->input->post('provinsi');
        $ongkos = $this->input->post('ongkir');
        $notes = $this->input->post('note');

        $items = [];
        foreach ($this->cart->contents() as $cart) {
            $item = [
                'id' => $cart['id'],
                'price' => $cart['price'],
                'quantity' => $cart['qty'],
                'name' => $cart['name']
            ];
            array_push($items, $item);
        }

        $ongkir = [
            'id' => 'ONGKIR',
            'price' => $ongkos,
            'quantity' => 1,
            'name' => 'Ongkos Kirim JNE'
        ];
        array_push($items, $ongkir);

        $customer = [
            'first_name' => $name,
            'email' => $email,
            'phone' => $phone,
            'shipping_address' => [
                'first_name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $alamat,
                'city' => $kabkota
            ]
        ];

        $payments = [
            'transaction_details' => [
                'order_id' => rand(),
            ],
            'item_details' => $items,
            'customer_details' => $customer,
            'callbacks' => [
                'finish' => base_url('Payment/finish')
            ],
            'expiry' => [
                'start_time' => date('Y-m-d H:i:s T'),
                'unit' => 'minutes',
                'duration' => '15'
            ],
        ];

        $data_transaksi = [
            'customer' => $customer,
            'produk' => $items,
            'provinsi' => $provinsi,
            'note' => $notes
        ];
        $this->session->set_userdata('transaksi', $data_transaksi);

        # Snap Redirect
        try {
            // Get Snap Payment Page URL
            $paymentUrl = \Midtrans\Snap::createTransaction($payments)->redirect_url;
            //  detail transaksi

            // Redirect to Snap Payment Page
            header('Location: ' . $paymentUrl);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function finish()
    {
        $data = $this->session->userdata('transaksi');
        $nama = $data['customer']['first_name'];
        $provinsi = $data['provinsi'];
        $kabupaten = $data['customer']['shipping_address']['city'];
        $jalan = $data['customer']['shipping_address']['address'];
        $nomer = $data['customer']['phone'];
        $email = $data['customer']['email'];
        $note = $data['note'];
        $jumlah = $this->cart->total();
        $tgl_pesan = date('Y-m-d H:i:s');
        $data = [
            'nama' => $nama,
            'provinsi' => $provinsi,
            'kabupaten' => $kabupaten,
            'jalan' => $jalan,
            'nomer' => $nomer,
            'email' => $email,
            'note' => $note,
            'jumlah' => $jumlah,
            'tgl_pesan' => $tgl_pesan
        ];
        $this->db->insert('tb_invoice', $data);
        $this->cart->destroy();
        $this->session->set_flashdata('transaksi_sukses', 'Transaksi Sudah Selesai');
        redirect('Main');
    }
}
