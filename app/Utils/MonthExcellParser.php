<?php

//type1 : baniere, recap, grandes rubriques. type2 : baniere recap grandes lignes. type3 baniere titre, recap grandes lignes. type 4 : titre, categories rubriques.
// type 5 : categories, rubs. type 6 : type 5 + sous categories.


//the $row variable represents the index of the row being transormed at this moment.
//the sheet is the worksheet

//use App\Http\Resources\LigneResource;
namespace App\Utils;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
//call iofactory instead of xlsx writer
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\FILL;
use PhpOffice\PhpSpreadsheet\Style\Border;
use stdClass;

//include 'BudgetRecapMockElement.php';


class MonthExcellParser {
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
        $this->sheet->getColumnDimension('F')->setWidth(15);

        //setting general column width
        
        $this->thinborders['borders']['outline']['borderStyle'] = BORDER::BORDER_THIN;
        $this->thickborders['borders']['outline']['borderStyle'] = BORDER::BORDER_THICK;
        $this->greentext['font']['color']['rgb'] = '227447';
        $this->row = 1;
        $this->gapindex = 0;
        $this->criteres = ['mois', 'jour', 'rapport_mensuel', 'intervalle'];
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
        $this->processchapitre($data, $data->header);
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

        //set the header first, so the result will be treated as an xlsx file.
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

         //make it an attachment so we can define filename
        header('Content-Disposition: attachment;filename="'.$params->filename.'"');

        // //create IOFactory object
        $writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        //save into php output
        $writer->save('php://output');


        $filename = $params->filename;
        $myfile = fopen("file:///C:/Dev/milky/gesbudgetback/storage/app/public/files/".$filename, "w") or die("Unable to open file!");
        
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
    
        $this->sheet->mergeCells("A".$this->row.":F".$this->row);
        
        //setting title border
        $this->sheet->getStyle("A".$this->row.":F".$this->row)->applyFromArray($this->thickborders);
    
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
        $this->sheet->insertNewRowBefore($this->row, 1);
        $this->sheet->setCellValue('A'.$this->row,$data->libelle);
        $this->sheet->getStyle('A'.$this->row)->getFont()->setBold(true);
        $this->row++;
        
        foreach($data->collection as $ligne){
            $this->sheet->insertNewRowBefore($this->row, 1);
            $this->processligne($ligne);
            $this->sheet->getStyle('A'.$this->row)->getFont()->setBold(false);
            $this->row+=1; //we always increase row index after processing either header or rubriques or lignes
        }

        //add sum 
        //TODO
        $this->sheet->insertNewRowBefore($this->row, 1);
        $this->sheet->setCellValue('A'.$this->row,"Sous-Total");
        $this->sheet->getStyle('A'.$this->row)->getFont()->setBold(true);
        //set sum data.

        $this->row++;
    }

    function processcollection($data){
        // echo "processing collection ".$data->libelle;
        // echo "\n";
        
        foreach($data->collection as $ligne){
            $this->sheet->insertNewRowBefore($this->row, 1);
            $this->processligne($ligne);
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
        $this->sheet->getStyle('A'.$this->row)->getFont()->setBold(true);
        //set sum data.

        $this->row++;
    }

    function processchapitre($data, $tableheader){
        // //add categorie title, add space,
        // //add header
        // //for each rubrique, process rubrique
        // //add total line.
        // //global $this->sheet, $types, $row, $thickborders;
        // //titre
        //     //set titre
        // $this->sheet->setCellValue('A'.$this->row, $data->label);
        //     //merge cells
        // $this->sheet->mergeCells("A".$this->row.":C".$this->row);
    
        //     //set title Font style
        // $this->sheet->getStyle('A'.$this->row)->getFont()->setSize(20);
        
        //     //adding space
        // $this->sheet->getRowDimension($this->row+1)->setRowHeight(24);
        
        //     //increasing row index.
        //     $this->row+=2;
    
        // //header
        // $this->makeheaderandbody($data, $tableheader);
        $this->row+=1; //we always increase row index after processing either header or rubriques or lignes
        //rubriques
        foreach($data->collection as $rubrique){
            $this->processrubrique($rubrique);
            //$this->row+=1; //we always increase row index after processing either header or rubriques or lignes
        }
        //sum row chapitre
        //TODO
        $this->sheet->insertNewRowBefore($this->row, 1);
        $this->sheet->setCellValue('A'.$this->row,"Total Titre");
        $this->sheet->getStyle('A'.$this->row)->getFont()->setBold(true);
        //delete unacessary rows 
        $this->row++;
    }
    
    function processligne($ligne){
        //global $this->sheet, $row;
        
        //setting values
        // echo "processing ligne ".$ligne->libelle;
        // echo "\n";
        $this->sheet->setCellValue('A'.$this->row,$ligne->libelle)
            ->setCellValue('B'.$this->row,$ligne->prevision)
            ->setCellValue('C'.$this->row,$ligne->realisations)
            ->setCellValue('D'.$this->row,$ligne->engagements)
            ->setCellValue('E'.$this->row,$ligne->execution)
            ->setCellValue('F'.$this->row,$ligne->tauxExecution);
        
        //setting taux execution column bold
        $this->sheet->getStyle('F'.$this->row)->getFont()->setBold(true);
    }

    function makeheaderandbody($data, $tableheader){
        //global $this->sheet, $row, $thinborders, $greentext, $tableheader;
        // echo "building header and body";
        // echo "\n";
        //header title row
        $this->sheet->getStyle("A".$this->row.":F".$this->row)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THICK);
        
        $this->sheet->getStyle("A".$this->row)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK);
        $this->sheet->getStyle("A".$this->row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        $this->sheet->getStyle("A".$this->row)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);
        //$this->sheet->getStyle("I".$this->row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        
        // $this->sheet->getStyle("A".$this->row+1)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK);
        // $this->sheet->getStyle("A".$this->row+1)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        // $this->sheet->getStyle("A".$this->row+1)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);
        
        //merging header title cells
        $this->sheet->mergeCells("B".$this->row.":F".$this->row);
        //set styles alignment, borders, value, etc.
        $this->sheet->getStyle("B".$this->row.":F".$this->row)->getAlignment()->setHorizontal(ALIGNMENT::HORIZONTAL_CENTER);
        $this->sheet->getStyle("B".$this->row.":F".$this->row)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK);
        $this->sheet->getStyle("B".$this->row.":F".$this->row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        $this->sheet->getStyle("B".$this->row.":F".$this->row)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);
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
        foreach(['B','C','D','E','F'] as $x){
            $this->sheet->getStyle($x.$this->row.":".$x.($this->row+1))->applyFromArray($this->thinborders);
            $this->sheet->mergeCells($x.$this->row.":".$x.($this->row+1));
        }

        
        
        
        //setting values
        $this->sheet->setCellValue('B'.$this->row,$tableheader->previsionsLabel)
            ->setCellValue('C'.$this->row,$tableheader->realisationsLabel)
            ->setCellValue('D'.$this->row,$tableheader->engagementsLabel)
            ->setCellValue('E'.$this->row,$tableheader->executionLabel)
            ->setCellValue('F'.$this->row,$tableheader->tauxExecutionLabel);
    
        //fonts, row dimensions, alignment
        $this->sheet->getStyle('B'.$this->row)->getFont()->setSize(14);
        $this->sheet->getStyle('B'.$this->row)->getFont()->setBold(true);
        $this->sheet->getStyle("C".$this->row.":F".$this->row)->getFont()->setSize(12);
        $this->sheet->getStyle("C".$this->row.":F".$this->row)->getFont()->setBold(true);
        $this->sheet->getRowDimension($this->row+1)->setRowHeight(28);
        $this->sheet->getStyle("B".$this->row.":F".$this->row)->getAlignment()->setHorizontal(ALIGNMENT::HORIZONTAL_CENTER);
        $this->sheet->getStyle("B".$this->row.":F".$this->row)->getAlignment()->setVertical(ALIGNMENT::VERTICAL_CENTER);
        $this->sheet->getStyle("B".$this->row.":F".$this->row)->getAlignment()->setWrapText(true);
        //make text green
        $this->sheet->getStyle("B".$this->row.":F".$this->row)->applyFromArray($this->greentext);
        //setting thick borders around the header row
        $this->sheet->getStyle("A".$this->row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        $this->sheet->getStyle("A".$this->row)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK);
        $this->sheet->getStyle("F".$this->row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        $this->row++;
        $this->sheet->getStyle("F".$this->row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        $this->sheet->getStyle("A".$this->row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        $this->sheet->getStyle("A".$this->row)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK);
        $this->sheet->getStyle("A".$this->row)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);



        //create and style two row that will serve as templates
        for($i = 0; $i < 2; $i++){
            $this->row++;
            foreach(['A','B','C','D','E','F'] as $x){
                $this->sheet->getStyle($x.$this->row.":".$x.($this->row+1))->applyFromArray($this->thinborders);
            }
            $this->sheet->getStyle('A'.$this->row)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK);
            $this->sheet->getStyle('F'.$this->row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        }
        $this->row = $this->row-2;
    }
}













