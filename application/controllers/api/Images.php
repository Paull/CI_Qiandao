<?php defined('BASEPATH') || exit('No direct script access allowed');

class Images extends CI_Controller {

    function __construct()
    {
        parent::__construct();
    }

    public function test($aid, $page=0)
    {
        $this->load->model('m_namelist');
        $aid = intval($aid);
        $guests = $this->m_namelist->select('id, barcode')->where('aid', $aid)->like('barcode', 'd', 'right')->order_by('ordered_id')->get()->result_array();
        foreach($guests as $key=>$value)
        {
            echo $value['id'], ' = ', $value['barcode'], '<br>';
            $value['barcode'] = $this->m_namelist->generate_uniqid();
            $this->m_namelist->modify($value);
            echo $value['id'], ' = ', $value['barcode'], '<br>';
        }
    }

    public function create_image($aid, $page=0)
    {
        $this->load->model('m_namelist');
        require  APPPATH . 'libraries' . DIRECTORY_SEPARATOR . 'php-barcode.php';
        $aid = intval($aid);
        $page = intval($page);
        $perpage = 12;

        $guests = $this->m_namelist->select('ordered_id, realname, community, barcode')->where('aid', $aid)->order_by('ordered_id')->limit($perpage, $page)->get()->result_array();
        foreach($guests as $key=>$value)
        {
            $guests[$key]['ordered_id'] = str_pad($value['ordered_id'], 4, '0', STR_PAD_LEFT);
            $bars=barcode_encode($value['barcode'], 'ANY');
            $guests[$key]['barcode_img'] = barcode_outimage($bars['text'],$bars['bars'], 3);
//       	Header("Content-type: image/png");
//       	ImagePng($guests[$key]['barcode_img']);
//        exit;
        }

		$result = $this->_create_image('lianhao', $guests, $page);
    }
    
	private function _create_image($type, $guests, $page)
	{
		$this->load->helper('watermark');

		$filename    = FCPATH . 'source' . DIRECTORY_SEPARATOR . $type . '.jpg';
		$fp          = fopen($filename, "r");
		$image_data  = fread($fp, filesize($filename));
		fclose($fp);
		$image = ImageCreateFromString($image_data);

        $pos = array(
                'ordered_id'    => array('left'=>550,  'top'=>400, 'fontsize'=>40, 'name'=>'序号', 'relative'=>10, 'color'=>0x000000, 'unit'=>'号'),
                'realname'      => array('left'=>850,  'top'=>410, 'fontsize'=>80, 'name'=>'姓名', 'relative'=>16, 'color'=>0x000000, 'fontfile'=>'simhei.ttf'),
                'community'     => array('left'=>1100, 'top'=>400, 'fontsize'=>40, 'name'=>'社区', 'relative'=>0, 'color'=>0x000000),
//                'barcode'       => array('left'=>1450, 'top'=>510, 'fontsize'=>18, 'name'=>'条码编号', 'relative'=>5, 'color'=>0x000000),
                'barcode_image' => array('left'=>1200, 'top'=>450, 'name'=>'条形码', 'width'=>432, 'height'=>240, 'color'=>0x000000),
            );
        $w=1648;$h=795;
        $position=array();
        for($i=0;$i<12;$i++)
        {
            $tmp = $pos;
            foreach($tmp as $k=>$v)
            {
                $tmp[$k]['left'] += floor($i / 6) * $w;
                $tmp[$k]['top'] += $i % 6 * $h;
            }
            $position[] = $tmp;
        }

        foreach($guests as $guest_id=>$guest)
        {
            foreach($guest as $key=>$value){
                if($value && isset($position[$guest_id][$key]))
                {
                    if(isset($position[$guest_id][$key]['unit'])) $value .= $position[$guest_id][$key]['unit'];
                    if(isset($position[$guest_id][$key]['options'])) $value = $position[$guest_id][$key]['options'][$value];
                }
                else continue;
                
                $position[$guest_id][$key]['left'] -= (strlen($value) - 1) * $position[$guest_id][$key]['relative'];

                $image = watermark($image, $value, $position[$guest_id][$key]);
            }
			imagecopy($image, $guest['barcode_img'], $position[$guest_id]['barcode_image']['left'], $position[$guest_id]['barcode_image']['top'], 0, 0, $position[$guest_id]['barcode_image']['width'], $position[$guest_id]['barcode_image']['height']);
        }

		$targetpath = FCPATH . 'photos' . DIRECTORY_SEPARATOR . $type . '_' . $page . '.png';

        //保存图片
        ImagePng($image, $targetpath);
        //查看图片
       	//Header("Content-type: image/png");
       	//ImagePng($image);

        ImageDestroy($image);
        return true;
	}
}

/* End of file Images.php */
/* Location: ./application/controller/api/Images.php */
