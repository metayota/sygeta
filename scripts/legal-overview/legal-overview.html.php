<div class="legal-overview">
    <h1><t>legal_overview_title</t></h1>
    <?php $legalTexts = $db->getAllByQuery('SELECT lt.*, t.translation as title_translated FROM legal_text lt LEFT JOIN translation t ON lt.title = t.translation_key AND t.language=:language WHERE archived=0', ['language'=>$GLOBALS['language']]); ?>   
    <ul class="legal-links">
        <?php foreach($legalTexts as $legalText): ?>
            <li><a link="/legal/<?= $legalText['name'] ?>"><?= $legalText['title_translated'] ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>