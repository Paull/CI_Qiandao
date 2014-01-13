<?php defined('BASEPATH') || exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    function __construct()
    {
        parent::__construct();
    }
    
    //后台控制面板
	public function index()
	{
        $this->load->model('m_namelist');
        $this->load->language('activity');

        $this->_data['template']['title'] = '签到';

        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.gritter/jquery.gritter.min.js';
        $this->_data['template']['javascript'] .= "
$('#barcode').keypress(function(event){
    if(event.keyCode == 13)
    {
        $.get('".base_url(CLASS_URI.'/ajax_checkin')."/'+$(this).val(), function(data){
            $.gritter.add({
                title: data.barcode,
                text: '序号:'+data.ordered_id+'<br>姓名:'+data.realname+'<br>社区:'+data.community,
                class_name: 'gritter-light'
            });
            $('#id'+data.id).html('<span class=\"label label-success\">刚刚</span>');
            $('#barcode').val('');
        }, 'json');
    }
});
var status_update = function(){
    $.get('".base_url(CLASS_URI.'/ajax_load_status')."', function(data){
        for(i in data){
            if(data[i]['signed'] != 0){
                $('#id'+data[i]['id']).html('<span class=\"label label-success\">'+data[i]['signed']+'</span>');
            }
        }
    }, 'json');
    $('#barcode').focus();
};
setInterval(status_update, 5000);
";

        $this->_data['list'] = $this->m_namelist->where('aid', 1)->order_by('ordered_id')->get()->result_array();

        $this->load->view($this->_layout, $this->_data);
	}

    public function ajax_checkin($code)
    {
        $code = trim($code);
        $this->load->model('m_namelist');
        $person = $this->m_namelist->where('barcode', $code)->limit(1)->get()->row_array();
        $result = $this->m_namelist->where('barcode', $code)->set('signed', $_SERVER['REQUEST_TIME'])->update();
        $this->output->set_output(json_encode($person));
    }

    public function ajax_load_status()
    {
        $this->load->model('m_namelist');
        $data = $this->m_namelist->select('id, signed')->where('aid', 1)->get()->result_array();
        foreach($data as $key=>$value)
        {
            if($value['signed']) $data[$key]['signed'] = date('H:i:s', $value['signed']);
        }
        $this->output->set_output(json_encode($data));
    }
    
}

/* End of file Dashboard.php */
/* Location: ./application/controllers/admincp/Dashboard.php */
