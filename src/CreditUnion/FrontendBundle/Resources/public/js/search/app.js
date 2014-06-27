

var searchApp = angular.module('searchApp', [
  'ngResource',
  'ui.bootstrap',
  'clientsControllers'
]);

searchApp
        .config(['$routeProvider',
          function($routeProvider) {
            $routeProvider.
                    //basic list
                    when('/basicSearch/clients/:searchText', {
                      name: 'basicClientList',
                      templateUrl: Routing.generate('cr_frontend_client_template', {templateName: 'clientList.html.twig'}),
                      controller: 'ClientListCtrl',
                    }).
                    //details client from basic list
                    when('/basicSearch/clientDetail/:clientId/:searchText', {
                      templateUrl: Routing.generate('cr_frontend_client_template', {templateName: 'clientDetails.html.twig'}),
                      controller: 'ClientDetailsCtrl'
                    }).
                    //advanced search list
                    when('/advancedSearch/clients/name=:name&birthDate=:birthDate&fininstitut=:fininstitut&panNumber=:panNumber&accountNumber=:accountNumber', {
                      name: 'advancedClientList',
                      templateUrl: Routing.generate('cr_frontend_client_template', {templateName: 'clientList.html.twig'}),
                      controller: 'ClientListAdvCtrl'
                    }).
                    //details client from advanced list
                    when('/advancedSearch/clientDetail/:clientId/name=:name&birthDate=:birthDate&fininstitut=:fininstitut&panNumber=:panNumber&accountNumber=:accountNumber', {
                      templateUrl: Routing.generate('cr_frontend_client_template', {templateName: 'clientDetails.html.twig'}),
                      controller: 'ClientDetailsAdvCtrl'
                    }).
                    otherwise({
                      redirectTo: '/'
                    });
          }
        ])
        .run(function($route, $rootScope) {
          $rootScope.path = function(controller, params)
          {
            // Iterate over all available routes
            for (var path in $route.routes)
            {
              var nameController = $route.routes[path].name;
              if (nameController === controller) // Route found
              {
                var result = path;
                // Construct the path with given parameters in it
                for (var param in params)
                {
                  result = result.replace(':' + param, params[param]);
                }
                return result;
              }
            }
            // No such controller in route definitions
            return undefined;
          };
        });
;