YUI.add('edgarezsb-installview', function (Y) {
    Y.namespace('edgarEzSb');

    Y.edgarEzSb.InstallView = Y.Base.create('edgarezsbInstallView', Y.eZ.ServerSideView, [], {
        events: {
            '#edgarezsb_forms_install_install': {
                'tap': '_submitFormn'
            },
        },

        initializer: function () {
            this.containerTemplate = '<div class="ez-view-edgarezsbinstallview"/>';
        },

        _submitFormn: function (e) {
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
