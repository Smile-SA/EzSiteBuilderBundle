YUI.add('edgarezsb-installview', function (Y) {
    Y.namespace('edgarEzSb');

    Y.edgarEzSb.InstallView = Y.Base.create('edgarezsbInstallView', Y.eZ.ServerSideView, [], {
        events: {
            '.edgarezsb-install-location': {
                'tap': '_navigateToLocation'
            }
        },

        initializer: function () {
            this.containerTemplate = '<div class="ez-view-edgarezsbinstallview"/>';
        },

        _navigateToLocation: function (e) {
            var link = e.target;

            e.preventDefault();

            this.fire('navigateTo', {
                routeName: link.getData('route-name'),
                routeParams: {
                    id: link.getData('route-id'),
                    languageCode: link.getData('route-languagecode')
                }
            });
        }
    });
});
