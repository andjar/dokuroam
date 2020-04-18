<?php
require_once(dirname(__FILE__) . '/../action.php');
$conf['ActionReaderFirst']  = 'GoToBestPageName';
$conf['ActionReaderSecond'] = 'GoToSearchEngine';
$conf['ActionReaderThird']  = 'Nothing';
$conf['GoToEditMode'] = 1;
$conf['ShowPageNameIsNotUnique'] = 1;
$conf['ShowMessageClassic'] = 1;
$conf['WeightFactorForSamePageName'] = 4;
$conf['WeightFactorForStartPage'] = 3;
// If the page has the same namespace in its path, it gets more weight
$conf['WeightFactorForSameNamespace'] = 5;
?>
