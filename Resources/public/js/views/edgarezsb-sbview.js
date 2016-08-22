YUI.add('edgarezsb-sbview', function (Y) {
    Y.namespace('edgarEzSb');

    Y.edgarEzSb.SbView = Y.Base.create('edgarezsbSbView', Y.eZ.ServerSideView, [], {
        events: {
            '.edgarezsb-sb-location': {
                'tap': '_navigateToLocation'
            }
        },

        initializer: function () {
            this.containerTemplate = '<div class="ez-view-edgarezsbsbview"/>';
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
