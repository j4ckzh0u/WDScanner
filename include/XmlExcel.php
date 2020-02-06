<?php
include_once ('IExcel.php');
/**
 * @desc php����excel�ຯ�� ֧�ֵ��� ���� �๤����(���ݷ־���)
 * @filesource XmlExcel.php
 * @author mengdejun
 * @date 20100801
 * @version 1.8.1
 */
if(!defined("CHARSET")):define("CHARSET","UTF-8");endif;
if(!defined("VERSION")):define("VERSION","12.00");endif;
if(!defined("THIS_VERSION")):define("THIS_VERSION","1.8.1");endif;
if(!defined("NULL")):define("NULL",null);endif;
class XmlExcel implements IExcel
{
    private $header = "<?xml version=\"1.0\" encoding=\"%s\"?>\n<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http://www.w3.org/TR/REC-html40\">";
    private $documentInfo="<DocumentProperties xmlns=\"urn:schemas-microsoft-com:office:office\"><Author>{author}</Author><Created>{time}</Created><Company>{company}</Company><Version>{version}</Version></DocumentProperties>";
    private $footer = "</Workbook>";
    private $align_left="<Style ss:ID=\"s62\"><Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Center\"/></Style>";
    private $align_center="<Style ss:ID=\"s63\"><Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Center\"/></Style>";
    private $align_right="<Style ss:ID=\"s64\"><Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Center\"/></Style>";
    private $align_bold="<Style ss:ID=\"s65\"><Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Center\"/><Font ss:FontName=\"����\" x:CharSet=\"134\" ss:Size=\"12\" ss:Color=\"#000000\" ss:Bold=\"1\"/></Style>";
    private $align_default="<Style ss:ID=\"Default\" ss:Name=\"Normal\"><Alignment ss:Horizontal=\"%s\" ss:Vertical=\"Center\"/><Borders/><Font ss:FontName=\"����\" x:CharSet=\"134\" ss:Size=\"11\" ss:Color=\"#000000\"/><Interior/><NumberFormat/><Protection/></Style>";
    private $charset=CHARSET;
    private $convert="convert";
    private static $pre_workBook=NULL;
    private $_line=NULL;
    private $_column=NULL;
    private $_columnType=NULL;
    private $_styles=NULL;
    private $_style=NULL;
    private $_title=NULL;
    private $_align="Left";
    private $defaultHeight=13.5;
    private $defaultWidth=54;
    private $_sheets=NULL;
    private $_heads=NULL;
    /**
     * @desc ���췽�� PHP5.X
     * @param string $charset �ַ�����
     */
    public function __construct($charset = 'UTF-8')
    {
        $this->charset=$charset;
    }
    /**
     * @desc ���췽�� PHP4.X
     * @param string $charset �ַ�����
     */
    public function XmlExcel($charset = 'UTF-8')
    {
        $this->charset=$charset;
    }
    /**
     * @desc ��������
     */
    public function __destruct(){}
    /**
     * @desc �ͷſ�����Դ
     * @return null
     */
    public function release()
    {
        unset($this->_line,$this->_column,$this->_heads,$this->_sheets,$this->_styles,$this->_style,$this->_title,self::$pre_workBook);
    }
    /**
     * @desc ������ת������
     * @param array $array
     */
    protected function getLine(array $array)
    {
        $_temp="<Row ss:AutoFitHeight=\"0\">";
            foreach($array as $key=>$val):
                #��ȡָ����������,Ĭ��String
                $_type=!empty($this->_columnType)&&isset($this->_columnType)?!empty($this->_columnType[$key])&&isset($this->_columnType)?$this->_columnType[$key]:"String":"String";
                $_temp.="<Cell><Data ss:Type=\"{$_type}\">{$this->convert($val)}</Data></Cell>";
            endforeach;
        $_temp.="</Row>";
        return $_temp;
    }
    /**
     * @desc ��ӱ��ͷ,Ĭ�ϵĵ�һ�����齫��Ϊ��ͷ
     * @param array $array
     * @param string $sheet ��������
     * @exception $array ����Ϊ��
     */
    public function addHead(array $array, $sheet = "sheet1")
    {
        $this->_line[$sheet][0]=$this->getLine($array);
        $this->_title[$sheet]['width']=count($array)-1;
        $this->_sheets[]=$sheet;
        $this->_heads[$sheet][0]=$array;
    }
    /**
     * @desc �����
     * @param array $array
     * @param string $sheet
     */
    public function addRow(array $array, $sheet = "sheet1",$isErrorReport=true)
    {
        if($isErrorReport):
            if(empty($array)||!isset($array)||count($array)==0):
                exit("data can't null'");
            else:
                $this->_line[$sheet][]=$this->getLine($array);
            endif;
        else:
            $this->_line[$sheet][]=$this->getLine($array);
        endif;
    }
    /**
     * @desc ���ù������ı�ͷ����
     * @param $head ��ͷ����
     * @param $sheet ���������� 
     */
    public function setSheetHead(array $head,$sheet="Sheet1")
    {
        $this->_line[$sheet][]=$this->getLine($head);
    }
    /**
     * @desc ��Ӷ��� ֧��Ƕ������
     * @param array $array
     * @param unknown_type $sheet
     */
    public function addRows(array $array,$sheet = "Sheet1")
    {
        foreach($array as $value):
            if(is_array($value)):
                $this->addRow($value,$sheet);
            else:
                $this->addRow($array,$sheet);
            endif;
        endforeach;
    }
    /**
     * @desc ��ȡ�ƶ����������п��
     * @param @sheet ����������
     */
    public function getColumnLength($sheet="Sheet1")
    {
        return $this->_title[$sheet]['width'];
    }
    /**
     * @desc ��ӹ�����
     * @param unknown_type unknown_type $sheet
     */
    public function addSheet($sheet,$array=array())
    {
        $this->_line[$sheet][]=$array;
    }
    /**
     * @desc ��������ӱ���
     * @param string $str ����
     * @param string $sheet ��������
     */
    public function addTitle($str,$sheet="Sheet1")
    {
        $str=$this->convert($str);
        $this->_title[$sheet]['title']="<Row ss:AutoFitHeight=\"0\" ss:StyleID=\"s65\"><Cell ss:MergeAcross=\"{num}\"><Data ss:Type=\"String\">{$str}</Data></Cell></Row>";
    }
    /**
     * @desc excel����
     * @param string $fileName �������ļ���
     */
    public function export($fileName = "excel",$isConvert=false)
    {
        if($isConvert):
            $fileName=$this->getConvertString($fileName);
        endif;
        header("Content-Type: application/vnd.ms-excel; charset=" . $this->charset);
        header("Content-Disposition:attachment; filename=\"{$fileName}.xls\"");
        echo stripslashes(sprintf($this->header, $this->charset));
        echo str_replace("{company}","sf-express",str_replace("{time}",date("Y-m-dH:i:s",time()),str_replace("{author}","Mr.x",str_replace("{version}",VERSION,$this->documentInfo))));
        echo "<Styles>";
        echo stripslashes(sprintf($this->align_default, $this->_align));
        echo $this->align_left;
        echo $this->align_right;
        echo $this->align_center;
        echo $this->align_bold;
        echo "</Styles>";
        $_hasData=count($this->_line)==0?false:true;
        if($_hasData):
            #������,�����������excel���
            foreach($this->_line as $key=>$value):
            echo "<Worksheet ss:Name=\"{$this->convert($key)}\"><Table ss:DefaultColumnWidth=\"{$this->defaultWidth}\" ss:DefaultRowHeight=\"{$this->defaultHeight}\">";
                #����ʽ�Ϳ��
                if(isset($this->_column[$key]['style_width'])):
                    foreach($this->_column[$key]['style_width'] as $s_key=>$s_value):
                        echo "<Column ss:Index=\"{$s_key}\" ss:AutoFitWidth=\"1\" ss:Width=\"$s_value\"/>";
                    endforeach;
                endif;
                #������
                if(!empty($this->_title[$key]['title'])):
                    echo str_replace("{num}",$this->_title[$key]['width'],$this->_title[$key]['title']);
                endif;
                #��Ԫ��
                foreach($value as $_v):
                    echo $_v;
                endforeach;
            echo "</Table></Worksheet>";
            endforeach;
            #���ر�׼������(Ĭ������������)
            $length=count($this->_line);
            while($length<3):
                $length++;
                echo "<Worksheet ss:Name=\"Sheet{$length}\"><Table></Table></Worksheet>";
            endwhile;
        else:
             #������,���Ĭ�Ϲ�����������֧��(������:�ļ���ȡʧ��)
             for($index=1;$index<=3;$index++):
                echo "<Worksheet ss:Name=\"Sheet{$index}\"><Table></Table></Worksheet>";
             endfor;
        endif;
        echo $this->footer;
    }
    /**
     * @desc excel���뺯��,ע�ú������ļ��������Ƿ�����
     * @param unknown_type $fileName ������ļ�
     * @param unknown_type $convert_callback_function �ص����� ֧�ֱ���ת��,�践��ת������ַ���
     * @return ��ά����,�ֱ��Ӧ ������/��/��Ԫ��
     */
    public function import($fileName,$convert_callback_function=null)
    {
        $xls=simplexml_load_file($fileName);
        $is_convert=!empty($convert_callback_function)&&function_exists($convert_callback_function);
        $index=0;
        $_ra=array();
        foreach($xls->Worksheet as $worksheet):#ѭ��������
            $index_i=1;
            foreach($worksheet->Table->Row as $cells):#ѭ����
                if($index_i!==1):
                    foreach($cells as $cell):#ѭ����Ԫ��
                        $_ra[$index][$index_i][]=$is_convert?call_user_func($convert_callback_function,$cell->Data):$cell->Data;
                    endforeach;
                endif;
                $index_i++;
            endforeach;
            $index++;
        endforeach;
        return $_ra;
    }
    /**
     * @desc �����ַ�����
     * @param string $charset ���õ����ļ��ı���
     */
    public function setCharset($charset="GBK")
    {
        $this->charset = $charset;
    }
 
    /**
     * ���ù��������еĿ�� array(1=>10,2=>23,3=>23,4=>213,5=>asd) �ظ����ø�ֵ ������ǰһ�β����Ľ��
     * @param string $sheet ��������
     * @param array $array ������
     */
    public function setColumnWidth($sheet="sheet1",$array)
    {
        if(!empty($this->_column[$sheet]['style_width'])&&isset($this->_column[$sheet]['style_width'])):
            unset($this->_column[$sheet]['style_width']);
        endif;
        $this->_column[$sheet]['style_width']=$array;
    }
    /**
     * @desc �������й��������п��
     * @param array $array �п��
     */
    public function setAllColumnWidth(array $array)
    {
        $_temp=$this->getAllSheetNames();
        foreach($_temp as $value):
            $this->setColumnWidth($value,$array);
        endforeach;
    }
    /**
     * @desc ����Ĭ���и�
     * @param integer $height
     */
    public function setDefaultRowHeight($height="54")
    {
        $this->defaultHeight=$height;
    }
    /**
     * �����ַ�����ת������(�ص�����)
     * @param string $convert ����ת������ Ĭ������Ϊconvert
     */
    public function addConvert($convert="convert")
    {
        $this->convert = $convert;
    }
    /**
     * @desc �ڲ��ص�����������ַ������ת��
     * @param unknown_type $str
     */
    protected function convert($str)
    {
        if(function_exists($this->convert)):
            return call_user_func($this->convert,$str);
        else:
            return $str;
        endif;
    }
    /**
     * ��ȡ����������
     * @param int $sheet ��ȡ�������ĸ���
     * @return integer
     */
    public function getSheets()
    {
        return sizeof($this->_line);
    }
    /**
     * ��ȡ�������������
     * @param String $sheet ��������
     * @return integer
     */
    public function getRows($sheet)
    {
        return sizeof($this->_line[$sheet]);
    }
    /**
     * @desc ��ȡָ���������ı�ͷ��Ϣ
     * @param string $sheet ����������
     */
    public function getHead($sheet)
    {
        return $this->_heads[$sheet][0];
    }
    /**
     * @desc ����Ĭ���и߶�
     * @param integer $defaultHeight �е�Ĭ�ϸ߶� ��Ĭ��ֵ
     */
    public function setDefaultHeight($defaultHeight) {
        $this->defaultHeight = $defaultHeight;
    }
    /**
     * @desc ����Ĭ�ϵ��п��
     * @param integer $defaultWidth �е�Ĭ�Ͽ�� ��Ĭ��ֵ
     */
    public function setDefaultWidth($defaultWidth) {
        $this->defaultWidth = $defaultWidth;
    }
    /**
     * @desc ��ǰ��������������
     */
    public function currentSheetsLength()
    {
        return sizeof($this->_line)+1;
    }
    /**
     * @desc ����Ĭ�ϵľ��з�ʽ
     * @param string $_align ��ѡֵ Left(left),Center(center),Right(right)
     */
    public function setDefaultAlign($_align)
    {
        $this->_align = ucfirst($_align);
    }
    /**
     * @desc �Զ�����������,֧���Զ��־���,�÷�����addHead��ͻ,ʹ�ø÷���ʱ�������addHead,�������һ���հ׵Ĺ�����
     * @param array $head ��ͷ
     * @param array $data ����
     * @param int $pageSize ҳ������ Ĭ��60000,excel���֧��65536
     * @param string $defaultName ��������,��������������
     */
    public function addPageRow(array $head,array $data,$pageSize=60000,$defaultName="Sheet")
    {
        if(!isset($defaultName)||$defaultName=="Sheet")$defaultName="Sheet".($this->getSheets()+1);
        if(empty(self::$pre_workBook)):
            self::$pre_workBook=$defaultName;
            if(!isset($this->_heads[self::$pre_workBook][0]))
            $this->addHead($head,self::$pre_workBook);
            $this->addRow($data,self::$pre_workBook);
        else:
            if($this->getRows(self::$pre_workBook)>=($pageSize+1)):
                $this->addHead($head,$defaultName);
                $this->addRow($data,$defaultName);
                self::$pre_workBook=$defaultName;
            else:
                $this->addRow($data,self::$pre_workBook);
            endif;
        endif;
    }
    /**
     * @desc �������й�������
     * @param null
     */
    public function getAllSheetNames()
    {
        return $this->_sheets;
    }
    /**
     * @desc �������б�����(�־�) Ĭ��Ϊ�ϲ���ǰ��������������,��������ʾ(����) �÷��������ڹ��������ڵ�����µ���.
     * @param string $title ����
     */
    public function setAllTitle($title)
    {
        $_temp=$this->getAllSheetNames();
        foreach($_temp as $value):
            $this->addTitle($title,$value);
        endforeach;
    }
    /**
     * @desc ����ת������
     * @param string $str ת�����ַ���
     * @param string $source_code ԭ���� Ĭ��UTF-8
     * @param string $target_code Ŀ����� Ĭ��GBK
     */
    protected function getConvertString($str,$source_code='UTF-8',$target_code='GBK')
    {
        return !empty($str)&&is_string($str)?iconv($source_code,$target_code,$str):$str;
    }
    /**
     * @desc ��ӡ������Ϣ
     * @param null
     */
    public function debug($out=true)
    {
        if($out):
            var_dump($this->_line);
        else:
            return $this->_line;
        endif;
    }
    /**
     * @desc ������������׺ ���ô˷���������ȫ��Ψһ��������
     * @param $name �Զ��幤������
     */
    public function uniqueName($name)
    {
        $size=$this->getSheets();
        if($size==0)return $name;
        else return $name.$size;
    }
    /**���õ�λ����������,�÷��������������ǰ��� �������Ͳ���ָ���汾��excel
     * @param $_columnType the $_columnType to set array ָ���ļ�ֵ������
     */
    public function set_columnType($_columnType)
    {
        $this->_columnType = $_columnType;
    }
}
?>