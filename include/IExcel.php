<?php
/**
 * @desc excel�ӿ�
 * @author mengdejun
 */
interface  IExcel
{
        //����excel
        public function import($fileName,$convert_callback_function=null);
        //����excel
        public function export($fileName="excel");
        //�����
        public function addRow(array $array,$sheet="sheet1");
        //��ӱ�ͷ
        public function addHead(array $array,$sheet="sheet1");
        //��ӹ�����
        public function  addSheet($sheet);
        //�ͷ���Դ
        public function release();
}
?>