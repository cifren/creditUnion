searchApp
        .factory("myShareModel", function() {
            var search = {
                name: '',
                birthDate: '',
                branch: '',
                panNumber: '',
                accountNumber: '',
                query: ''
            };
            return {
                getModel: function() {
                    return search;
                }
            };

        });

var clientsControllers = angular.module('clientsControllers', []);

clientsControllers.controller('SearchListCtrl', ['$scope', '$location', '$timeout', '$filter', 'myShareModel',
    function SearchListCtrl($scope, $location, $timeout, $filter, myShareModel) {
        $scope.search = myShareModel.getModel();

        $scope.updateClients = function() {
            $location.path('/basicSearch/clients/' + $scope.search.query);
        };
        $scope.updateAdvancedClients = function() {
            if (angular.isString($scope.search.birthDate) && $scope.search.birthDate !== '') {
                $scope.search.birthDate = new Date($scope.search.birthDate);
            } else if (angular.isDate($scope.search.birthDate)) {
                $scope.search.birthDate = $filter('date')($scope.search.birthDate, 'yyyy-MM-dd');
            } else {
                $scope.search.birthDate = '';
            }

            $location.path('/advancedSearch/clients/' + $scope.search.query);
        };
        $scope.open = function() {
            $timeout(function() {
                $scope.opened = true;
            });
        };
    }]);

clientsControllers.controller('ClientListCtrl', ['$scope', '$http', '$routeParams', 'myShareModel',
    function ClientListCtrl($scope, $http, $routeParams, myShareModel) {
        $scope.search = myShareModel.getModel();
        $scope.search.query = $routeParams.searchText;
        $scope.display = true;
        $scope.searchType = 'basicSearch';
        if (!$scope.search.query || $scope.search.query === 'undefined') {
            $scope.display = false;
        } else {
            $http.get(Routing.generate('cr_frontend_client_list', {searchText: $routeParams.searchText}))
                    .success(function(data) {
                        $scope.clients = data;
                    })
                    .error(function(data) {
                        $scope.display = false;
                    });
        }
    }]);

clientsControllers.controller('ClientListAdvCtrl', ['$scope', '$http', '$routeParams', 'myShareModel', '$filter',
    function ClientListAdvCtrl($scope, $http, $routeParams, myShareModel, $filter) {
        $scope.display = true;
        $scope.search = myShareModel.getModel();
        $('#myTab #tabAdvanced').tab('show');

        if ($routeParams.name !== 'undefined' && $routeParams.name !== 'null') {
            $scope.search.name = $routeParams.name;
        }
        if ($routeParams.birthDate !== 'undefined' && $routeParams.birthDate !== 'null' && $routeParams.birthDate !== '') {
            $scope.search.birthDate = $filter('date')(new Date($routeParams.birthDate.replace(/-/g, '/')), 'yyyy/MM/dd');
        }
        if ($routeParams.branch !== 'undefined' && $routeParams.branch !== 'null') {
            $scope.search.branch = $routeParams.branch;
        }
        if ($routeParams.panNumber !== 'undefined' && $routeParams.panNumber !== 'null') {
            $scope.search.panNumber = $routeParams.panNumber;
        }
        if ($routeParams.accountNumber !== 'undefined' && $routeParams.accountNumber !== 'null') {
            $scope.search.accountNumber = $routeParams.accountNumber;
        }
        //share data with searchCtrl
        $scope.search.query = 'name=' + $scope.search.name
                + '&birthDate=' + $scope.search.birthDate
                + '&branch=' + $scope.search.branch
                + '&panNumber=' + $scope.search.panNumber
                + '&accountNumber=' + $scope.search.accountNumber;
        
        if ($routeParams.name === 'undefined') {
            $routeParams.name = '';
        }
        if ($routeParams.birthDate === 'undefined' || $routeParams.birthDate === 'null') {
            $routeParams.birthDate = '';
        }
        if ($routeParams.branch === 'undefined') {
            $routeParams.branch = '';
        }
        if ($routeParams.panNumber === 'undefined') {
            $routeParams.panNumber = '';
        }
        if ($routeParams.accountNumber === 'undefined') {
            $routeParams.accountNumber = '';
        }

        $http.get(Routing.generate('cr_frontend_client_list_adv', {'name': $routeParams.name, 'birthDate': $routeParams.birthDate, 'branch': $routeParams.branch, 'panNumber': $routeParams.panNumber, 'accountNumber': $routeParams.accountNumber}))
                .success(function(data) {
                    $scope.clients = data;
                });
        $scope.searchType = 'advancedSearch';
    }]);

clientsControllers.controller('ClientDetailsCtrl', ['$scope', '$http', '$routeParams', '$rootScope',
    function ClientDetailsCtrl($scope, $http, $routeParams, $rootScope) {
        $http.get(Routing.generate('cr_frontend_client_getclient', {clientId: $routeParams.clientId})).success(function(data) {
            $scope.client = data;
        });
        $scope.urlList = $rootScope.path('basicClientList', {searchText: $routeParams.searchText});
        $scope.searchType = 'basicSearch';
    }]);

clientsControllers.controller('ClientDetailsAdvCtrl', ['$scope', '$http', '$routeParams', '$rootScope',
    function ClientDetailsAdvCtrl($scope, $http, $routeParams, $rootScope) {
        $http.get(Routing.generate('cr_frontend_client_getclient', {clientId: $routeParams.clientId})).success(function(data) {
            $scope.client = data;
        });
        $scope.urlList = $rootScope.path('advancedClientList', {
            name: $routeParams.name,
            birthDate: $routeParams.birthDate,
            branch: $routeParams.branch,
            panNumber: $routeParams.panNumber,
            accountNumber: $routeParams.accountNumber,
        });
        $scope.searchType = 'advancedSearch';
    }]);