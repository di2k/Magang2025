<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Mytask_log_model extends CI_Model
{

    // Definisikan properti secara eksplisit
    protected $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = 't_mytask_log';
    }

    /**
     * Mendapatkan jumlah akses user berdasarkan buttonid
     * 
     * @return array Data jumlah akses per buttonid
     */
    public function get_access_count_by_buttonid()
    {
        $this->db->select('buttonid, COUNT(*) as total_access');
        $this->db->from($this->table);
        $this->db->group_by('buttonid');
        $this->db->order_by('total_access', 'DESC');
        $query = $this->db->get();

        return $query->result_array();
    }

    /**
     * Mendapatkan jumlah akses user berdasarkan buttonid dengan filter waktu
     * 
     * @param string $start_date Tanggal awal dalam format Y-m-d
     * @param string $end_date Tanggal akhir dalam format Y-m-d
     * @return array Data jumlah akses per buttonid dalam rentang waktu tertentu
     */
    public function get_access_count_by_buttonid_with_date_range($start_date, $end_date)
    {
        $this->db->select('buttonid, COUNT(*) as total_access');
        $this->db->from($this->table);

        if ($start_date && $end_date) {
            $this->db->where('datetime >=', $start_date . ' 00:00:00');
            $this->db->where('datetime <=', $end_date . ' 23:59:59');
        }

        $this->db->group_by('buttonid');
        $this->db->order_by('total_access', 'DESC');
        $query = $this->db->get();

        return $query->result_array();
    }
}
