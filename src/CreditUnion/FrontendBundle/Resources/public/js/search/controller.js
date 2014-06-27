searchApp
        .factory("myShareModel", function() {
          var search = {
            name: '',
            birthDate: '',
            fininstitut: '',
            panNumber: '',
            accountNumber: '',
            query: '',
            advancedQuery: ''
          };

          return {
            getModel: function() {
              return search;
            },
            setAdvancedQuery: function() {
              search.advancedQuery =
                      'name=' + search.name
                      + '&birthDate=' + search.birthDate
                      + '&fininstitut=' + search.fininstitut
                      + '&panNumber=' + search.panNumber
                      + '&accountNumber=' + search.accountNumber;
            }
          };

        });

var clientsControllers = angular.module('clientsControllers', []);

clientsControllers.controller('SearchListCtrl', ['$scope', '$location', '$timeout', '$filter', 'myShareModel',
  function SearchListCtrl($scope, $location, $timeout, $filter, myShareModel) {
    $scope.search = myShareModel.getModel();

    $scope.updateAdvancedClients = function() {
      if (angular.isString($scope.search.birthDate) && $scope.search.birthDate !== '') {
        $scope.search.birthDate = new Date($scope.search.birthDate);
      } else if (angular.isDate($scope.search.birthDate)) {
        $scope.search.birthDate = $filter('date')($scope.search.birthDate, 'yyyy-MM-dd');
      } else {
        $scope.search.birthDate = '';
      }
      myShareModel.setAdvancedQuery();
      $location.path('/advancedSearch/clients/' + $scope.search.advancedQuery);
    };
    $scope.open = function() {
      $timeout(function() {
        $scope.opened = true;
      });
    };
  }]);

clientsControllers.controller('ClientListAdvCtrl', ['$scope', '$http', '$routeParams', 'myShareModel', '$filter',
  function ClientListAdvCtrl($scope, $http, $routeParams, myShareModel, $filter) {
    $scope.display = true;
    $scope.search = myShareModel.getModel();

    if ($routeParams.name !== 'undefined' && $routeParams.name !== 'null') {
      $scope.search.name = $routeParams.name;
    }
    if ($routeParams.birthDate !== 'undefined' && $routeParams.birthDate !== 'null' && $routeParams.birthDate !== '') {
      $scope.search.birthDate = $filter('date')(new Date($routeParams.birthDate.replace(/-/g, '/')), 'yyyy/MM/dd');
    }
    if ($routeParams.fininstitut !== 'undefined' && $routeParams.fininstitut !== 'null') {
      $scope.search.fininstitut = $routeParams.fininstitut;
    }
    if ($routeParams.panNumber !== 'undefined' && $routeParams.panNumber !== 'null') {
      $scope.search.panNumber = $routeParams.panNumber;
    }
    if ($routeParams.accountNumber !== 'undefined' && $routeParams.accountNumber !== 'null') {
      $scope.search.accountNumber = $routeParams.accountNumber;
    }
    //share data with searchCtrl
    myShareModel.setAdvancedQuery();

    if ($routeParams.name === 'undefined') {
      $routeParams.name = '';
    }
    if ($routeParams.birthDate === 'undefined' || $routeParams.birthDate === 'null') {
      $routeParams.birthDate = '';
    }
    if ($routeParams.fininstitut === 'undefined') {
      $routeParams.fininstitut = '';
    }
    if ($routeParams.panNumber === 'undefined') {
      $routeParams.panNumber = '';
    }
    if ($routeParams.accountNumber === 'undefined') {
      $routeParams.accountNumber = '';
    }

    $http.get(Routing.generate('cr_frontend_client_list_adv', {'name': $routeParams.name, 'birthDate': $routeParams.birthDate, 'fininstitut': $routeParams.fininstitut, 'panNumber': $routeParams.panNumber, 'accountNumber': $routeParams.accountNumber}))
            .success(function(data) {
              $scope.clients = data;
            });
    $scope.searchType = 'advancedSearch';
    $scope.parentQuery = $scope.search.advancedQuery;
  }]);

clientsControllers.controller('ClientDetailsAdvCtrl', ['$scope', '$http', '$routeParams', '$rootScope',
  function ClientDetailsAdvCtrl($scope, $http, $routeParams, $rootScope) {
    $http.get(Routing.generate('cr_frontend_client_getclient', {clientId: $routeParams.clientId})).success(function(data) {
      $scope.client = data;
    });
    $scope.urlList = $rootScope.path('advancedClientList', {
      name: $routeParams.name,
      birthDate: $routeParams.birthDate,
      fininstitut: $routeParams.fininstitut,
      panNumber: $routeParams.panNumber,
      accountNumber: $routeParams.accountNumber,
    });
    $scope.searchType = 'advancedSearch';
  }]);