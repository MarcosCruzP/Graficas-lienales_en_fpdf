<?php


use setasign\Fpdi\Fpdi;

require('libs/fpdf185/fpdf.php');
require_once('libs/fpdi2/src/autoload.php');


class PDF extends FPDF
{
// ********************* text rotation ************************************************** //
function TextWithDirection($x, $y, $txt, $direction='R')
{
    if ($direction=='R')
        $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',1,0,0,1,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
    elseif ($direction=='L')
        $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',-1,0,0,-1,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
    elseif ($direction=='U')
        $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,1,-1,0,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
    elseif ($direction=='D')
        $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,-1,1,0,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
    else
        $s=sprintf('BT %.2F %.2F Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
    if ($this->ColorFlag)
        $s='q '.$this->TextColor.' '.$s.' Q';
    $this->_out($s);
}

function TextWithRotation($x, $y, $txt, $txt_angle, $font_angle=0)
{
    $font_angle+=90+$txt_angle;
    $txt_angle*=M_PI/180;
    $font_angle*=M_PI/180;

    $txt_dx=cos($txt_angle);
    $txt_dy=sin($txt_angle);
    $font_dx=cos($font_angle);
    $font_dy=sin($font_angle);

    $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',$txt_dx,$txt_dy,$font_dx,$font_dy,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
    if ($this->ColorFlag)
        $s='q '.$this->TextColor.' '.$s.' Q';
    $this->_out($s);
}
}


$pdf = new PDF();
$pdf->AddPage();

 //position
 $PositionX=25;
 $PositionY=80;

 // Size
 $widthChart = 145;
 $heigtChart = 60;



// data
$dataX = array(
    1.0,5.0,10.0,20.0,50.0,100.0,120.0
);

$Type = $_GET['T'];
if($Type == 'P'){
    $dataY = array(13.26, 3.86, 2.90, 2.40, 1.94, 1.68, 1.62);
}
if($Type == 'N'){
    $dataY = array(-3.26, -10.20, -1.4, -2.40, -1.94, -1.68, -11.62);
}
if($Type == 'M'){
    $dataY = array(13.26, -3.86, 2.90, -2.40, -1.94, -1.68, 1.62);
}

$labelX = array(
    0,30.0,60.0,90.0,120.0
);

// We validate if there are positive data
$tienePositivos = false;
$tieneNegativos = false;
for($x=0;$x< sizeof($dataY);$x++){
    if($dataY[$x] > 0){
      $tienePositivos = true;
    }
    if($dataY[$x] < 0){
        $tieneNegativos = true;
      }

  }

// Maximum and minimum in Y and minimum in X
$minY = $tienePositivos  ? $tieneNegativos ?  min($dataY)+(min($dataY)*0.2) : 0 : 0;
$maxY = $tienePositivos ? $tieneNegativos ? max($dataY)-$minY-($minY*0.2) : max($dataY)+ (max($dataY)*0.03) :  min($dataY);


$maxX = max($dataX);

// intervals
$intervalos = 8;
$intData = $heigtChart/$intervalos;
$intervalX = $widthChart/(count($labelX)-1);


// $labelY = array();
$pdf->SetLineWidth(0.05);
// Set mapas
$pdf->SetDrawColor( 165, 166, 167 );
$pdf->Line($PositionX ,$PositionY ,$PositionX + $widthChart, $PositionY );
$pdf->Line($PositionX ,$PositionY ,$PositionX ,$PositionY  -  $heigtChart);

// Y axis lines
for($x =1; $x<$intervalos+1 ; $x++){
    $pdf->Line($PositionX ,$PositionY - ($intData*$x),$PositionX+ $widthChart , $PositionY - ($intData*$x));
}


$pdf->SetFont('Arial','B',9);
$pdf->Cell($PositionX +(($widthChart/2)-10));
$pdf->Cell( 10, 5 ,'Titulo de la grafica', 0 , 1 , 'C');
$pdf->SetFont('Arial','',7.5);


$datosfY = array();
// Y axis data
    for($x =0; $x<$intervalos+1 ; $x++){
        $datosfY[$x] = substr((($maxY/$intervalos)*($x))+$minY,0 ,5);
    }

    $ys = $intervalos;
    $datosy= $tienePositivos ?  array_reverse($datosfY) : $datosfY ;
    for($x =0; $x<$intervalos+1 ; $x++){
        $yAxisPos=$PositionY-($intData*$x);

        $pdf->SetXY($PositionX-10 , $yAxisPos-2);
        $pdf->Cell(10-2 , 5 ,$datosy[$ys--], 0 , 0 , 'R');
    }



// linear graph
$pdf->SetDrawColor(  16, 95, 165 );
$pdf->SetLineWidth(0.4);


    for($x =0; $x<sizeof($dataY)-1; $x++){
            $datoY1= $tienePositivos ? $dataY[$x]-$minY : $dataY[$x];
            $datoY2=$tienePositivos ? $dataY[$x+1]-$minY: $dataY[$x+1];

            $posx1 = (($dataX[$x])*$widthChart)/($maxX);
            $posy1 =  $tienePositivos ? ((($datoY1)*$heigtChart)/($maxY)) : (($datoY1*$heigtChart)/(-$maxY))+($heigtChart);
            $posx2 = (($dataX[$x+1])*$widthChart)/($maxX);
            $posy2 =  $tienePositivos ?  ((($datoY2)*$heigtChart)/($maxY)) : (($datoY2*$heigtChart)/(-$maxY))+($heigtChart);
            $pdf->Line($PositionX + $posx1 ,$PositionY - $posy1 ,$PositionX + $posx2 ,$PositionY - $posy2);
            $pdf-> Rect($PositionX + ( $posx1-0.25) ,$PositionY - ($posy1 +0.25), 0.5, 0.5);
            $index = $x;
    }
    $pdf-> Rect($PositionX + ( $posx2-0.25) ,$PositionY - ($posy2 +0.25), 0.5, 0.5);


// Graphic Generic Specification
$pdf->SetFont('Arial','B',6.4);
$pdf-> SetTextColor(0, 0, 0);
$pdf->Cell($widthChart + 15);
$pdf->Cell( 10, 5 ,'descipcion grafica', 0 , 1 , 'C');

 //// VISIBLE DATA IN X
 $pdf-> SetTextColor(0, 0, 0);
 $pdf->SetXY($PositionX-2,$PositionY);
   for($x = 0; $x<count($labelX);$x++){
     $pdf->Cell( 1, 5 ,$labelX[$x], 0 , 0 , 'L');
     $pdf->Cell($intervalX-1);
 }


       $pdf->SetFont('Arial','B',9);
       $pdf->ln(5);
       $pdf->Cell($PositionX +(($widthChart/2)-10));
       $pdf->Cell( 10, 5 ,'DESCRIPCION DE DATOS EN X', 0 , 1 , 'C');
       $pdf->Cell($PositionX +(($widthChart/2)-10));
       $pdf->Cell( 10, 5 ,'UNIDAD MEDIDA', 0 , 1 , 'C');

       $pdf->TextWithRotation($PositionX - 13, $PositionY-($PositionY*0.05),'DESCRIPCION DE DATOS EN Y',90,0);

       $pdf->SetLineWidth(0.05);



$pdf->Output();