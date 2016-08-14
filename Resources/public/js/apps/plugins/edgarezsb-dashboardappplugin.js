YUI.add('edgarezsb-dashboardapplugin', function (Y) {
    Y.namespace('edgarEzSb.Plugin');

    Y.edgarEzSb.Plugin.DashboardAppPlugin = Y.Base.create('edgarezsbDashboardAppPlugin', Y.Plugin.Base, [], {
        initializer: function () {
            var app = this.get('host'); // the plugged object is called host

            app.views.edgarezsbDashboardView = {
                type: Y.edgarEzSb.DashboardView,
            };

            app.route({
                name: "edgarEzSbDashboard",
                path: "/edgarezsb/dashboard",
                view: "edgarezsbDashboardView",
                service: Y.edgarEzSb.DashboardViewService, // the service will be used to load the necessary data
                // we want the navigationHub (top menu) but not the discoveryBar
                // (left bar), we can try different options
                sideViews: {'navigationHub': true, 'discoveryBar': false},
                callbacks: ['open', 'checkUser', 'handleSideViews', 'handleMainView'],
            });
        },
    }, {
        NS: 'edgarezsbTypeApp' // don't forget that
    });

    Y.eZ.PluginRegistry.registerPlugin(
        Y.edgarEzSb.Plugin.DashboardAppPlugin, ['platformuiApp']
    );
});
