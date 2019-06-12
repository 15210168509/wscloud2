<?php

namespace Lib\Tcpdf;
use Lib\Tools;

/**
 * PDF
 * User: dbn
 * Date: 2017/12/6
 * Time: 10:09
 */
class Tcpdf
{
    public function __construct()
    {
        vendor('tcpdf.tcpdf');
    }

    /**
     * 将HTML转换成PDF
     * @param string $html     HTML
     * @param string $title    标题
     * @param string $fileName 文件名
     * @param string $dest     输出方式
     * @param string $type     类型
     * @param bool   $ssl      安全设置
     * @return boolean
     */
    public function html2pdf($html, $title = '默认标题', $fileName, $dest = 'I', $type = 'Common', $ssl = false)
    {
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        //设置文件信息
        $pdf->SetCreator(PDF_CREATOR);               // 设置文档创建者
        $pdf->SetAuthor("华迅金安（北京）科技有限公司");  // 设置文档作者
        $pdf->SetTitle($title);                      // 设置标题
        $pdf->SetSubject('HXJA Internal File');      // 设置文档主题
        $pdf->SetKeywords('TCPDF, PDF');             // 设置文档关键字

        //删除预定义的打印 页眉/页尾
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        //设置默认等宽字体
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //设置页面边幅
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, true);

        //设置单元格的边距
        $pdf->setCellPaddings(0, 0, 0, 0);


        //设置线条的风格：
        $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => '0', 'color' => array(0, 0, 0)));

        //设置自动分页符
        $pdf->SetAutoPageBreak(TRUE, '10');

        //设置图像比例因子
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //设置一些语言相关的字符串
//       $pdf->setLanguageArray("xx");

        /*设置字体：字体类型（如helvetica(Helvetica)黑体，times (Times-Roman)罗马字体）、风格（B粗体，I斜体，underline下划线等）、字体大小 */
        $pdf->SetFont('stsongstdlight', '', 11); //设置中文显示

        //增加一个页面
        $pdf->AddPage();

        //设置单行单元格
        $pdf->Cell(0, 5, $title, 0, 1, 'C');

        //设置行间距
        $pdf->setCellHeightRatio(2);

        /*输出HTML文本：

        Html：html文本

        Ln：true，在文本的下一行插入新行

        Fill：填充。false，单元格的背景为透明，true，单元格必需被填充

        Reseth：true，重新设置最后一行的高度

        Cell：true，就调整间距为当前的间距

        Align：调整文本位置。 */

//        $pdf->writeHTML($html, true, false, true, true, '');

        /*用此函数可以设置可选边框，背景颜色和HTML文本字符串来输出单元格（矩形区域）

        W：设置单元格宽度。0，伸展到右边幅的距离

        H：设置单元格最小的高度

        X：以左上角为原点的横坐标

        Y：以左上角为原点的纵坐标

        Html：html文本

        Border：边框

        Ln：0，单元格后的内容插到表格右边或左边，1，单元格的下一行，2，在单元格下面

        Fill：填充

        Reseth：true，重新设置最后一行的高度

        Align：文本的位置

        Autopadding：true，自动调整文本到边框的距离。 */
        $pdf->writeHTMLCell(0, 0, PDF_MARGIN_LEFT, PDF_MARGIN_TOP, $html);

        //指向最后一页
        $pdf->lastPage();

        //安全性设置
        if ($ssl) {
            $pdf->SetProtection(
                array('print', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high'),
                '', C('OwnerPass'), 1, C('PubKeys'));
        }

        /*输入PDF文档:
        Name：PDF保存的名字
        Dest：PDF输出的方式。I，默认值，在浏览器中打开；D，点击下载按钮， PDF文件会被下载下来；F，文件会被保存在服务器中；S，PDF会以字符串形式输出；E：PDF以邮件的附件输出。 */
        switch ($dest) {
            case 'F':

                // 输出到临时文件夹中
                $dir = realpath(dirname(__FILE__)) . '/temporary/';
                if (!is_dir($dir)) {
                    @mkdir($dir, 0777, true);
                }
                @chmod($dir, 0777);
                $furl = $dir.$fileName.'.pdf';
                $pdf->Output($furl, $dest);

                // 上传到服务器
                if ($this->uploadPdf2Sign($furl, $fileName, $type)) {

                    // 上传成功，删除临时文件
                    @unlink($furl);
                    return true;
                }
                return false;break;

            case 'I':
            case 'D':
            case 'S':
            case 'E':
            default :
                $pdf->Output($fileName.'.pdf', $dest);
                return true;break;
        }
    }

    /**
     * 上传PDF到sign服务器
     * @param  string $furl     文件路径
     * @param  string $fileName 文件名
     * @param  string $type     文件用途类型
     * @return mixed
     */
    private function uploadPdf2Sign($furl, $fileName, $type)
    {
        return Tools::uploadFile2Sign($furl, 'pdf', $fileName, $type);
    }
}