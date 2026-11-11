<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
$lineDiagramSeries = $db->getAll('line_diagram_series',['diagram_id'=>$data['diagram_id']]);
foreach($lineDiagramSeries as &$lineDiagramSerie) {
    $lineDiagramSerie['title_translated'] = translate($lineDiagramSerie['title']);
}
echo json_encode($lineDiagramSeries);
?>