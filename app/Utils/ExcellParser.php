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
        $this->row = 1;
    }


    //initial and only public function in the class. redirects to appropriate processing method depending on data type
    public function toExcell($data){
        //$row = 1;
        if(isset($data->baniere)){ //if baniere then process 
            $this->processwithbaniere($data);
        }
        else if(isset($data->titre)){
            $this->processtitre($data);
        }
        else if(isset($data->chapitre)){
            $this->processchapitre($data, $data->tableheader);
        }
        else return 'unknown data type passed';

        // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // //make it an attachment so we can define filename
        // header('Content-Disposition: attachment;filename="result.xlsx"');


        //create IOFactory object
        $writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        //save into php output
        //$writer->save('php://output');
        $myfile = fopen("file:///C:/Dev/Git/budget/gesbudget/storage/app/public/files/result.xlsx", "w") or die("Unable to open file!");
        
        //$writer->save('file:///C:/laragon/www/saturn/app/public/files/result.xlsx');
        $writer->save($myfile);
        fclose($myfile);
        echo 'file correctly saved. check location';
    }


    public function processwithbaniere($data){
        //global $this->sheet, $types, $row, $thickborders;
        /**
         * add baniere to sheet
    
        if type1, process grandes rubriques
        if type2, process grandes lignes
        if type3, process titre
        **/
    
        $this->sheet->setCellValue('A'.$this->row, $data->baniere);
    
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
        
        if($data->type == 'ban_gr'){
            $this->makeheaderandbody($data, $data->tableheader);
            $this->row++; //we always increase row index after processing either header or rubriques or lignes
            foreach($data->grandesrubriques as $granderubrique){
                $this->processgranderubrique($granderubrique, $this->row, $data->tableheader);
                $this->row++; //we always increase row index after processing either header or rubriques or lignes
                //add sum
                //TODO
            }
        }
        else if($data->type == 'ban_gl'){
            $this->processgrandeslignes($data, $data->tableheader);
        }
        else if($data->type == 'ban_titre_gl'){
            $this->processtitre($data, $data->tableheader);
        }
    }

    public function processtitre($data){
        //add titre
    
        //if type3, process grandes lignes
        //if type4, for each categorie, process categories
        //global $this->sheet, $types, $row, $thickborders;
        //set titre
        $this->sheet->setCellValue('A'.$this->row, $data->titre);
        //merge cells
        $this->sheet->mergeCells("A".$this->row.":C".$this->row);
    
        //set title Font style
        $this->sheet->getStyle('A'.$this->row)->getFont()->setSize(20);
    
        if($data->type == $this->types['type3']){
            //make header
            $this->makeheaderandbody($data, $data->tableheader);
            $this->row+=2; //we always increase row index after processing either header or rubriques or lignes
            //for each grande ligne, process grande ligne, 
            //make sum
            $this->processgrandeslignes($data, $data->tableheader);
            //TODO SUM
        } 
        else if($data->type == $this->types['type4']){
            foreach($data->chapitres as $chapitre){
                $this->processchapitre($chapitre, $data->tableheader);
                $this->row+=2; //we always increase row index after processing either header or rubriques or lignes
            }
        }
    }

    function processgrandeslignes($data, $tableheader){
        //make header, read lines, add them, add sum
        $this->makeheaderandbody($data, $tableheader);
        $this->row+=2; //we always increase row index after processing either header or rubriques or lignes
        foreach($data->grandeslignes as $ligne){
            $this->processligne($ligne, $this->row);
            $this->row+=1; //we always increase row index after processing either header or rubriques or lignes
        }
    }
    
    function processgrandesrubriques($data, $row, $tableheader){
        //add header
        //for each gr, processrubrique(), record sums
        //add global sum
        $this->makeheaderandbody($data, $row, $tableheader);
        foreach($data->grandesrubriques as $granderubrique){
            $this->processrubrique($granderubrique, $row);
        }
        //make sum
        //TODO
    }

    function processgranderubrique($data, $tableheader){
        //write grande rubrique header
        $this->sheet->insertNewRowBefore($this->row, 1);
        $this->sheet->setCellValue('A'.$this->row,$data->label);
        $this->row++;
        //for each rubrique in grande rubrique, processrubrique
        //write sum
        echo "processing grande rubrique ".$data->label;
        echo "\n";
        //$this->makeheaderandbody($data, $row, $tableheader);
        foreach($data->rubriques as $rubrique){
            $this->processrubrique($rubrique);
            $this->row+=1; //we always increase row index after processing either header or rubriques or lignes
        }
    }

    function processrubrique($data){
        //add lines, add sum, record sum
       //global $this->sheet, $types, $row, $thickborders;
        echo "processing rubrique ".$data->label;
        echo "\n";
        $this->sheet->insertNewRowBefore($this->row, 1);
        $this->sheet->setCellValue('A'.$this->row,$data->label);
        $this->row++;
        
        foreach($data->lignes as $ligne){
            $this->sheet->insertNewRowBefore($this->row, 1);
            $this->processligne($ligne);
            $this->row+=1; //we always increase row index after processing either header or rubriques or lignes
        }
        //add sum 
        //TODO
    }

    function processchapitre($data, $tableheader){
        //add categorie title, add space,
        //add header
        //for each rubrique, process rubrique
        //add total line.
        //global $this->sheet, $types, $row, $thickborders;
        //titre
            //set titre
        $this->sheet->setCellValue('A'.$this->row, $data->label);
            //merge cells
        $this->sheet->mergeCells("A".$this->row.":C".$this->row);
    
            //set title Font style
        $this->sheet->getStyle('A'.$this->row)->getFont()->setSize(20);
        
            //adding space
        $this->sheet->getRowDimension($this->row+1)->setRowHeight(24);
        
            //increasing row index.
            $this->row+=2;
    
        //header
        $this->makeheaderandbody($data, $tableheader);
        $this->row+=2; //we always increase row index after processing either header or rubriques or lignes
        //rubriques
        foreach($data->rubriques as $rubrique){
            $this->processrubrique($rubrique, $this->row);
            $this->row+=1; //we always increase row index after processing either header or rubriques or lignes
        }
        //sum rubriques
        //TODO
    }
    
    function processligne($ligne){
        //global $this->sheet, $row;
        
        //setting values
        echo "processing ligne ".$ligne->label;
        echo "\n";
        $this->sheet->setCellValue('A'.$this->row,$ligne->label)
            ->setCellValue('B'.$this->row,$ligne->prevision)
            ->setCellValue('C'.$this->row,$ligne->realisations_mois)
            ->setCellValue('D'.$this->row,$ligne->realisations_precedentes)
            ->setCellValue('E'.$this->row,$ligne->realisations_cumulees)
            ->setCellValue('F'.$this->row,$ligne->engagements_mois)
            ->setCellValue('G'.$this->row,$ligne->execution_mois)
            ->setCellValue('H'.$this->row,$ligne->solde)
            ->setCellValue('I'.$this->row,$ligne->taux_execution);
        
        //setting solde column bold
        $this->sheet->getStyle('H'.$this->row)->getFont()->setBold(true);
        //setting taux execution column bold
        $this->sheet->getStyle('I'.$this->row)->getFont()->setBold(true);
    }

    function makeheaderandbody($data, $tableheader){
        //global $this->sheet, $row, $thinborders, $greentext, $tableheader;
        echo "building header and body";
        echo "\n";
        //header title row
        $this->sheet->getStyle("A".$this->row.":I".$this->row)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THICK);
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

        //header column titles
        foreach(['B','C','D','E','F','G','H','I'] as $x){
            $this->sheet->getStyle($x.$this->row.":".$x.($this->row+1))->applyFromArray($this->thinborders);
            $this->sheet->mergeCells($x.$this->row.":".$x.($this->row+1));
        }
    
        //setting values
        $this->sheet->setCellValue('B'.$this->row,$tableheader['prevision'])
            ->setCellValue('C'.$this->row,$tableheader['realisations_mois_label'])
            ->setCellValue('D'.$this->row,$tableheader['realisations_precedentes_label'])
            ->setCellValue('E'.$this->row,$tableheader['realisations_cumulees_label'])
            ->setCellValue('F'.$this->row,$tableheader['engagements_mois_label'])
            ->setCellValue('G'.$this->row,$tableheader['execution_mois_label'])
            ->setCellValue('H'.$this->row,$tableheader['solde_label'])
            ->setCellValue('I'.$this->row,$tableheader['taux_execution_label']);
    
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

        //create and style two row that will serve as templates
        for($i = 0; $i < 2; $i++){
            $this->row++;
            foreach(['A','B','C','D','E','F','G','H','I'] as $x){
                $this->sheet->getStyle($x.$this->row.":".$x.($this->row+1))->applyFromArray($this->thinborders);
            }
            $this->sheet->getStyle('A'.$this->row)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK);
            $this->sheet->getStyle('I'.$this->row)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
        }
        $this->row--;
    }
}













