<?php defined('BASEPATH') || exit('No direct script access allowed');

/*
helper functions for library PHPExcel.
it's a third party library not include by CodeIgniter self.
the PHPExcel file located at 'appliction/libraries/PHPExcel.php'
*/

if ( !class_exists('PHPExcel_IOFactory') )
{
    $CI =& get_instance();
    $CI->load->library('PHPExcel');
}

if ( !function_exists('get_sheet_list') )
{
    function get_sheet_list($filepath)
    {
        $excel['type'] = PHPExcel_IOFactory::identify($filepath);

        $obj_reader = PHPExcel_IOFactory::createReader($excel['type']);
        //设置单元格：只读数据不读格式
        $obj_reader->setReadDataOnly(TRUE);

        return $obj_reader->listWorksheetInfo($filepath);
    }
}

if ( !function_exists('load_sheet_data') )
{
    function load_sheet_data($filepath, $sheet_id, $row_limit=array(1,9999), $column_limit=array(0,9999))
    {
        $excel['type'] = PHPExcel_IOFactory::identify($filepath);

        $obj_reader = PHPExcel_IOFactory::createReader($excel['type']);
        //设置单元格：只读数据不读格式
        $obj_reader->setReadDataOnly(TRUE);

        $obj_excel = $obj_reader->load($filepath);
        $obj_sheet = $obj_excel->getSheet($sheet_id);

        $data = array();
        $row = $row_limit[0];

        list($data['row_max'], $data['column_max']) = _get_sheet_max_row_and_column($obj_sheet);
        $data['row_max'] > $row_limit[1] && $data['row_max'] = $row_limit[1];
        $data['column_max'] > $column_limit[1] && $data['column_max'] = $column_limit[1];

        //$data['title'] = _get_sheet_title($obj_sheet, $row);
        //$data['comment'] = _get_sheet_comment($obj_sheet, $row);
        $data['start_row'] = _get_sheet_content_row($obj_sheet, $row);

        for($row=$data['start_row']; $row<=$data['row_max']; $row++)
        {
            for($col=0; $col<=$data['column_max']; $col++)
            {
                $data['cells'][$row][$col] = $obj_sheet->getCellByColumnAndRow($col, $row)->getValue();
                //处理单元格富文本格式，只取文本值
                if ( is_object($data['cells'][$row][$col]) )
                {
                    $data['cells'][$row][$col] = _format_rich_text($obj_sheet, $col, $row);
                }
                //处理多余的换行符
                if(strpos($data['cells'][$row][$col], "\n") !== FALSE)
                {
                    $data['cells'][$row][$col] = str_replace(array("\r", "\n"), array('', ''), $data['cells'][$row][$col]);
                }
                //计算单元格的值
                if(strpos($data['cells'][$row][$col], '=') === 0)
                {
                    $data['cells'][$row][$col] = $obj_sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
                }
            }
        }

        return $data;
    }
}

//分析表单
if ( !function_exists('analyse_sheet_data') )
{
    function analyse_sheet_data($data)
    {
//        $data['title_year'] = _get_title_year($data['title']);
//        $data['title_status'] = _get_title_status($data['title']);
//        $data['title_category'] = _get_title_category($data['title'], 'dropdown');

        $column_a = '';
        //如果是第0列有值就给column_a赋值，反之，如果第0列无值就从column_a赋值
        foreach($data['cells'] as $row=>$row_data)
        {
            if ( !empty($row_data[0]) )
            {
                $column_a = $row_data[0];
            }
            else
            {
                $data['cells'][$row][0] = preg_replace('/[\(（]\S+[\)）]/i', '', $column_a);
            }
        }

        //28=项目年度计划表, 18=项目储备库, 19=项目待建库, 30=项目在建库, 31=项目建成库, 13=项目入库填报表
        $allowed_column_number = array(2);
        if ( !in_array($data['column_max'], $allowed_column_number) )
        {
            $data['error'] = '不支持的表单样式，请确认表单是否符合导入标准。';
        }
        else
        {
            $data['cells'] = call_user_func('_analyse_table_'.$data['column_max'], $data['cells']);
        }

        return $data;
    }
}

//从标题内提取整张表单的年份
//支持格式：
//yyyy-yyyy，返回数组range(yyyy, yyyy)
//yyyy年，返回数组array(yyyy)
if ( !function_exists('_get_title_year') )
{
    function _get_title_year($title)
    {
        $pattern = '/(\d{4})\D?(\d{4})/';
        if ( preg_match($pattern, $title, $match) )
        {
            return range($match[1], $match[2]);
        }
        else
        {
            $pattern = '/(\d{4})年/';
            if ( preg_match($pattern, $title, $match) )
            {
                return (array)$match[1];
            }
            else
            {
                return $match;
            }
        }
    }
}

//从标题内提取整张表单的项目状态
if ( !function_exists('_get_title_status') )
{
    function _get_title_status($title)
    {
        $options = Helper_Array::toHashmap(load_options('project_status'), 'value', 'text');
        $select  = '';
        $pattern = '/(储备|待建|在建|建成)/';
        if ( preg_match($pattern, $title, $match) )
        {
            switch($match[1])
            {
                case '储备':
                    $select = '0.storaged';
                    break;
                case '待建':
                    $select = '1.standby';
                    break;
                case '在建':
                    $select = '6.implementing';
                    break;
                case '建成':
                    $select = '7.done';
                    break;
                defult:
                    $select = '-1.imported';
            }
        }
        return array('options'=>$options, 'selected'=>$select);
    }
}

//从标题内提取整张表单的资金类型
if ( !function_exists('_get_title_category') )
{
    function _get_title_category($title, $return_type='string')
    {
        if($return_type == 'dropdown')
        {
            $options = Helper_Array::toHashmap(load_options('project_category'), 'value', 'text');
        }
        $select  = '';
        $pattern1 = '/(小型水库|小库)/';
        $pattern2 = '/(结余资金|结余)/';
        $pattern3 = '/(后期扶持|后扶)/';
        if ( preg_match($pattern1, $title, $match) )
        {
            $select = 'XK';
        }
        elseif ( preg_match($pattern2, $title, $match) )
        {
            $select = 'JY';
        }
        elseif ( preg_match($pattern3, $title, $match) )
        {
            $select = 'HF';
        }
        else
        {
            $select = 'NOTYPE';
        }
        if($return_type == 'dropdown')
        {
            return array('options'=>$options, 'selected'=>$select);
        }
        else
        {
            return $select;
        }
    }
}

//从地名取得管理者ID
//TODO:本功能较费资源，可优化
function get_uid_by_place_name($place_name)
{
    $CI =& get_instance();
    $area_hash = $CI->m_area->get_hash();
    $area_hash_invert = array_flip($area_hash);
    if ( in_array($place_name, $area_hash) )
    {
        $owner = $CI->m_member->select('id')->where('areaid', $area_hash_invert[$place_name])->get()->row_array();
    }
    else
    {
        $owner = array('id'=>0);
    }
    return $owner['id'];
}

//分析项目表格28列
function _analyse_table_28($cells)
{
    $tmp = array();

    foreach($cells as $key=>$row)
    {
        //过滤不是项目的行，条件：第1列或第2列为空
        if ( empty($row[1]) || empty($row[2]) )
        {
            continue;
        }
        else
        {
            //过滤表格的抬头
            if ( $row[1] == '项目名称' ) continue;

            $tmp[$key]['project_name']        = $row[0];    //项目名称
            $tmp[$key]['unit']                = $row[1];    //项目责任主体
            $tmp[$key]['location']            = $row[2];    //建设地点
            $tmp[$key]['kind']                = $row[3];    //建设性质
            $tmp[$key]['scale_unit']          = $row[4];    //建设规模,单位
            $tmp[$key]['scale_number']        = $row[5];    //建设规模,数量
            $tmp[$key]['scale_unnamed']       = $row[6];    //建设规模,倍数，未命名
            $tmp[$key]['benefit_unit']        = $row[7];    //设计效益,单位
            $tmp[$key]['benefit_number']      = $row[8];    //设计效益,数量
            $tmp[$key]['years']               = $row[9];    //建设起止年限
            $tmp[$key]['prepare']             = $row[10];   //项目前期工作情况
            $tmp[$key]['approval']            = $row[11];   //审批文号
            $tmp[$key]['investment_total']    = round($row[12], 2);   //投资来源（万元），合计
            $tmp[$key]['investment_province'] = round($row[13], 2);   //投资来源（万元），中央和省
            $tmp[$key]['investment_city']     = round($row[14], 2);   //投资来源（万元），市和县
            $tmp[$key]['investment_other']    = round($row[15], 2);   //投资来源（万元），其他资金
            $tmp[$key]['invested_total']      = round($row[16], 2);   //已累计投资（万元），合计
            $tmp[$key]['invested_province']   = round($row[17], 2);   //已累计投资（万元），中央和省
            $tmp[$key]['invested_city']       = round($row[18], 2);   //已累计投资（万元），市和县
            $tmp[$key]['invested_other']      = round($row[19], 2);   //已累计投资（万元），其他资金
            $tmp[$key]['invest_total']        = round($row[20], 2);   //本年计划投资（万元），合计
            $tmp[$key]['invest_province']     = round($row[21], 2);   //本年计划投资（万元），中央和省
            $tmp[$key]['invest_city']         = round($row[22], 2);   //本年计划投资（万元），市和县
            $tmp[$key]['invest_other']        = round($row[23], 2);   //本年计划投资（万元），其他资金
            $tmp[$key]['milestone']           = $row[24];   //本年主要建设内容
            $tmp[$key]['goal_name']           = $row[25];   //本年新增生产能力和效益，名称及单位
            $tmp[$key]['goal_number']         = $row[26];   //本年新增生产能力和效益，数量
            $tmp[$key]['goal_beneficiary']    = $row[27];   //本年主要建设内容, 受益移民（人）
        }
    }

    return $tmp;
}

//分析项目表格18列
function _analyse_table_18($cells)
{
    $tmp = array();

    foreach($cells as $key=>$row)
    {
        //过滤不是项目的行，条件：第2列或第4列为空
        if ( empty($row[2]) || empty($row[4]) )
        {
            continue;
        }
        else
        {
            //过滤表格的抬头
            if ( $row[2] == '项目名称' ) continue;

            $tmp[$key]['status']              = '0.storaged';

            $tmp[$key]['class_name']          = $row[1];    //项目类别
            $tmp[$key]['project_name']        = $row[2];    //项目名称
            $tmp[$key]['uid']                 = get_uid_by_place_name($row[3]);     //从地名得到管辖者ID
            $tmp[$key]['unit']                = $row[4];    //项目责任主体
            $tmp[$key]['location']            = $row[5];    //建设地点
            $tmp[$key]['kind']                = $row[6];    //建设性质
            $tmp[$key]['scale_unit']          = $row[7];    //建设规模,单位
            $tmp[$key]['scale_number']        = $row[8];    //建设规模,数量
            $tmp[$key]['scale_unnamed']       = $row[9];    //建设规模,倍数，未命名
            $tmp[$key]['years']               = $row[10];    //建设起止年限
            $tmp[$key]['investment_total']    = round($row[11], 2);   //投资来源（万元），合计
            $tmp[$key]['goal_name']           = $row[12];   //本年新增生产能力和效益，名称及单位
            $tmp[$key]['goal_number']         = $row[13];   //本年新增生产能力和效益，数量
            $tmp[$key]['category'] = _get_title_category($row[14]);    //资金类型
        }
    }

    return $tmp;
}

//分析项目表格19列
function _analyse_table_19($cells)
{
    $tmp = array();

    foreach($cells as $key=>$row)
    {
        //过滤不是项目的行，条件：第1列或第2列为空
        if ( empty($row[1]) || empty($row[2]) )
        {
            continue;
        }
        else
        {
            //过滤表格的抬头
            if ( $row[1] == '项目名称' ) continue;

            $tmp[$key]['status']              = '1.standby';

            $tmp[$key]['project_name']        = $row[1];    //项目名称
            $tmp[$key]['unit']                = $row[2];    //项目责任主体
            $tmp[$key]['location']            = $row[3];    //建设地点
            $tmp[$key]['kind']                = $row[4];    //建设性质
            $tmp[$key]['scale_unit']          = $row[5];    //建设规模,单位
            $tmp[$key]['scale_number']        = $row[6];    //建设规模,数量
            $tmp[$key]['scale_unnamed']       = $row[7];    //建设规模,倍数，未命名
            $tmp[$key]['benefit_unit']        = $row[8];    //设计效益,单位
            $tmp[$key]['benefit_number']      = $row[9];    //设计效益,数量
            $tmp[$key]['years']               = $row[10];   //建设起止年限
            $tmp[$key]['prepare']             = $row[11];   //项目前期工作情况
            $tmp[$key]['approval']            = $row[12];   //审批文号
            $tmp[$key]['investment_total']    = round($row[13], 2);   //投资来源（万元），合计
            $tmp[$key]['category']            = _get_title_category($row[14]);    //资金类型
            $tmp[$key]['milestone']           = $row[15];   //本年主要建设内容
            $tmp[$key]['goal_name']           = $row[16];   //本年新增生产能力和效益，名称及单位
            $tmp[$key]['goal_number']         = $row[17];   //本年新增生产能力和效益，数量
            $tmp[$key]['goal_beneficiary']    = $row[18];   //本年新增生产能力和效益，受益移民(人)
        }
    }

    return $tmp;
}

//分析项目表格30列
function _analyse_table_30($cells)
{
    $tmp = array();

    foreach($cells as $key=>$row)
    {
        //过滤不是项目的行，条件：第1列或第2列为空
        if ( empty($row[1]) || empty($row[2]) )
        {
            continue;
        }
        else
        {
            //过滤表格的抬头
            if ( $row[1] == '项目名称' ) continue;

            $tmp[$key]['status']              = '6.implementing';

            $tmp[$key]['project_name']        = $row[1];    //项目名称
            $tmp[$key]['unit']                = $row[2];    //项目责任主体
            $tmp[$key]['location']            = $row[3];    //建设地点
            $tmp[$key]['kind']                = $row[4];    //建设性质
            $tmp[$key]['scale_unit']          = $row[5];    //建设规模,单位
            $tmp[$key]['scale_number']        = $row[6];    //建设规模,数量
            $tmp[$key]['scale_unnamed']       = $row[7];    //建设规模,倍数，未命名
            $tmp[$key]['benefit_unit']        = $row[8];    //设计效益,单位
            $tmp[$key]['benefit_number']      = $row[9];    //设计效益,数量
            $tmp[$key]['years']               = $row[10];   //建设起止年限
            $tmp[$key]['prepare']             = $row[11];   //项目前期工作情况
            $tmp[$key]['approval']            = $row[12];   //审批文号
            $tmp[$key]['investment_total']    = round($row[13], 2);   //投资来源（万元），合计
            $tmp[$key]['investment_province'] = round($row[14], 2);   //投资来源（万元），中央和省
            $tmp[$key]['investment_city']     = round($row[15], 2);   //投资来源（万元），市和县
            $tmp[$key]['investment_other']    = round($row[16], 2);   //投资来源（万元），其他资金
            $tmp[$key]['invested_total']      = round($row[17], 2);   //已累计投资（万元），合计
            $tmp[$key]['invested_province']   = round($row[18], 2);   //已累计投资（万元），中央和省
            $tmp[$key]['invested_city']       = round($row[19], 2);   //已累计投资（万元），市和县
            $tmp[$key]['invested_other']      = round($row[20], 2);   //已累计投资（万元），其他资金
            $tmp[$key]['invest_total']        = round($row[21], 2);   //本年计划投资（万元），合计
            $tmp[$key]['invest_province']     = round($row[22], 2);   //本年计划投资（万元），中央和省
            $tmp[$key]['invest_city']         = round($row[23], 2);   //本年计划投资（万元），市和县
            $tmp[$key]['invest_other']        = round($row[24], 2);   //本年计划投资（万元），其他资金
            $tmp[$key]['category']            = _get_title_category($row[25]);    //资金类型
            $tmp[$key]['milestone']           = $row[26];   //本年主要建设内容
            $tmp[$key]['goal_name']           = $row[27];   //本年新增生产能力和效益，名称及单位
            $tmp[$key]['goal_number']         = $row[28];   //本年新增生产能力和效益，数量
            $tmp[$key]['goal_beneficiary']    = $row[29];   //本年新增生产能力和效益，受益移民(人)
        }
    }

    return $tmp;
}

//分析项目表格31列
function _analyse_table_31($cells)
{
    $tmp = array();

    foreach($cells as $key=>$row)
    {
        //过滤不是项目的行，条件：第1列或第2列为空
        if ( empty($row[1]) || empty($row[2]) )
        {
            continue;
        }
        else
        {
            //过滤表格的抬头
            if ( $row[1] == '项目名称' ) continue;

            $tmp[$key]['status']              = '7.done';

            $tmp[$key]['project_name']        = $row[1];    //项目名称
            $tmp[$key]['unit']                = $row[2];    //项目责任主体
            $tmp[$key]['location']            = $row[3];    //建设地点
            $tmp[$key]['kind']                = $row[4];    //建设性质
            $tmp[$key]['scale_unit']          = $row[5];    //建设规模,单位
            $tmp[$key]['scale_number']        = $row[6];    //建设规模,数量
            $tmp[$key]['scale_unnamed']       = $row[7];    //建设规模,倍数，未命名
            $tmp[$key]['benefit_unit']        = $row[8];    //设计效益,单位
            $tmp[$key]['benefit_number']      = $row[9];    //设计效益,数量
            $tmp[$key]['years']               = $row[10];   //建设起止年限
            $tmp[$key]['prepare']             = $row[11];   //项目前期工作情况
            $tmp[$key]['approval']            = $row[12];   //审批文号
            $tmp[$key]['investment_total']    = round($row[13], 2);   //投资来源（万元），合计
            $tmp[$key]['investment_province'] = round($row[14], 2);   //投资来源（万元），中央和省
            $tmp[$key]['investment_city']     = round($row[15], 2);   //投资来源（万元），市和县
            $tmp[$key]['investment_other']    = round($row[16], 2);   //投资来源（万元），其他资金
            $tmp[$key]['invested_total']      = round($row[17], 2);   //已累计投资（万元），合计
            $tmp[$key]['invested_province']   = round($row[18], 2);   //已累计投资（万元），中央和省
            $tmp[$key]['invested_city']       = round($row[19], 2);   //已累计投资（万元），市和县
            $tmp[$key]['invested_other']      = round($row[20], 2);   //已累计投资（万元），其他资金
            $tmp[$key]['invest_total']        = round($row[21], 2);   //本年计划投资（万元），合计
            $tmp[$key]['invest_province']     = round($row[22], 2);   //本年计划投资（万元），中央和省
            $tmp[$key]['invest_city']         = round($row[23], 2);   //本年计划投资（万元），市和县
            $tmp[$key]['invest_other']        = round($row[24], 2);   //本年计划投资（万元），其他资金
            $tmp[$key]['category']            = _get_title_category($row[25]);    //资金类型
            $tmp[$key]['milestone']           = $row[26];   //本年主要建设内容
            $tmp[$key]['goal_name']           = $row[27];   //本年新增生产能力和效益，名称及单位
            $tmp[$key]['goal_number']         = $row[28];   //本年新增生产能力和效益，数量
            $tmp[$key]['goal_beneficiary']    = $row[29];   //本年新增生产能力和效益，受益移民(人)
            $tmp[$key]['done_status']         = $row[30];   //完成情况
        }
    }

    return $tmp;
}

//分析项目表格13列
function _analyse_table_13($cells)
{
    $tmp = array();

    foreach($cells as $key=>$row)
    {
        //过滤不是项目的行，条件：第1列为空
        if ( empty($row[1]) )
        {
            continue;
        }
        else
        {
            $tmp[$key]['project_name']        = $row[1];    //项目名称
            $tmp[$key]['unit']                = $row[2];    //项目责任主体
            $tmp[$key]['location']            = $row[3];    //建设地点
            $tmp[$key]['kind']                = $row[4];    //建设性质
            $tmp[$key]['scale_unit']          = $row[5];    //建设规模,单位
            $tmp[$key]['scale_number']        = $row[6];    //建设规模,数量
            $tmp[$key]['scale_unnamed']       = $row[7];    //建设规模,倍数，未命名
            $tmp[$key]['years']               = $row[8];    //建设起止年限
            $tmp[$key]['investment_total']    = round($row[9], 2);   //投资来源（万元），合计
            $tmp[$key]['goal_name']           = $row[10];   //本年新增生产能力和效益，名称及单位
            $tmp[$key]['goal_number']         = $row[11];   //本年新增生产能力和效益，数量
            $tmp[$key]['category'] = _get_title_category($row[12]);    //资金类型
        }
    }

    return $tmp;
}

//分析项目表格2列
function _analyse_table_2($cells)
{
    $tmp = array();

    foreach($cells as $key=>$row)
    {
        //过滤不是项目的行，条件：第1列为空
        if ( empty($row[1]) )
        {
            continue;
        }
        else
        {
            $tmp[$key]['ordered_id'] = $row[0];
            $tmp[$key]['community']  = $row[1];
            $tmp[$key]['realname']   = $row[2];
        }
    }

    return $tmp;
}

//同步数据库
if ( !function_exists('sync_database') )
{
    function sync_database($aid, $data)
    {
        if ( isset($data['error']) ) return $data;
        require  APPPATH . 'libraries' . DIRECTORY_SEPARATOR . 'php-barcode.php';
        $CI =& get_instance();
        foreach($data['cells'] as $key=>$value)
        {
            $row = $CI->m_namelist->where('aid', $aid)
                                 ->where('ordered_id', $value['ordered_id'])
                                 ->get()->row_array();
            $value['aid'] = $aid;
            if( !empty($row) )
            {//存在老数据，更新数据
                //TODO: 如果新老数据有不同的地方，应提示数据有更新
                $value['id'] = $row['id'];
                $CI->m_namelist->modify($value);
                $data['cells'][$key] = $CI->m_namelist->where('id', $value['id'])->limit(1)->get()->row_array();
            }
            else
            {//不存在老数据，插入数据
                $value['barcode_orign'] = $CI->m_namelist->generate_uniqid();

                $bars = barcode_encode($value['barcode_orign'], 'ANY');
                $barcode = '';
                $numbers = explode(' ', $bars['text']);
                foreach($numbers as $number)
                {
                    list(,,$tmp) = explode(':', $number);
                    $barcode .= $tmp;
                }
                $value['barcode'] = substr($barcode_encoded, 1);

                $value['id'] = $CI->m_namelist->modify($value);
                $data['cells'][$key] = $CI->m_namelist->where('id', $value['id'])->limit(1)->get()->row_array();
            }
        }
        return $data;
    }
}

//得到表单标题
if ( !function_exists('_get_sheet_title') )
{
    function _get_sheet_title(&$sheet, &$row)
    {
        $title = $sheet->getCell('A'.$row++)->getValue().$sheet->getCell('B'.$row)->getValue();
        //第一行常见“附件x：”等字符，少于30个字节(10个中文字)则跳过
        if ( empty($title) || strlen($title) < 30 )
        {
            $title = $sheet->getCell('A'.$row)->getValue().$sheet->getCell('B'.$row++)->getValue();
            if ( empty($title) )
            {
                $title = '无法取得标题，请检查Excel的A1或A2单元格是否存在表单标题！';
            }
        }
        return $title;
    }
}

//得到表单备注
if ( !function_exists('_get_sheet_comment') )
{
    function _get_sheet_comment(&$sheet, &$row)
    {
        $comment = $sheet->getCell('A'.$row++)->getValue();
        if ( in_array($comment, array('序号', '项目名称')) )
        {
            $comment = '';
            $row--;
        }
        return $comment;
    }
}

//得到表单正文起始行
if ( !function_exists('_get_sheet_content_row') )
{
    function _get_sheet_content_row(&$sheet, &$row, $default=array('序号'))
    {
        $title1 = $sheet->getCell('A'.$row++)->getValue();
        $title2 = $sheet->getCell('A'.$row++)->getValue();

        if ( is_array($default) )
        {
            $compare = in_array($title1, $default) && $title2 == NULL;
        }
        else
        {
            $compare = $title == $default && $title2 == NULL;
        }

        if ( $compare )
        {
            return $row;
        }
        else
        {
            return 1;
        }
    }
}

//得到表单最大行和最大列数值
//row index is 1-based while column index is 0-based
if ( !function_exists('_get_sheet_max_row_and_column') )
{
    function _get_sheet_max_row_and_column(&$sheet)
    {
        $row_max = $sheet->getHighestRow();
        $column_max = $sheet->getHighestColumn();
        $column_max = PHPExcel_Cell::columnIndexFromString($column_max);

        return array($row_max, $column_max);
    }
}

if ( !function_exists('_format_rich_text') )
{
    function _format_rich_text(&$sheet, $column, $row)
    {
        $objRichText = new PHPExcel_RichText($sheet->getCellByColumnAndRow($column, $row));
        $elements = $objRichText->getRichTextElements();

        $objRichText2 = $elements[0]->getText(); // judging from the $objRichtText->PlainText method, this should have the plaintext, but instead it is another object, so we need to get the rich text elements again

        $elements2 = $objRichText2->getRichTextElements();
        
        $returnValue = "";
        
        foreach ($elements2 as $text)
        {
            $returnValue .= $text->getText();
        }

        return $returnValue; // this has the plaintext now
    }
}
