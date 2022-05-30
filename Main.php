<?php
defined('BASEPATH') or exit('No direct script access allowed');

use \Controllers\API;

class Main extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->load->model('model_barang', 'barang');
        $this->load->model('model_invoice', 'invoice');
    }


    public function index()
    {
        // var_dump($this->cart->contents());die;
        $data['barang'] = $this->barang->tampil_data()->result();
        $this->load->view('main/templates/header', $data);
        $this->load->view('main/templates/topbar');
        $this->load->view('main/index');
        $this->load->view('main/templates/footer');
    }
    public function pesanan()
    {
        // var_dump($this->cart->contents());die;
        $data['invoice'] = $this->invoice->tampil_data();
        $data['barang'] = $this->barang->tampil_data();
        $this->load->view('main/templates/header', $data);
        $this->load->view('main/templates/topbar');
        $this->load->view('main/pesanan');
        $this->load->view('main/templates/footer');
    }
    public function shopdetail($id_brg)
    {
        // var_dump($this->cart->contents());die;
        $data['barang'] = $this->barang->detail_brg($id_brg);
        $this->load->view('main/templates/header', $data);
        $this->load->view('main/templates/topbar');
        $this->load->view('main/shopdetail');
        $this->load->view('main/templates/footer');
    }
    public function cart()
    {
        is_logged_in();
        $this->load->view('main/templates/header');
        $this->load->view('main/templates/topbar');
        $this->load->view('main/cart');
        $this->load->view('main/templates/footer');
    }

    public function checkout()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => base_url('API/tampilProvinsi'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $provinsi = json_decode($response);
        }

        $data['provinsi'] = $provinsi->rajaongkir->results;
        // var_dump($data['provinsi']);die;
        $this->load->view('main/templates/header', $data);
        $this->load->view('main/templates/topbar');
        $this->load->view('main/checkout');
        $this->load->view('main/templates/footer');
    }

    public function shop()
    {
        $kategori = $this->input->get('kategori');
        $keyword = $this->input->post('keyword');
        if($keyword || $kategori){
            $data['barang'] = $this->barang->getBarangByKategori($kategori, $keyword);
        } else {
            $data['barang'] = $this->barang->getAllBarang();
        }
        $data['kategori'] = $this->barang->getKategori();
        $this->load->view('main/templates/header', $data);
        $this->load->view('main/templates/topbar');
        $this->load->view('main/shop');
        $this->load->view('main/templates/footer');
    }

    public function addCart()
    {
        is_logged_in();
        $id = $this->input->get('id');
        $qty = 1;
        $price = $this->input->get('price');
        $name = urldecode($this->input->get('name'));

        $data = [
            'id' => $id,
            'qty' => $qty,
            'price' => $price,
            'name' => $name
        ];
        $this->cart->insert($data);
        $this->session->set_flashdata('addCart', 'sukses ditambahkan ke keranjang');
        redirect('main');
    }

    public function bayar()
    {
        $data = array(
            'nama'          => $this->input->post('nama'),
            'provinsi'      => $this->input->post('provinsi'),
            'kabupaten'     => $this->input->post('kabupaten'),
            'jalan'         => $this->input->post('jalan'),
            'nomer'         => $this->input->post('nomer'),
            'email'         => $this->input->post('email'),
            'note'          => $this->input->post('note'),
        );

        $this->db->insert('tb_invoice', $data);
        return ('main/checkout');
    }
}
