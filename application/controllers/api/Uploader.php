<?php defined('BASEPATH') || exit('No direct script access allowed');

class Uploader extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        //加载必须模型
        $this->load->model('m_attachment');
        $this->load->helper('number');
    }
    
    public function dropzone()
    {
        if(empty($_FILES) || !isset($_FILES['file']))
        {
            $response['error'] = 'none file uploaded';
            $this->output->set_output(json_encode($response));
            return;
        }

        //配置上传类
        $config['upload_path'] = TMP_PATH;
        $config['allowed_types'] = 'xls|xlsx';
        $config['max_size'] = '5120';   //单位是KB
        $config['encrypt_name'] = TRUE;
        $config['file_ext_tolower'] = TRUE;
        //加载上传类带配置
        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('file'))
        {   //上传失败
            $response['error'] = $this->upload->display_errors();
            $this->output->set_output(json_encode($response));
            return;
        } 
        else
        {   //上传成功
            $data = $upload_data = $this->upload->data();

            //处理需要保存的数据
            unset($data['file_path']);
            unset($data['full_path']);
            $data['file_size'] = $data['file_size'] * 1024;
            $data['uid'] = $this->session->userdata('uid');

            //保存上传文件的数据
            $insert_id = $this->m_attachment->modify($data);
            if($insert_id)
            {
                //保存成功，文件移至正式目录
                rename($upload_data['full_path'], ATTACHMENT_PATH.$upload_data['file_name']);

                //确定要返回给客户端的数据
                $response['id']         = $insert_id;
                $response['client_name'] = $data['client_name'];
                $response['file_type']   = $data['file_type'];
                $response['file_size']   = byte_format($data['file_size']);
                $this->output->set_output(json_encode($response));
            }
            else
            {   //保存失败
                $response['error'] = '文件保存失败，请联系管理员';
                $this->output->set_output(json_encode($response));
                return;
            }
        }

    }
    
    public function uploadifive_file()
    {
        if(empty($_FILES) || !isset($_FILES['filedata']))
        {
            $response['error'] = 'none file uploaded';
            $this->output->set_output(json_encode($response));
            return;
        }
        if($_POST['token'] != md5('calendar' . $_POST['timestamp']))
        {
            $response['error'] = 'upload with encryption error';
            $this->output->set_output(json_encode($response));
            return;
        }

        //配置上传类
        $config['upload_path'] = TMP_PATH;
        $config['allowed_types'] = 'gif|jpg|png|bmp|txt|pdf|xls|doc|zip|rar|7z|tar|tar.gz|tar.bz2';
        $config['max_size'] = '5120';   //单位是KB
        $config['encrypt_name'] = TRUE;
        $config['file_ext_tolower'] = TRUE;
        //加载上传类带配置
        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('filedata'))
        {   //上传失败
            $response['error'] = $this->upload->display_errors();
            $this->output->set_output(json_encode($response));
            return;
        } 
        else
        {   //上传成功
            $data = $upload_data = $this->upload->data();

            //处理需要保存的数据
            unset($data['file_path']);
            unset($data['full_path']);
            $data['file_size'] = $data['file_size'] * 1024;
            $data['uid'] = $this->session->userdata('uid');

            //保存上传文件的数据
            $insert_id = $this->m_attachment->modify($data);
            if($insert_id)
            {
                //保存成功，文件移至正式目录
                rename($upload_data['full_path'], ATTACHMENT_PATH.$upload_data['file_name']);

                //确定要返回给客户端的数据
                $response['id']         = $insert_id;
                $response['client_name'] = $data['client_name'];
                $response['file_type']   = $data['file_type'];
                $response['file_size']   = byte_format($data['file_size']);
                $this->output->set_output(json_encode($response));
            }
            else
            {   //保存失败
                $response['error'] = '文件保存失败，请联系管理员';
                $this->output->set_output(json_encode($response));
                return;
            }
        }

    }
    
    //uploadfive专用上传方法
    public function uploadifive_image()
    {
        if(empty($_FILES) || !isset($_FILES['filedata']))
        {
            $response['error'] = 'none file uploaded';
            $this->output->set_output(json_encode($response));
            return;
        }
        if($_POST['token'] != md5('calendar' . $_POST['timestamp']))
        {
            $response['error'] = 'upload with encryption error';
            $this->output->set_output(json_encode($response));
            return;
        }

        //配置上传类
        $config['upload_path'] = TMP_PATH;
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size'] = '5120';   //单位是KB
        $config['encrypt_name'] = TRUE;
        $config['file_ext_tolower'] = TRUE;
        //加载上传类带配置
        $this->load->library('upload', $config);

        $image_width = $this->input->post('width') ? intval($this->input->post('width')) : 450;
        $image_height = $this->input->post('height') ? intval($this->input->post('height')) : 450;

        if ( ! $this->upload->do_upload('filedata'))
        {   //上传失败
            $response['error'] = $this->upload->display_errors();
            $this->output->set_output(json_encode($response));
            return;
        } 
        else
        {   //上传成功
            $upload_data = $this->upload->data();

            //确认是否是图像文件
            if($upload_data['is_image'])
            {
                $response['raw_name']     = $upload_data['raw_name'];
                $response['file_name']    = $upload_data['file_name'];
                $response['file_url']     = TMP_URL . $upload_data['file_name'];
                $response['image_width']  = $image_width;
                $response['image_height'] = $image_height;

                //图像处理库配置
                unset($config);
                $config['image_library']  = 'gd2';
                $config['source_image']   = $upload_data['full_path'];
                $config['new_image']      = $upload_data['full_path'];
                $config['width']          = $response['image_width'];
                $config['height']         = $response['image_height'];
                $this->load->library('image_lib', $config);

                //处理图像
                $this->image_lib->resize();

                $this->output->set_output(json_encode($response));
                return;
            }
            else
            {
                $response['error'] = 'it\'s not a image file!';
                $this->output->set_output(json_encode($response));
                return;
            }
        }
    }

    //富文本编辑器上传插图
    public function wysiwyg_img()
    {
        //HTML5上传
        if(isset($_SERVER['HTTP_CONTENT_DISPOSITION'])&&preg_match('/attachment;\s+name="(.+?)";\s+filename="(.+?)"/i',$_SERVER['HTTP_CONTENT_DISPOSITION'],$info))
        {
            try
            {
                $localName = urldecode($info[2]);
                $file_parts = pathinfo($localName);
                $temp_path = TMP_PATH . date('YmdHis') . rand(10000, 99999) . 'tmp.' . strtolower($file_parts['extension']);
                file_put_contents($temp_path, file_get_contents("php://input"));
            }
            catch(Exception $e)
            {
                $response['err'] = $e->getMessage();
                $this->output->set_output(json_encode($response));
                return;
            }
        }
        else
        {
            if(empty($_FILES) || !isset($_FILES['filedata']))
            {
                $response['err'] = 'none file uploaded';
                $this->output->set_output(json_encode($response));
                return;
            }
            $image_width = 300;
            $image_height = 300;

            $config['upload_path'] = TMP_PATH;
            $config['allowed_types'] = 'gif|jpg|png';
            $config['max_size'] = '5120';   //单位是KB
            $config['encrypt_name'] = TRUE;
            $config['file_ext_tolower'] = TRUE;

            $this->load->library('upload', $config);

            if ( ! $this->upload->do_upload('filedata'))
            {   //上传失败
                $response['err'] = $this->upload->display_errors();
                $this->output->set_output(json_encode($response));
                return;
            } 
            else
            {   //上传成功
                $upload_data = $this->upload->data();

                // rename(TMP_URL.$upload_data['file_name'], TMP_URL.$upload_data['raw_name'].$upload_data['file_ext']);
                // rename(TMP_URL.$upload_data['raw_name'].'_thumb'.$upload_data['file_ext'], TMP_URL.$upload_data['raw_name'].'_thumb'.$upload_data['file_ext']);

                //确认是否是图像文件
                if($upload_data['is_image'])
                {
                    $response['err']              = $this->upload->display_errors();
                    $response['msg']['url']       = '!'.TMP_URL.$upload_data['raw_name'].'_thumb'.$upload_data['file_ext'].'||'.TMP_URL.$upload_data['file_name'];
                    $response['msg']['localname'] = $upload_data['client_name'];

                    //图像处理库配置
                    unset($config);
                    $config['image_library']  = 'gd2';
                    $config['source_image']   = $upload_data['full_path'];
                    $config['new_image']      = $upload_data['full_path'];
                    $config['create_thumb']   = TRUE;
                    $config['width']          = $image_width;
                    $config['height']         = $image_height;
                    $this->load->library('image_lib', $config);

                    //处理图像
                    $this->image_lib->resize();

                    $this->output->set_output(json_encode($response));
                    return;
                }
                else
                {
                    $response['err'] = 'it\'s not a image file!';
                    $this->output->set_output(json_encode($response));
                    return;
                }
            }
        }

    }

}

/* End of file Uploader.php */
/* Location: ./application/controller/api/Uploader.php */
