<?php defined('BASEPATH') || exit('No direct script access allowed');

class M_attachment extends MY_Model {

    function __construct()
    {
        parent::__construct();
        $this->_table = 'attachment';
    }
    
    function modify($data)
    {
        if(isset($data['id']) && intval($data['id']) > 0)
        {
            $id = intval($data['id']);
            unset($data['id']);
            return $this->edit($id, $data);
        }
        else
        {
            return $this->create($data);
        }
    }
    
    function create($data)
    {
        $data['created'] = $this->input->server('REQUEST_TIME');
        $this->db->insert($this->_table, $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }
    
    function edit($id, $data)
    {
        $this->db->where('id', $id);
        
        if($this->session->userdata('identity') != 'superman')
        {
            $this->db->where('uid', $this->session->userdata('uid'));
        }
        
        $this->db->update($this->_table, $data);
        $affected_rows = $this->db->affected_rows();
        return $affected_rows;
    }
    
}

/* End of file M_attachment.php */
/* Location: ./application/models/M_attachment.php */
