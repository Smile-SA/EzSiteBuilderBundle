YUI.add('edgarezsb-navigationplugin', function (Y) {
    Y.namespace('edgarEzSb.Plugin');

    Y.edgarEzSb.Plugin.NavigationPlugin = Y.Base.create('edgarezsbNavigationPlugin', Y.eZ.Plugin.ViewServiceBase, [], {
        initializer: function () {
            var service = this.get('host'); // the plugged object is called host

            service.addNavigationItem({
                Constructor: Y.eZ.NavigationItemView,
                config: {
                    title: "Site Builder",
                    identifier: "edgarezsb-list-contents",
                    route: {
                        name: "edgarEzSbSb"
                    }
                }
            }, 'platform');
        },
    }, {
        NS: 'edgarezsbNavigation'
    });

    Y.eZ.PluginRegistry.registerPlugin(
        Y.edgarEzSb.Plugin.NavigationPlugin, ['navigationHubViewService']
    );
});
