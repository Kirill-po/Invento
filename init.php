<?php
\Bitrix\Main\EventManager::getInstance()->addEventHandler('documentgenerator', 'onGetDataProviderList', 'addDocumentProviders');

function addDocumentProviders()
{
    \Bitrix\Main\Loader::includeModule('documentgenerator');

    require_once('myprovider.php');

    $result['MyProvider'] = [
        'NAME' => 'Мой провайдер',
        'CLASS' => 'MyProvider',
        'MODULE' => 'documentgenerator',
    ];

    return $result;
}