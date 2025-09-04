<?php


use Bitrix\Main\Engine\Router;
use Bitrix\Main\Engine\Controller;

$router = Router::getInstance();
$router->addRoute('get_companies.get_companies', 'GetCompaniesController::getCompanies');
