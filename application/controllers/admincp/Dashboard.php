<?php defined('BASEPATH') || exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('m_namelist');
        $this->load->language('activity');
    }
    
    //后台控制面板
	public function index()
	{
        $this->_data['template']['title'] = '签到';

        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.gritter/jquery.gritter.min.js';
        $this->_data['template']['javascript'] .= "
$('#barcode').keypress(function(event){
    if(event.keyCode == 13)
    {
        var barcode = $(this).val();
        $(this).val('');
        $.get('".base_url(CLASS_URI.'/ajax_checkin')."/'+barcode, function(data){
            if(data == null){
                alert('条码有误，请检查');
            }else{
                if(data.signed > 0){
                    $.gritter.add({
                        title: data.barcode,
                        text: '序号:'+data.ordered_id+'<br>姓名:'+data.realname+'<br>社区:'+data.community,
                        class_name: 'gritter-light'
                    });
                    $('#id'+data.id).html('<span class=\"label label-success\">刚刚</span>');
                }else{
                    $.gritter.add({
                        title: data.barcode,
                        text: '序号:'+data.ordered_id+'<br>姓名:'+data.realname+'<br>社区:'+data.community
                    });
                    $('#id'+data.id).html('<span class=\"label label-success\">刚刚</span>');
                }
            }
        }, 'json');
    }
});
var status_update = function(){
    $.get('".base_url(CLASS_URI.'/ajax_load_status')."', function(data){
        for(i in data['guests']){
            if(data['guests'][i]['signed'] != 0){
                $('#id'+data['guests'][i]['id']).html('<span class=\"label label-success\">'+data['guests'][i]['signed']+'</span>');
            }
        }
        $('.badge:eq(0)').text(data.signed);
        $('.badge:eq(1)').text(data.unsigned);
    }, 'json');
    $('#barcode').focus();
};
setInterval(status_update, 5000);\n";

        $this->_data['list'] = $this->m_namelist->where('aid', 1)->order_by('ordered_id')->get()->result_array();

        $total = count($this->_data['list']);
        $unsigned = $this->m_namelist->where('aid', 1)->where('signed', 0)->get()->num_rows();
        $signed = $total-$unsigned;
        $this->_data['template']['javascript'] .= "$('.badge:eq(0)').text(".$signed.");$('.badge:eq(1)').text(".$unsigned.");";

        $this->load->view($this->_layout, $this->_data);
	}

    //已到
	public function signed()
	{
        $this->_data['template']['title'] = '已签到';
        $this->_data['template']['breadcrumbs'][] = array('uri'=>$this->uri->uri_string, 'title'=>$this->_data['template']['title']);

        $this->_data['template']['styles'][] = STATIC_URL.'plugins/jquery.footable/css/footable.core.min.css';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.footable/dist/footable.min.js';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.footable/dist/footable.filter.min.js';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.footable/dist/footable.sort.min.js';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.footable/dist/footable.paginate.min.js';

        $this->_data['template']['javascript'] .= "$(\"span[data-toggle='tooltip']\").tooltip();$('.footable').footable();";

        $this->_data['list'] = $this->m_namelist->where('aid', 1)->where('signed >', 0, FALSE)->order_by('ordered_id')->get()->result_array();

        $total = $this->m_namelist->where('aid', 1)->get()->num_rows();
        $signed = count($this->_data['list']);
        $unsigned = $total-$signed;
        $this->_data['template']['javascript'] .= "$('.badge:eq(0)').text(".$signed.");$('.badge:eq(1)').text(".$unsigned.");";

        $this->load->view($this->_layout, $this->_data);
	}

	public function unsigned()
	{
        $this->_data['template']['title'] = '未签到';
        $this->_data['template']['breadcrumbs'][] = array('uri'=>$this->uri->uri_string, 'title'=>$this->_data['template']['title']);

        $this->_data['template']['styles'][] = STATIC_URL.'plugins/jquery.footable/css/footable.core.min.css';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.footable/dist/footable.min.js';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.footable/dist/footable.filter.min.js';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.footable/dist/footable.sort.min.js';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.footable/dist/footable.paginate.min.js';

        $this->_data['template']['javascript'] .= "$(\"span[data-toggle='tooltip']\").tooltip();$('.footable').footable();";

        $this->_data['list'] = $this->m_namelist->where('aid', 1)->where('signed', 0)->order_by('ordered_id')->get()->result_array();

        $total = $this->m_namelist->where('aid', 1)->get()->num_rows();
        $unsigned = count($this->_data['list']);
        $signed = $total-$unsigned;
        $this->_data['template']['javascript'] .= "$('.badge:eq(0)').text(".$signed.");$('.badge:eq(1)').text(".$unsigned.");";

        $this->load->view($this->_layout, $this->_data);
	}

    public function ajax_checkin($code)
    {
        $code = trim($code);
        $person = $this->m_namelist->where('barcode', $code)->limit(1)->get()->row_array();
        $result = $this->m_namelist->where('barcode', $code)->set('signed', $_SERVER['REQUEST_TIME'])->update();
        $this->output->set_output(json_encode($person));
    }

    public function ajax_load_status()
    {
        $guests = $this->m_namelist->select('id, signed')->where('aid', 1)->get()->result_array();
        $signed = $unsigned = 0;
        foreach($guests as $key=>$value)
        {
            if($value['signed'])
            {
                $guests[$key]['signed'] = date('H:i:s', $value['signed']);
                $signed++;
            }
            else
            {
                $unsigned++;
            }
        }
        $data = array('guests'=>$guests, 'signed'=>$signed, 'unsigned'=>$unsigned);
        $this->output->set_output(json_encode($data));
    }
    
}

/* End of file Dashboard.php */
/* Location: ./application/controllers/admincp/Dashboard.php */
