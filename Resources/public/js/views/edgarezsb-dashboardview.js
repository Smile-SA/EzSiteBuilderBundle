YUI.add('edgarezsb-dashboardview', function (Y) {
    Y.namespace('edgarEzSb');

    Y.edgarEzSb.DashboardView = Y.Base.create('edgarezsbDashboardView', Y.eZ.ServerSideView, [], {
        events: {
            '.edgarezsb-dashboard-location': {
                // tap is 'fast click' (touch friendly)
                'tap': '_navigateToLocation'
            }
        },

        initializer: function () {
            this.containerTemplate = '<div class="ez-view-edgarezsbdashboardview"/>';
        },

        _navigateToLocation: function (e) {
            var link = e.target;

            e.preventDefault(); // don't want the normal link behavior

            this.fire('navigateTo', {
                routeName: link.getData('route-name'),
                routeParams: {
                    id: link.getData('route-id'),
                    languageCode: link.getData('route-languagecode'),
                }
            });
        },
    });
});
