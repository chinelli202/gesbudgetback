<?php

//type1 : baniere, recap, grandes rubriques. type2 : baniere recap grandes lignes. type3 baniere titre, recap grandes lignes. type 4 : titre, categories rubriques.
// type 5 : categories, rubs. type 6 : type 5 + sous categories.


//the $row variable represents the index of the row being transormed at this moment.
//the sheet is the worksheet

//use App\Http\Resources\LigneResource;
namespace App\Utils;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
//call iofactory instead of xlsx writer
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\FILL;
use PhpOffice\PhpSpreadsheet\Style\PHPExcel_Style_Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use stdClass;

//include 'BudgetRecapMockElement.php';


class ExcellParser {
    public function __construct(){
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->spreadsheet->getDefaultStyle()
                ->getFont()
                ->setName('Rockwell')
                ->setSize(12);
        $this->sheet->getHeaderFooter()->setOddFooter('&R&F Page &P / &N');
        $this->sheet->getHeaderFooter()->setEvenFooter('&R&F Page &P / &N');
        $this->sheet->getColumnDimension('A')->setWidth(50);
        $this->sheet->getColumnDimension('B')->setWidth(22);
        $this->sheet->getColumnDimension('C')->setWidth(21);
        $this->sheet->getColumnDimension('D')->setWidth(20);
        $this->sheet->getColumnDimension('E')->setWidth(18);
        $this->sheet->getColumnDimension('F')->setWidth(20);
        $this->sheet->getColumnDimension('G')->setWidth(20);
        $this->sheet->getColumnDimension('H')->setWidth(21);
        $this->sheet->getColumnDimension('I')->setWidth(15);
        //setting general column width
        
        $this->thinborders['borders']['outline']['borderStyle'] = BORDER::BORDER_THIN;
        $this->thickborders['borders']['outline']['borderStyle'] = BORDER::BORDER_THICK;
        $this->greentext['font']['color']['rgb'] = '227447';
        //$this->greyrow['fill']['fillType']['startColor'] = 'BFBFBF';
        
        $this->greyrow = array(
            'fill' => array(
                'type' => FILL::FILL_SOLID,
                'color' => array('rgb' => '538ED')
            )
            );

        $this->tablehead = [
            'font'=>[
                'color'=>[
                    'rgb'=>'FFFFFF'
                ],
                'bold'=>true,
                'size'=>13
            ],
            'fill'=>[
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '538ED5'
                ]
            ],
        ];
        $this->normalrow = [
            'font'=>[
                'color'=>[
                    'rgb'=>'000000'
                ],
                'bold'=>true,
                'size'=>12
            ],
            'fill'=>[
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'FFFFFF'
                ]
            ],
        ];
        $this->row = 1;
        $this->rowgap = 0;
        $this->deletegap = 'chapitre';
        $this->criteres = ['mois', 'jour', 'rapport_mensuel', 'intervalle'];
        $this->datatype = ['chapitre', 'rubrique', 'collection'];
    }


    //initial and only public function in the class. redirects to appropriate processing method depending on data type
    public function toExcell($data, $params){
        //$row = 1;
        // if(isset($data->baniere)){ //if baniere then process 
        //     $this->processwithbaniere($data);
        // }
        // else if(isset($data->titre)){
        //     $this->processtitre($data);
        // }
        // else if(isset($data->chapitre)){
        //     $this->processchapitre($data, $data->tableheader);
        // }
        // else return 'unknown data type passed';
        
        $this->processbaniere($data, $params->baniere);
        // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->makeheaderandbody($data, $data->header);
        $this->row++; //we always increase row index after processing either header or rubriques or lignes
        
        //depending on param type, send to chapitre, collection, or other
        if($params->type == 'chapitre'){
            $this->deletegap = 'chapitre';
            $this->processchapitre($data, $data->header);
        }
        else if($params->type == 'rubrique'){
            $this->deletegap = 'rubrique';
            $this->row++;
            $this->processrubrique($data, $data->header);
            $this->sheet->removeRow($this->row-1,2);
            $this->row -=2;
            $this->sheet->getStyle("A".$this->row.":I".$this->row)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);
        } 
        else if($params->type == 'collection'){
            $this->deletegap = 'collection';
            $this->processcollection($data, $data->header);
        }
        else if($params->type == 'full'){
            $this->processcollection($data, $data->header);
            foreach($data->collection as $chapitre){
                $this->row+=2;
                $this->processbaniere($data, $chapitre->libelle);
                // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                $this->makeheaderandbody($data, $data->header);
                $this->row++;
                $this->processchapitre($chapitre, $chapitre->header);
            }
        }
        else if($params->type == 'domaine'){
            //add recap of both sections
            $this->processSections($data, $params->baniere);
            $this->row+=2;
            foreach($data->sections as $section){
                //add full section 
                $this->processbaniere($section, $section->libelle);
                //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                $this->makeheaderandbody($section, $section->header);
                $this->row++;

                $this->processcollection($section, $data->header);
                foreach($section->collection as $chapitre){
                    $this->row+=2;
                    $this->processbaniere($data, $chapitre->libelle);
                    // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    $this->makeheaderandbody($data, $data->header);
                    $this->row++;
                    $this->processchapitre($chapitre, $chapitre->header);
                }             
            }
        }
        // foreach($data->collection as $granderubrique){
        //     $this->processgranderubrique($granderubrique, $this->row, $data->tableheader);
        //     $this->row++; //we always increase row index after processing either header or rubriques or lignes
        //     //add sum
            //TODO
        // //make it an attachment so we can define filename
        // header('Content-Disposition: attachment;filename="result.xlsx"');


        //create IOFactory object
        //$writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        //save into php output
        //$writer->save('php://output');

        $filename = $params->filename;
        //set the header first, so the result will be treated as an xlsx file.
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

         //make it an attachment so we can define filename
        header('Content-Disposition: attachment;filename="'.$filename.'"');

        // // //create IOFactory object
        $writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        // //save into php output
        $writer->save('php://output');


        $myfile = fopen("file:///C:/Dev/Git/budget/gesbudget/storage/app/public/files/".$filename, "w") or die("Unable to open file!");
        
        //$writer->save('file:///C:/laragon/www/saturn/app/public/files/result.xlsx');
        $writer->save($myfile);
        fclose($myfile);
        //echo 'file correctly saved. check location';
    }


    public function processbaniere($data, $baniere){
        //global $this->sheet, $types, $row, $thickborders;
        /**
         * add baniere to sheet
    
        if type1, process grandes rubriques
        if type2, process grandes lignes
        if type3, process titre
        **/
    
        $this->sheet->setCellValue('A'.$this->row, $baniere);
    
        $this->sheet->mergeCells("A".$this->row.":I".$this->row);
        
        //setting title border
        $this->sheet->getStyle("A".$this->row.":I".$this->row)->applyFromArray($this->thickborders);
    
        //set title Font style
        $this->sheet->getStyle('A'.$this->row)->getFont()->setSize(22);
    
        //title alignment
        $this->sheet->getStyle('A'.$this->row)->getAlignment()->setHorizontal(ALIGNMENT::HORIZONTAL_CENTER);
    
        // spacing before table : +2 increment on the row
        $this->sheet->getRowDimension($this->row+1)->setRowHeight(24);
        
        //increasing row index.
        $this->row+=2;
    }

    function processrubrique($data){
        //add lines, add sum, record sum
       //global $this->sheet, $types, $row, $thickborders;
        // echo "processing rubrique ".$data->libelle;
        // echo "\n";
        $this->rowgap = $this->row;
        $this->sheet->insertNewRowBefore($this->row, 1);
        $this->sheet->setCellValue('A'.$this->row,$data->libelle);
        $this->sheet->getStyle('A'.$this->row)->getFont()->setBold(true);
        $this->sheet->getStyle("A".$this->row.":I".$this->row)->applyFromArray($this->normalrow);
        $this->row++;
        
        foreach($data->collection as $ligne){
            $this->sheet->insertNewRowBefore($this->row, 1);
            $this->processligne($ligne);
            $this->sheet->getStyle('A'.$this->row)->getFont()->setBold(false);
            $this->row+=1; //we always increase row index after processing either header or rubriques or lignes
        }

        // //add sum 
        // //TODO
        // $this->sheet->insertNewRowBefore($this->row, 1);
        // $this->sheet->setCellValue('A'.$this->row,"Sous-Total");
        // $this->sheet->getStyle('A'.$this->row)->getFont()->setBold(true);
        // //set sum data.

        // $this->row++;



        $this->sheet->insertNewRowBefore($this->row, 1);
        $this->sheet->setCellValue('A'.$this->row,"Sous-Total");
        //setting sum style
        $this->sheet->getStyle('A'.$this->row.":I".$this->row)->getFont()->setBold(true);
        $this->sheet->getStyle("B".$this->row.":I".$this->row)->applyFromArray($this->greentext);
        $this->sheet->getStyle("A".$this->row.":I".$this->row)->applyFromArray($this->tablehead);
        //set sum data.
        $this->sheet->setCellValue('B'.$this->row,$data->prevision)
        ->setCellValue('C'.$this->row,$data->realisationsMois)
        ->setCellValue('D'.$this->row,$data->realisationsMoisPrecedents)
        ->setCellValue('E'.$this->row,$data->realisations)
        ->setCellValue('F'.$this->row,$data->engagements)
        ->setCellValue('G'.$this->row,$data->execution)
        ->setCellValue('H'.$this->row,$data->solde)
        ->setCellValue('I'.$this->row,$data->tauxExecution);
        $this->sheet->getStyle('B'.$this->row.":H".$this->row)->getNumberFormat()->setFormatCode('# ##0.0');
        if($this->deletegap == 'rubrique')
            $this->sheet->removeRow($this->rowgap-1,1);
        //$this->sheet->removeRow($this->row,2);
        Log::info("row gap value ".$this->rowgap);
        $this->row++;
    }

    function processcollection($data){
        // echo "processing collection ".$data->libelle;
        // echo "\n";
        $this->row++;
        $this->rowgap = $this->row;
        foreach($data->collection as $ligne){
            $this->sheet->insertNewRowBefore($this->row, 1);
            $this->processligne($ligne);
            //$this->row--;
            //$this->row--;
            //set A content to bold
            $this->sheet->getStyle('A'.$this->row)->getFont()->setBold(true);
            //increase row height a little bit
            $this->sheet->getRowDimension($this->row)->setRowHeight(24);
            $this->row+=1; //we always increase row index after processing either header or rubriques or lignes
        }

        //add sum 
        //TODO
        $this->sheet->insertNewRowBefore($this->row, 1);
        $this->sheet->setCellValue('A'.$this->row,"Sous-Total");
        //setting sum style
        $this->sheet->getStyle('A'.$this->row.":I".$this->row)->getFont()->setBold(true);
        $this->sheet->getStyle("B".$this->row.":I".$this->row)->applyFromArray($this->greentext);
        $this->sheet->getStyle("A".$this->row.":I".$this->row)->applyFromArray($this->tablehead);
        //set sum data.
        $this->sheet->setCellValue('B'.$this->row,$data->prevision)
        ->setCellValue('C'.$this->row,$data->realisationsMois)
        ->setCellValue('D'.$this->row,$data->realisationsMoisPrecedents)
        ->setCellValue('E'.$this->row,$data->realisations)
        ->setCellValue('F'.$this->row,$data->engagements)
        ->setCellValue('G'.$this->row,$data->execution)
        ->setCellValue('H'.$this->row,$data->solde)
        ->setCellValue('I'.$this->row,$data->tauxExecution);
        $this->sheet->getStyle('B'.$this->row.":H".$this->row)->getNumberFormat()->setFormatCode('### ### ### ###');
        if($this->deletegap == 'collection')
            $this->sheet->removeRow($this->rowgap-1,1);
        //$this->sheet->removeRow($this->row,1);
        Log::info("row gap value ".$this->rowgap);
        $this->row+=2;
    }

    function processchapitre($data, $tableheader){

    
        // //header
        // $this->makeheaderandbody($data, $tableheader);
        $this->row+=1; //we always increase row index after processing either header or rubriques or lignes
        $this->rowgap = $this->row;
        //rubriques
        foreach($data->collection as $rubrique){
            $this->processrubrique($rubrique);
            //$this->row+=1; //we always increase row index after processing either header or rubriques or lignes
        }
        //sum row chapitre
        //TODO
        // $this->sheet->insertNewRowBefore($this->row, 1);
        // $this->sheet->setCellValue('A'.$this->row,"Total Titre");
        // $this->sheet->getStyle('A'.$this->row)->getFont()->setBold(true);
        // //delete unacessary rows 
        // $this->row++;


        $this->sheet->insertNewRowBefore($this->row, 1);
        $this->sheet->setCellValue('A'.$this->row,"Total Titre");
        //setting sum style
        $this->sheet->getStyle('A'.$this->row.":I".$this->row)->getFont()->setBold(true);
        $this->sheet->getStyle("B".$this->row.":I".$this->row)->applyFromArray($this->greentext);
        $this->sheet->getStyle("A".$this->row.":I".$this->row)->applyFromArray($this->tablehead);
        //set sum data.
        $this->sheet->setCellValue('B'.$this->row,$data->prevision)
        ->setCellValue('C'.$this->row,$data->realisationsMois)
        ->setCellValue('D'.$this->row,$data->realisationsMoisPrecedents)
        ->setCellValue('E'.$this->row,$data->realisations)
        ->setCellValue('F'.$this->row,$data->engagements)
        ->setCellValue('G'.$this->row,$data->execution)
        ->setCellValue('H'.$this->row,$data->solde)
        ->setCellValue('I'.$this->row,$data->tauxExecution);
        $this->sheet->getStyle('B'.$this->row.":H".$this->row)->getNumberFormat()->setFormatCode('### ### ### ###');
        if($this->deletegap == 'chapitre')
            $this->sheet->removeRow($this->rowgap-1,1);
        $this->sheet->removeRow($this->row,2);
        Log::info("row gap value ".$this->rowgap);
        $this->row++;
    }

    function processSections($sections, $baniere){
        foreach($sections->sections as $section){
            $this->processcollection($section);
        }
        //TODO add sum row
    }

    function processligne($ligne){
        //global $this->sheet, $row;
        
        //setting values
        // echo "processing ligne ".$ligne->libelle;
        // echo "\n";
        $this->sheet->setCellValue('A'.$this->row,$ligne->libelle)
            ->setCellValue('B'.$this->row,$ligne->prevision)
            ->setCellValue('C'.$this->row,$ligne->realisationsMois)
            ->setCellValue('D'.$this->row,$ligne->realisationsMoisPrecedents)
            ->setCellValue('E'.$this->row,$ligne->realisations)
            ->setCellValue('F'.$this->row,$ligne->engagements)
            ->setCellValue('G'.$this->row,$ligne->execution)
            ->setCellValue('H'.$this->row,$ligne->solde)
            ->setCellValue('I'.$this->row,$ligne->tauxExecution);
            $this->sheet->getStyle('B'.$this->row.":H".$this->row)->getNumberFormat()->setFormatCode('### ### ### ###');
        
        //setting solde column bold
        $this->sheet->getStyle('H'.$this->row)->getFont()->setBold(true);
        //setting taux execution column bold
        $this->sheet->getStyle('I'.$this->row)->getFont()->setBold(true);
    }

    function makeheaderandbody($data, $tableheader){
        //global $this->sheet, $row, $thinborders, $greentext, $tableheader;
        // echo "building header and body";
        // echo "\n";
        //header title row
        $this->sheet->getStyle("A".$this->row.":I".$this->row)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THICK);
        
        $this->sheet->getStyle("A".$this->row)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK);
        $this->sheet->getStyle("A".$this->row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        $this->sheet->getStyle("A".$this->row)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);
        //$this->sheet->getStyle("I".$this->row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        
        // $this->sheet->getStyle("A".$this->row+1)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK);
        // $this->sheet->getStyle("A".$this->row+1)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        // $this->sheet->getStyle("A".$this->row+1)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);
        
        //merging header title cells
        $this->sheet->mergeCells("B".$this->row.":I".$this->row);
        //set styles alignment, borders, value, etc.
        $this->sheet->getStyle("B".$this->row.":I".$this->row)->getAlignment()->setHorizontal(ALIGNMENT::HORIZONTAL_CENTER);
        $this->sheet->getStyle("B".$this->row.":I".$this->row)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK);
        $this->sheet->getStyle("B".$this->row.":I".$this->row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        $this->sheet->getStyle("B".$this->row.":I".$this->row)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);
        // row height
        $this->sheet->getRowDimension($this->row)->setRowHeight(30);
        // cell value
        $this->sheet->setCellValue('B'.$this->row,'EXECUTION  DU BUDGET  2020');
        //bold
        $this->sheet->getStyle('B'.$this->row)->getFont()->setBold(true);
        //text size
        $this->sheet->getStyle('B'.$this->row)->getFont()->setSize(16);
    
        //increase row index to start entering header column titles
        $this->row++;
    
        //header labels
        $this->sheet->setCellValue('A'.$this->row,'Libellés');
        $this->sheet->getStyle('A'.$this->row)->getFont()->setBold(true);
        $this->sheet->getStyle('A'.$this->row)->applyFromArray($this->thinborders);
        $this->sheet->getStyle("A".$this->row)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK);
        $this->sheet->getStyle("A".$this->row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        
        

        //header column titles
        foreach(['B','C','D','E','F','G','H','I'] as $x){
            $this->sheet->getStyle($x.$this->row.":".$x.($this->row+1))->applyFromArray($this->thinborders);
            $this->sheet->mergeCells($x.$this->row.":".$x.($this->row+1));
        }

        //setting values
        $this->sheet->setCellValue('B'.$this->row,$tableheader->previsionsLabel)
            ->setCellValue('C'.$this->row,$tableheader->realisationsMoisLabel)
            ->setCellValue('D'.$this->row,$tableheader->realisationsMoisPrecedentsLabel)
            ->setCellValue('E'.$this->row,$tableheader->realisationsLabel)
            ->setCellValue('F'.$this->row,$tableheader->engagementsLabel)
            ->setCellValue('G'.$this->row,$tableheader->executionLabel)
            ->setCellValue('H'.$this->row,$tableheader->soldeLabel)
            ->setCellValue('I'.$this->row,$tableheader->tauxExecutionLabel);
    
        //fonts, row dimensions, alignment
        $this->sheet->getStyle('B'.$this->row)->getFont()->setSize(14);
        $this->sheet->getStyle('B'.$this->row)->getFont()->setBold(true);
        $this->sheet->getStyle("C".$this->row.":I".$this->row)->getFont()->setSize(12);
        $this->sheet->getStyle("C".$this->row.":I".$this->row)->getFont()->setBold(true);
        $this->sheet->getRowDimension($this->row+1)->setRowHeight(28);
        $this->sheet->getStyle("B".$this->row.":I".$this->row)->getAlignment()->setHorizontal(ALIGNMENT::HORIZONTAL_CENTER);
        $this->sheet->getStyle("B".$this->row.":I".$this->row)->getAlignment()->setVertical(ALIGNMENT::VERTICAL_CENTER);
        $this->sheet->getStyle("B".$this->row.":I".$this->row)->getAlignment()->setWrapText(true);
        //make text green
        $this->sheet->getStyle("B".$this->row.":I".$this->row)->applyFromArray($this->greentext);
        //setting thick borders around the header row
        $this->sheet->getStyle("A".$this->row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        $this->sheet->getStyle("A".$this->row)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK);
        $this->sheet->getStyle("I".$this->row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        $this->row++;
        $this->sheet->getStyle("I".$this->row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        $this->sheet->getStyle("A".$this->row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        $this->sheet->getStyle("A".$this->row)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK);
        $this->sheet->getStyle("A".$this->row)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);



        //create and style two row that will serve as templates
        for($i = 0; $i < 2; $i++){
            $this->row++;
            foreach(['A','B','C','D','E','F','G','H','I'] as $x){
                $this->sheet->getStyle($x.$this->row.":".$x.($this->row+1))->applyFromArray($this->thinborders);
            }
            $this->sheet->getStyle('A'.$this->row)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK);
            $this->sheet->getStyle('I'.$this->row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        }
        $this->row = $this->row-2;
    }
}













