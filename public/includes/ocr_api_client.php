<?php
/**
 * =============================================
 * OCR API Client untuk Sistem BBA
 * Client PHP untuk call FastAPI OCR endpoints
 * =============================================
 */

class OCRAPIClient {
    private $base_url;
    private $timeout;
    
    public function __construct($base_url = 'http://localhost:8000') {
        $this->base_url = rtrim($base_url, '/');
        $this->timeout = 30; // 30 seconds timeout
    }
    
    /**
     * Validate bukti transfer
     */
    public function validateTransfer($file_path, $params) {
        $url = $this->base_url . '/api/v1/validate-transfer';
        
        // Prepare multipart form data
        $post_fields = [
            'file' => new CURLFile($file_path, mime_content_type($file_path), basename($file_path)),
            'uploader_type' => $params['uploader_type'],
            'uploader_id' => $params['uploader_id'],
            'expected_amount' => $params['expected_amount'],
            'expected_nis' => $params['expected_nis'],
            'expected_nama' => $params['expected_nama'],
        ];
        
        if (isset($params['keuangan_id'])) {
            $post_fields['keuangan_id'] = $params['keuangan_id'];
        }
        
        return $this->post($url, $post_fields, true);
    }
    
    /**
     * Get validation list
     */
    public function getValidations($filters = []) {
        $url = $this->base_url . '/api/v1/validations';
        
        $query_params = [];
        if (isset($filters['uploader_id'])) {
            $query_params['uploader_id'] = $filters['uploader_id'];
        }
        if (isset($filters['status'])) {
            $query_params['status'] = $filters['status'];
        }
        if (isset($filters['limit'])) {
            $query_params['limit'] = $filters['limit'];
        }
        if (isset($filters['offset'])) {
            $query_params['offset'] = $filters['offset'];
        }
        
        if (!empty($query_params)) {
            $url .= '?' . http_build_query($query_params);
        }
        
        return $this->get($url);
    }
    
    /**
     * Get validation detail
     */
    public function getValidationDetail($validation_id) {
        $url = $this->base_url . "/api/v1/validations/{$validation_id}";
        return $this->get($url);
    }
    
    /**
     * Manual approval/rejection
     */
    public function manualApproval($validation_id, $status, $validated_by, $notes = null) {
        $url = $this->base_url . "/api/v1/validations/{$validation_id}/approve";
        
        $data = [
            'validation_id' => $validation_id,
            'status' => $status, // 'approved' or 'rejected'
            'validated_by' => $validated_by,
        ];
        
        if ($notes) {
            $data['notes'] = $notes;
        }
        
        return $this->post($url, json_encode($data), false, true);
    }
    
    /**
     * Health check
     */
    public function healthCheck() {
        $url = $this->base_url . '/';
        return $this->get($url);
    }
    
    /**
     * Generic GET request
     */
    private function get($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'Connection error: ' . $error
            ];
        }
        
        $result = json_decode($response, true);
        
        if ($http_code >= 400) {
            return [
                'success' => false,
                'error' => $result['detail'] ?? 'Request failed',
                'http_code' => $http_code
            ];
        }
        
        return $result;
    }
    
    /**
     * Generic POST request
     */
    private function post($url, $data, $is_multipart = false, $is_json = false) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        
        if ($is_multipart) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } elseif ($is_json) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'Connection error: ' . $error
            ];
        }
        
        $result = json_decode($response, true);
        
        if ($http_code >= 400) {
            return [
                'success' => false,
                'error' => $result['detail'] ?? 'Request failed',
                'http_code' => $http_code
            ];
        }
        
        return $result;
    }
}
