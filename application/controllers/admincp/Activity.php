<?php defined('BASEPATH') || exit('No direct script access allowed');

class Activity extends MY_Controller {

    private $_model = 'm_activity';

    function __construct()
    {
        parent::__construct();
        $this->load->model($this->_model);
        $this->load->model('m_namelist');
        $this->load->language('activity');
    }
    
    public function index()
    {
        $this->_data['template']['title'] = '活动列表';
        $this->_data['template']['breadcrumbs'][] = array('uri'=>CLASS_URI, 'title'=>$this->_data['template']['title']);

        $this->_data['template']['styles'][] = STATIC_URL.'plugins/jquery.x-editable/css/bootstrap-editable.css';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.x-editable/js/bootstrap-editable.min.js';

        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/bootstrap.datetimepicker/bootstrap-datetimepicker.min.js';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/bootstrap.datetimepicker/locales/bootstrap-datetimepicker.'.config_item('language').'.js';

        $this->_data['template']['styles'][] = STATIC_URL.'plugins/jquery.footable/css/footable.core.min.css';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.footable/dist/footable.min.js';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.footable/dist/footable.filter.min.js';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.footable/dist/footable.sort.min.js';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.footable/dist/footable.paginate.min.js';

        $this->_data['template']['javascript'] .= "
var isDatetime = /\d{4}-\d{2}-\d{2}[ |T]\d{2}:\d{2}/;
$(\"span[data-toggle='tooltip']\").tooltip();

$('.footable').footable();

$('.editable').editable({
    selector: 'a[data-type]',
    ajaxOptions: {
        dataType: 'json'
    },
    url: '".base_url(CLASS_URI.'/ajax_modify1')."',
    validate: function(value) {
        if($.trim(value) == '') return '该项必须填写.';
    },
    params: function(params) {
        params.hash = hash;
        return params;
    },
    success: function(response, newValue) {
        if(!response.success){
            return response.msg;
        }else{
            if ( isDatetime.test(response.newValue) ){
                return {newValue: new Date(response.newValue)}
            }else{
                return {newValue: response.newValue}
            }
        }
    }
});\n";

        //读取数据
        $this->_data['list'] = $this->{$this->_model}->order_by('id')->get()->result_array();
        
        //加载模板
        $this->load->view($this->_layout, $this->_data);
    }

    public function namelist($id)
    {
        $this->_data['id'] = $id = intval($id);
        $activity = $this->{$this->_model}->where('id', $id)->get()->row_array();
        if ( empty($activity) )
        {
            $this->load->view('common/message', array('message'=>"找不到指定的活动", 'url'=>site_url(CLASS_URI)));
        }

        $this->_data['template']['title'] = '活动名单';
        $this->_data['template']['breadcrumbs'][] = array('uri'=>CLASS_URI, 'title'=>'活动列表');
        $this->_data['template']['breadcrumbs'][] = array('uri'=>METHOD_URI.'/'.$id, 'title'=>$activity['name']);
        $this->_data['template']['breadcrumbs'][] = array('uri'=>METHOD_URI.'/'.$id, 'title'=>$this->_data['template']['title']);

        $this->_data['template']['styles'][] = STATIC_URL.'plugins/jquery.x-editable/css/bootstrap-editable.css';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.x-editable/js/bootstrap-editable.min.js';

        $this->_data['template']['styles'][] = STATIC_URL.'plugins/jquery.footable/css/footable.core.min.css';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.footable/dist/footable.min.js';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.footable/dist/footable.filter.min.js';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.footable/dist/footable.sort.min.js';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.footable/dist/footable.paginate.min.js';

        $this->_data['template']['javascript'] .= "
$(\"span[data-toggle='tooltip']\").tooltip();

$('.footable').footable();

$('.editable').editable({
    selector: 'a[data-type]',
    ajaxOptions: {
        dataType: 'json'
    },
    url: '".base_url(CLASS_URI.'/ajax_modify2')."',
    validate: function(value) {
        if($.trim(value) == '') return '该项必须填写.';
    },
    params: function(params) {
        params.hash = hash;
        return params;
    },
    success: function(response, newValue) {
        if(!response.success){
            return response.msg;
        }else{
            if ( isDatetime.test(response.newValue) ){
                return {newValue: new Date(response.newValue)}
            }else{
                return {newValue: response.newValue}
            }
        }
    }
});\n";

        $this->_data['list'] = $this->m_namelist->where('aid', $id)->order_by('ordered_id')->get()->result_array();

        //加载模板
        $this->load->view($this->_layout, $this->_data);
    }

    public function namelist_import($id)
    {
        $this->_data['id'] = $id = intval($id);
        $activity = $this->{$this->_model}->where('id', $id)->get()->row_array();
        if ( empty($activity) )
        {
            $this->load->view('common/message', array('message'=>"找不到指定的活动", 'url'=>site_url(CLASS_URI)));
        }

        $this->load->model('m_attachment');
        $this->load->helper('number');

        $this->_data['template']['title'] = '导入名单表格';
        $this->_data['template']['breadcrumbs'][] = array('uri'=>CLASS_URI, 'title'=>'活动列表');
        $this->_data['template']['breadcrumbs'][] = array('uri'=>METHOD_URI.'/'.$id, 'title'=>$activity['name']);
        $this->_data['template']['breadcrumbs'][] = array('uri'=>METHOD_URI.'/'.$id, 'title'=>$this->_data['template']['title']);

        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.gritter/jquery.gritter.min.js';

        $this->_data['template']['styles'][] = STATIC_URL.'plugins/jquery.uploadifive/uploadifive.css';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/jquery.uploadifive/jquery.uploadifive.min.js';

        $this->_data['template']['javascript'] .= "
jQuery('#uploader').uploadifive({
    'fileObjName'      : 'filedata',
    'uploadScript'     : '".base_url('api/uploader/uploadifive_file')."',
    'multi'            : false,
    'fileSizeLimit'    : '5MB',
    'buttonText'       : '上传表格',
    'buttonClass'      : 'btn btn-primary',
    'itemTemplate'     : '',
    'formData'         : {'timestamp': '{$_SERVER['REQUEST_TIME']}', 'token': '".md5('calendar'.$_SERVER['REQUEST_TIME'])."', 'hash': '".$this->security->get_csrf_hash()."'},
    'onInit'           : function(){
        $('#uploadifive-uploader').removeClass('uploadifive-button').find(\"input[type='file']:eq(1)\").height(74);
    },
    'onUploadComplete' : function(file, data) {
        $('#uploadifive-uploader-queue').html('');
        data = jQuery.parseJSON(data);
        if(data.error){
            jQuery.gritter.add({
                title: '上传出错！',
                text: data.error,
                sticky: true
            });
        }
        else{
            jQuery.gritter.add({
                title: '上传成功！',
                text: '<dl><dt>文件名：'+data.client_name+'</dt><dd>大小：'+data.file_size+'</dd><dd>类型：'+data.file_type+'</dd><dt>请点击文件列表中的数据库进行下一步操作。</dt><dd>注意：目前只支持application/vnd.ms-excel文件类型</dl>',
                sticky: false,
                class_name: 'gritter-light'
            });
            table_filelist_prepend(data);
            $('.btn-next').trigger('click');
        }
    },
    width: 200,
    height:60
});
var table_filelist_prepend = function(data){
    var tr = $('<tr/>').prependTo('#filelist');
    tr.append('<td>'+data.id+'</td>');
    tr.append('<td>'+data.client_name+'</td>');
    tr.append('<td>'+data.file_type+'</td>');
    tr.append('<td>'+data.file_size+'</td>');
    tr.append('<td>刚刚</td>');
    tr.append('<td></td>');
    tr.find('td:last').append('<a href=\"".base_url('admincp/activity/sheets')."/'+data.id+'.html\" class=\"btn btn-primary\">导入数据库</a>\\n')
                      .append('<a href=\"".base_url('member/attachment/destroy')."/'+data.id+'\" class=\"btn btn-danger\" onclick=\"return confirm(\'该操作不可恢复！删除该文件？\');\">删除文件</a>');
    if ($('#filelist tr').length > 11) $('#filelist tr:last').remove();
};\n";

        $this->_data['attachments'] = $this->m_attachment->limit(10)->order_by('id', 'DESC')->where('uid', $this->_data['self']['id'])->get()->result_array();

        //加载模板
        $this->load->view($this->_layout, $this->_data);
    }

    //活动添加、修改
    public function modify($id=0)
    {
        $id = intval($id);
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<span class="label label-important">', '</span>');
        $this->form_validation->set_rules('location', lang('location'), 'required');
        $this->form_validation->set_rules('start_at', lang('start_at'), 'required');

        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/bootstrap.datetimepicker/bootstrap-datetimepicker.min.js';
        $this->_data['template']['scripts'][] = STATIC_URL.'plugins/bootstrap.datetimepicker/locales/bootstrap-datetimepicker.'.config_item('language').'.js';
        $this->_data['template']['javascript'] .= "$('#start_at').datetimepicker();";

        if($id > 0)
        {
            $this->form_validation->set_rules('name', lang('name'), 'required');
            //初始化活动
            $this->_data['row'] = $this->{$this->_model}->where('id', $id)->get()->row_array();
            $this->_data['template']['title'] = '修改活动';
            $this->_data['template']['breadcrumbs'][] = array('uri'=>CLASS_URI, 'title'=>'活动列表');
            $this->_data['template']['breadcrumbs'][] = array('uri'=>METHOD_URI, 'title'=>$this->_data['template']['title']);
            $this->_data['template']['breadcrumbs'][] = array('uri'=>METHOD_URI.'/'.$id, 'title'=>$this->_data['row']['name']);
        }
        else
        {
            $this->form_validation->set_rules('name', lang('name'), 'required|is_unique[activity.name]');
            $this->_data['template']['title'] = '添加活动';
            $this->_data['template']['breadcrumbs'][] = array('uri'=>CLASS_URI, 'title'=>'活动列表');
            $this->_data['template']['breadcrumbs'][] = array('uri'=>METHOD_URI, 'title'=>$this->_data['template']['title']);
            //初始化活动空数据
            $this->_data['row'] = $this->{$this->_model}->new_row();
        }

        if ($this->form_validation->run() == FALSE)
        {
            $this->load->view($this->_layout, $this->_data);
        }
        else
        {
            //准备更新数据
            if($id > 0)
            {
                $data['id'] = $id;
            }
            $data['name'] = set_value('name');
            $data['location'] = set_value('location');
            $data['start_at'] = set_value('start_at');

            //更新数据
            $result = $this->{$this->_model}->modify($data);

            if($result)
            {
                $this->load->view('common/message', array('message'=>$this->_data['template']['title']."成功", 'url'=>site_url(CLASS_URI)));
            }
            else
            {
                $this->load->view('common/message', array('message'=>$this->_data['template']['title']."失败"));
            }
        }
    }
    
    //活动删除
    public function destroy($id){
        $id = intval($id);

        $this->{$this->_model}->destroy($id);

        redirect(REFERER_URI);
    }

    //活动表格单击修改
    public function ajax_modify1()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('', '');

        $rules['id'] = array('name'=>'pk', 'title'=>'编号', 'rule'=>'required|is_natural_no_zero|callback__check_id');
        $rules['name'] = array('name'=>'value', 'title'=>lang('name'), 'rule'=>'required');
        $rules['location'] = array('name'=>'value', 'title'=>lang('location'), 'rule'=>'required');
        $rules['start_at'] = array('name'=>'value', 'title'=>lang('start_at'), 'rule'=>'required');

        if($this->input->is_ajax_request() && $this->input->post())
        {
            $data['id'] = $this->input->post('pk');
            $data[$this->input->post('name')] = $this->input->post('value');

            //为每个提交的值设置表单验证规则
            foreach($data as $key=>$value)
            {
                if(isset($rules[$key]))
                {
                    $this->form_validation->set_rules($rules[$key]['name'], $rules[$key]['title'], $rules[$key]['rule']);
                }
            }


            if ($this->form_validation->run() == FALSE)
            {
                $response['success'] = false;
                $response['msg'] = validation_errors();
            }
            else
            {
                try
                {
                    $response['success'] = $this->{$this->_model}->modify($data);
                }
                catch(Exception $e)
                {
                    $response['msg'] = $e->getMessage();
                }

                if( ! $response['success'] && !isset($response['msg']) )
                {
                    $response['msg'] = '更新失败';
                }
                else
                {
                    $response['newValue'] = $this->input->post('value');
                }
            }

            $this->output->set_output(json_encode($response));
        }
    }

    //名单表格单击修改
    public function ajax_modify2()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('', '');

        $rules['id'] = array('name'=>'pk', 'title'=>'编号', 'rule'=>'required|is_natural_no_zero|callback__check_id');
        $rules['realname'] = array('name'=>'value', 'title'=>lang('realname'), 'rule'=>'required');
        $rules['community'] = array('name'=>'value', 'title'=>lang('community'), 'rule'=>'required');
        $rules['signed'] = array('name'=>'value', 'title'=>lang('signed'), 'rule'=>'required');

        if($this->input->is_ajax_request() && $this->input->post())
        {
            $data['id'] = $this->input->post('pk');
            $data[$this->input->post('name')] = $this->input->post('value');

            //为每个提交的值设置表单验证规则
            foreach($data as $key=>$value)
            {
                if(isset($rules[$key]))
                {
                    $this->form_validation->set_rules($rules[$key]['name'], $rules[$key]['title'], $rules[$key]['rule']);
                }
            }


            if ($this->form_validation->run() == FALSE)
            {
                $response['success'] = false;
                $response['msg'] = validation_errors();
            }
            else
            {
                try
                {
                    $response['success'] = $this->{$this->_model}->modify($data);
                }
                catch(Exception $e)
                {
                    $response['msg'] = $e->getMessage();
                }

                if( ! $response['success'] && !isset($response['msg']) )
                {
                    $response['msg'] = '更新失败';
                }
                else
                {
                    $response['newValue'] = $this->input->post('value');
                }
            }

            $this->output->set_output(json_encode($response));
        }
    }
    //检查数据是否存在
    public function _check_id($str)
    {
        if($this->{$this->_model}->where('id', $str)->get()->num_rows() > 0)
        {
            return TRUE;
        }
        else
        {
            $this->form_validation->set_message(__FUNCTION__, '活动数据不存在.');
            return FALSE;
        }
    }

}

/* End of file Activity.php */
/* Location: ./application/controllers/admincp/Activity.php */
