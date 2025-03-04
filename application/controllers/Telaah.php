<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Telaah extends CI_Controller {
	
	private $api_key;
	private $api_endpoint;
    private $default_assistant_id; // ID asisten default yang digunakan
	
	function __construct() {
		parent::__construct();
		$this->load->model('ragflow_model');
		$this->load->helper('url');
		$this->load->library('session');
		
		// Ambil konfigurasi RAGFlow dari database atau file config
        $this->api_key = $this->config->item('ragflow_api_key');
        $this->api_endpoint = $this->config->item('ragflow_api_endpoint');
        $this->default_assistant_id = $this->config->item('ragflow_default_assistant');
	}

	function index()
	{
		if (! $this->session->userdata('isLoggedIn')) redirect("login");
		if (isset($_GET['q'])) $menu = $_GET['q']; else show_404();

		if ($menu == 'ch4tRAG') $this->RAGFlow();
		else show_404();
	}

	private function RAGFlow() {
        // Cek jika sudah ada sesi aktif
        $user_id = $this->session->userdata('user_id');
        $active_session = $this->ragflow_model->get_active_session($user_id);
        
        if (!$active_session) {
            // Jika tidak ada sesi aktif, buat sesi baru dengan asisten default
            $session_name = "Chat " . date('d/m/Y H:i');
            $response = $this->create_ragflow_session($this->default_assistant_id, $session_name, $user_id);
            
            if (isset($response['code']) && $response['code'] === 0) {
                // Simpan informasi sesi ke database lokal
                $session_data = [
                    'user_id' => $user_id,
                    'chat_id' => $this->default_assistant_id,
                    'session_id' => $response['data']['id'],
                    'session_name' => $session_name,
                    'created_at' => date('Y-m-d H:i:s'),
                    'is_active' => 1
                ];
                
                $this->ragflow_model->save_session($session_data);
                $active_session = $this->ragflow_model->get_active_session($user_id);
            } else {
                // Tampilkan error jika gagal membuat sesi
                $data['error'] = "Gagal membuat sesi chat. Silakan coba lagi nanti.";
                $data['view'] = 'telaah/ragflow_simple';
                $this->load->view('main/main', $data);
                return;
            }
        }
        
        // Ambil riwayat percakapan dari sesi aktif
        $messages = $this->ragflow_model->get_session_messages($active_session['session_id']);
        
        $data = [
            'session_info' => $active_session,
            'messages' => $messages
        ];
		
		$data['view'] = 'telaah/ragflow_simple';
		$this->load->view('main/main', $data);
	}
    
    /**
     * API endpoint untuk mengirim pesan ke asisten RAGFlow
     */
    public function send_message()
    {
        if (! $this->session->userdata('isLoggedIn')) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        // Validasi request
        if (!$this->input->is_ajax_request()) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $active_session = $this->ragflow_model->get_active_session($user_id);
        
        if (!$active_session) {
            echo json_encode(['status' => 'error', 'message' => 'Tidak ada sesi aktif']);
            return;
        }
        
        $session_id = $active_session['session_id'];
        $chat_id = $active_session['chat_id'];
        $question = $this->input->post('message');
        
        // Simpan pesan pengguna ke database lokal
        $message_data = [
            'session_id' => $session_id,
            'role' => 'user',
            'content' => $question,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $message_id = $this->ragflow_model->save_message($message_data);
        
        // Kirim pertanyaan ke API RAGFlow
        $response = $this->send_to_ragflow($chat_id, $session_id, $question);
        
        if (isset($response['code']) && $response['code'] === 0 && isset($response['data'])) {
            // Simpan respons asisten ke database lokal
            $assistant_data = [
                'session_id' => $session_id,
                'role' => 'assistant',
                'content' => $response['data']['answer'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Jika ada referensi/citation, simpan juga
            if (isset($response['data']['reference']) && !empty($response['data']['reference'])) {
                $assistant_data['reference'] = json_encode($response['data']['reference']);
            }
            
            $this->ragflow_model->save_message($assistant_data);
            
            // Format respons untuk ditampilkan
            $formatted_response = $this->format_response($response['data']);
            
            echo json_encode([
                'status' => 'success',
                'message' => $formatted_response
            ]);
        } else {
            // Tangani error
            $error_message = isset($response['message']) ? $response['message'] : 'Error communicating with RAGFlow';
            echo json_encode([
                'status' => 'error',
                'message' => $error_message
            ]);
            
            // Log error
            log_message('error', 'RAGFlow API error: ' . json_encode($response));
        }
    }
    
    /**
     * Buat sesi baru
     */
    public function new_session()
    {
        if (! $this->session->userdata('isLoggedIn')) redirect("login");
        
        $user_id = $this->session->userdata('user_id');
        
        // Nonaktifkan semua sesi yang ada
        $this->ragflow_model->deactivate_all_sessions($user_id);
        
        // Buat sesi baru
        $session_name = "Chat " . date('d/m/Y H:i');
        $response = $this->create_ragflow_session($this->default_assistant_id, $session_name, $user_id);
        
        if (isset($response['code']) && $response['code'] === 0) {
            // Simpan informasi sesi ke database lokal
            $session_data = [
                'user_id' => $user_id,
                'chat_id' => $this->default_assistant_id,
                'session_id' => $response['data']['id'],
                'session_name' => $session_name,
                'created_at' => date('Y-m-d H:i:s'),
                'is_active' => 1
            ];
            
            $this->ragflow_model->save_session($session_data);
            
            redirect('telaah?q=ch4tRAG');
        } else {
            $this->session->set_flashdata('error', 'Gagal membuat sesi baru');
            redirect('telaah?q=ch4tRAG');
        }
    }
    
    /*
     * ===================== PRIVATE METHODS =====================
     */
    
    /**
     * Membuat sesi baru di RAGFlow API
     */
    private function create_ragflow_session($chat_id, $session_name, $user_id)
    {
        $url = $this->api_endpoint . '/api/v1/chats/' . $chat_id . '/sessions';
        
        $data = [
            'name' => $session_name,
            'user_id' => $user_id
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    /**
     * Mengirim pertanyaan ke API RAGFlow
     */
    private function send_to_ragflow($chat_id, $session_id, $question)
    {
        $url = $this->api_endpoint . '/api/v1/chats/' . $chat_id . '/completions';
        
        $data = [
            'question' => $question,
            'session_id' => $session_id,
            'stream' => false
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    /**
     * Format response untuk tampilan di frontend
     */
    private function format_response($data)
    {
        $answer = $data['answer'];
        $formatted_response = [
            'answer' => $answer,
            'references' => []
        ];
        
        // Cek apakah ada referensi/citation
        if (isset($data['reference']) && !empty($data['reference'])) {
            if (isset($data['reference']['chunks']) && is_array($data['reference']['chunks'])) {
                foreach ($data['reference']['chunks'] as $chunk) {
                    $formatted_response['references'][] = [
                        'id' => $chunk['id'],
                        'content' => $chunk['content'],
                        'document_name' => $chunk['document_name'] ?? 'Unknown Source',
                        'similarity' => $chunk['similarity']
                    ];
                }
            }
        }
        
        return $formatted_response;
    }
}