<?php defined('BASEPATH') || exit('No direct script access allowed');

class M_namelist extends MY_Model {

    function __construct()
    {
        parent::__construct();
        $this->_table = 'namelist';
    }
    
    //生成新行的默认赋值
    public function new_row()
    {
        return array(
            'aid'        => 0,
            'ordered_id' => 0,
            'name'       => '',
            'community'  => '',
            'barcode'    => '',
            'signed'     => 0,
        );
    }

    //插入或更新数据
    function modify($data, $ignore = FALSE)
    {
        if(isset($data['id']) && intval($data['id']) > 0)
        {
            $id = intval($data['id']);
            unset($data['id']);
            return $this->edit($id, $data, $ignore);
        }
        else
        {
            return $this->create($data, $ignore);
        }
    }
    
    //插入数据
    function create($data, $ignore = FALSE)
    {
        $data['created'] = $this->input->server('REQUEST_TIME');

        $this->db->insert($this->_table, $data);
        $insert_id = $this->db->insert_id();

        //记录操作日志----------------------
        $log['operate']    = "create {$this->_table}";
        $log['status']     = $insert_id > 0;
        $log['debug_info'] = array('insert_id'=>$insert_id);
        $this->m_log->create($log);
        //记录操作日志----------------------

        return $insert_id;
    }
    
    //更新数据
    function edit($id, $data, $ignore = FALSE)
    {
        $this->db->where('id', $id);

        $this->db->update($this->_table, $data);
        $affected_rows = $this->db->affected_rows();

        //记录操作日志----------------------
        $log['operate']    = "edit {$this->_table} #{$id}";
        $log['status']     = $affected_rows > 0;
        $log['debug_info'] = array('affected_rows'=>$affected_rows);
        $this->m_log->create($log);
        //记录操作日志----------------------

        return $affected_rows;
    }

    /**
     * Generates a salt that can be used to generate a password hash.
     * @return string the salt
     */
    public function generate_uniqid()
    {
        return '0'.substr(uniqid(rand()), 0, 11);
    }

}

/* End of file M_namelist.php */
/* Location: ./application/models/M_namelist.php */
