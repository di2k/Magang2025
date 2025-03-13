<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mytask_log extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Mytask_log_model');
        $this->load->helper('url');
        $this->load->library('session');

        // Anda dapat menambahkan middleware untuk autentikasi di sini
        // contoh: $this->_check_login();
    }

    /**
     * Fungsi utama untuk menampilkan dashboard grafik akses user
     */
    public function index()
    {
        $data['title'] = 'Dashboard Grafik Akses User';
        $data['subtitle'] = 'Jumlah Akses User Berdasarkan Button ID';

        // Ambil data dari model
        $access_data = $this->Mytask_log_model->get_access_count_by_buttonid();

        // Format data untuk ApexCharts
        $chart_data = $this->_format_chart_data($access_data);
        $data['chart_data'] = json_encode($chart_data);
        $data['button_ids'] = json_encode(array_column($access_data, 'buttonid'));

        // // Load view
        $this->load->view('mytask_log/header', $data);
        $this->load->view('mytask_log/dashboard', $data);
        $this->load->view('mytask_log/footer');
    }

    /**
     * Memfilter data grafik berdasarkan rentang tanggal
     */
    public function filter()
    {
        // Ambil parameter rentang tanggal
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');

        // Ambil data dari model dengan filter tanggal
        $access_data = $this->Mytask_log_model->get_access_count_by_buttonid_with_date_range($start_date, $end_date);

        // Format data untuk ApexCharts
        $chart_data = $this->_format_chart_data($access_data);

        // Kirim respons JSON untuk AJAX
        $response = [
            'status' => 'success',
            'data' => $chart_data,
            'button_ids' => array_column($access_data, 'buttonid')
        ];

        echo json_encode($response);
    }

    /**
     * Format data untuk ApexCharts
     * 
     * @param array $data Data dari model
     * @return array Data yang diformat untuk ApexCharts
     */
    private function _format_chart_data($data)
    {
        $formatted_data = [];

        foreach ($data as $item) {
            $formatted_data[] = (int)$item['total_access']; // Pastikan total_access berupa integer
        }

        return $formatted_data;
    }
}
