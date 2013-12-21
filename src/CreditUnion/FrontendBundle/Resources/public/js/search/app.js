

var searchApp = angular.module('searchApp', [
    'ngResource',
    'ui.bootstrap',
    'clientsControllers'
]);

searchApp.config(['$routeProvider',
    function($routeProvider) {
        $routeProvider.
                when('/basicSearch/clients/:searchText', {
                    templateUrl: Routing.generate('cr_frontend_client_template', {templateName: 'clientList.html.twig'}),
                    controller: 'ClientListCtrl'
                }).
                when('/advancedSearch/clients/name=:name&birthDate=:birthDate&agency=:agency&panNumber=:panNumber&accountNumber=:accountNumber', {
                    templateUrl: Routing.generate('cr_frontend_client_template', {templateName: 'clientList.html.twig'}),
                    controller: 'ClientListAdvCtrl'
                }).
                when('/basicSearch/clientDetail/:clientId/:searchText', {
                    templateUrl: Routing.generate('cr_frontend_client_template', {templateName: 'clientDetails.html.twig'}),
                    controller: 'ClientDetailsCtrl'
                }).
                when('/advancedSearch/clientDetail/:clientId/name=:name&birthDate=:birthDate&agency=:agency&panNumber=:panNumber&accountNumber=:accountNumber', {
                    templateUrl: Routing.generate('cr_frontend_client_template', {templateName: 'clientDetails.html.twig'}),
                    controller: 'ClientDetailsAdvCtrl'
                }).
                otherwise({
                    redirectTo: '/'
                });
    }
]);