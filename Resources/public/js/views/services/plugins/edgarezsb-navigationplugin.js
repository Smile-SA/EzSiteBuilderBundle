YUI.add('smileezsb-navigationplugin', function (Y) {
    Y.namespace('smileEzSb.Plugin');

    Y.smileEzSb.Plugin.NavigationPlugin = Y.Base.create('smileezsbNavigationPlugin', Y.eZ.Plugin.ViewServiceBase, [], {
        initializer: function () {
            var service = this.get('host'); // the plugged object is called host

            service.addNavigationItem({
                Constructor: Y.eZ.NavigationItemView,
                config: {
                    title: "Site Builder",
                    identifier: "smileezsb-list-contents",
                    route: {
                        name: "smileEzSbSb"
                    }
                }
            }, 'platform');
        },
    }, {
        NS: 'smileezsbNavigation'
    });

    Y.eZ.PluginRegistry.registerPlugin(
        Y.smileEzSb.Plugin.NavigationPlugin, ['navigationHubViewService']
    );
});
