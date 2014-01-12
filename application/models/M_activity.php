<?php defined('BASEPATH') || exit('No direct script access allowed');

class M_activity extends MY_Model {

    function __construct()
    {
        parent::__construct();
        $this->_table = 'activity';
    }
    
    //删除数据byPK
    function destroy($id)
    {
        $this->db->where('id', $id);
        $this->db->delete($this->_table);

        $deleted = $this->db->affected_rows();

        //如果删除会员成功，则同时删除该会员所有相关数据
        if($deleted > 0)
        {
            $children = array('namelist');
            //按条件批量删除多表
            $this->db->delete($children, array('aid'=>$id));
        }

        //记录操作日志----------------------
        $log['method'] = 'referer';
        $log['operate'] = "destroy {$this->_table} #{$id}";
        $log['status'] = $deleted > 0;
        $this->m_log->create($log);
        //记录操作日志----------------------

        return $deleted;
    }

    //生成新行的默认赋值
    public function new_row()
    {
        return array(
                'name'     => '',
                'location' => '',
                'start_at' => 0,
            );
    }

    private function _before_modify($data)
    {
        if ( isset($data['start_at']) )
        {
            if ( strpos($data['start_at'], 'T') )
            {
                list($date, $time) = explode('T', $data['start_at']);
                list($year, $month, $day) = explode('-', $date);
                list($hour, $minute) = explode(':', $time);
                $data['start_at'] = mktime($hour, $minute, 0, $month, $day, $year);
            }
            elseif ( strpos($data['start_at'], ' ') )
            {
                list($date, $time) = explode(' ', $data['start_at']);
                list($year, $month, $day) = explode('-', $date);
                list($hour, $minute) = explode(':', $time);
                $data['start_at'] = mktime($hour, $minute, 0, $month, $day, $year);
            }
        }
        return $data;
    }

    //插入或更新数据
    function modify($data, $ignore = FALSE)
    {
        //数据预处理
        $data = $this->_before_modify($data);

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

        //非管理员帐号
        if(! $ignore )
        {
            //增加权限判断
            switch($this->session->userdata('identity'))
            {
                case 'superman':
                    break;
                case 'agent':
                    $this->db->where_in('id', array_keys($this->_data['children']));
                    break;
                case 'member':
                    $this->db->where('email', $this->session->userdata('email'));
                    break;
                default:
                    exit('permission error');
            }
        }

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
    
}

/* End of file M_activity.php */
/* Location: ./application/models/M_activity.php */
