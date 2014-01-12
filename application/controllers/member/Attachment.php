<?php defined('BASEPATH') || exit('No direct script access allowed');

class Attachment extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        $this->model = 'm_attachment';
        $this->load->model($this->model);
    }
    
    public function destroy($id)
    {
        $id = intval($id);
        $this->{$this->model}->delete($id);

        redirect(REFERER_URI);
    }

}

/* End of file Attachment.php */
/* Location: ./application/controllers/member/Attachment.php */
